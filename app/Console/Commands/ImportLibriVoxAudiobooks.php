<?php

namespace App\Console\Commands;

use App\Models\Audiobook;
use App\Models\AudiobookSection;
use App\Models\Category;
use App\Models\ImportProgress;
use App\Services\LibriVoxService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportLibriVoxAudiobooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'librivox:import
        {--limit=10 : Number of audiobooks to process per run}
        {--offset=0 : Starting offset for fetching audiobooks (overrides stored progress if provided)}
        {--since= : Fetch audiobooks added since this date (YYYY-MM-DD)}
        {--dry-run : Simulate the import without writing to the database}
        {--skip=66 : Comma-separated LibriVox IDs to skip during import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports audiobooks and their sections from the LibriVox API with progress tracking.';

    protected LibriVoxService $libriVoxService;

    /**
     * Create a new command instance.
     *
     * @param LibriVoxService $libriVoxService
     */
    public function __construct(LibriVoxService $libriVoxService)
    {
        parent::__construct();
        $this->libriVoxService = $libriVoxService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $cliOffset = (int) $this->option('offset');
        $since = (string) $this->option('since');
        $isDryRun = (bool) $this->option('dry-run');
        $skipIds = array_map('trim', explode(',', $this->option('skip')));

        // Get or create progress tracking
        $progress = ImportProgress::firstOrCreate(
            ['type' => 'librivox_audiobooks'],
            ['last_id' => null, 'offset' => 0, 'errors' => '[]']
        );

        // Use CLI offset if provided, otherwise use stored offset
        $currentOffset = $cliOffset > 0 ? $cliOffset : $progress->offset;

        $this->info("Starting import from offset {$currentOffset} with limit {$limit}" . ($since ? ", since: {$since}" : "") . "...");
        if ($isDryRun) {
            $this->info('DRY RUN: No data will be written to the database.');
        }

        $apiParams = [];
        if ($since) {
            $apiParams['since'] = strtotime($since);
        }

        try {
            $apiResponse = $this->libriVoxService->fetchAudiobooks(limit: $limit, offset: $currentOffset, params: $apiParams);
            $apiAudiobooks = $apiResponse['books'] ?? [];
            $totalFound = $apiResponse['total_found'] ?? 0;

            if (empty($apiAudiobooks)) {
                $this->info("No more audiobooks found from offset {$currentOffset}. Import complete for this run.");
                return Command::SUCCESS;
            }

            $this->info(count($apiAudiobooks) . ' audiobooks fetched from API. Processing...');
            $progressBar = $this->output->createProgressBar(count($apiAudiobooks));
            $progressBar->start();

            $createdCount = 0;
            $updatedCount = 0;
            $errors = json_decode($progress->errors, true);

            foreach ($apiAudiobooks as $apiBook) {
                $librivoxId = $apiBook['id'] ?? null;
                $title = $apiBook['title'] ?? null;

                if (in_array($librivoxId, $skipIds)) {
                    $this->info("Skipping audiobook ID {$librivoxId} as requested.");
                    $progressBar->advance();
                    continue;
                }

                try {
                    if (empty($librivoxId) || empty($title)) {
                        $this->warn("Skipping an entry because LibriVox ID or title is empty. ID: '{$librivoxId}', Title: '{$title}'.");
                        $progressBar->advance();
                        continue;
                    }

                    // Category (from 'genres' field)
                    $categoryName = 'Uncategorized';
                    if (!empty($apiBook['genres']) && is_array($apiBook['genres'])) {
                        $genre = reset($apiBook['genres']);
                        $categoryName = $genre['name'] ?? 'Uncategorized';
                    }
                    $categorySlug = Str::slug($categoryName);

                    if (!$isDryRun) {
                        $category = Category::firstOrCreate(
                            ['name' => $categoryName],
                            ['slug' => $categorySlug]
                        );
                        if (!$category->wasRecentlyCreated && (is_null($category->slug) || $category->slug !== $categorySlug)) {
                            $category->slug = $categorySlug;
                            $category->save();
                        }
                    } else {
                        $category = (object)['id' => 99999, 'name' => $categoryName, 'slug' => $categorySlug];
                    }

                    // Author (from 'authors' field)
                    $authorName = 'Unknown Author';
                    if (!empty($apiBook['authors']) && is_array($apiBook['authors'])) {
                        $author = reset($apiBook['authors']);
                        $authorName = trim(($author['first_name'] ?? '') . ' ' . ($author['last_name'] ?? ''));
                        if (empty(trim($authorName))) {
                            $authorName = 'Unknown Author';
                        }
                    }

                    // Narrator (from 'sections' or 'description' - LibriVox API provides 'sections' with 'reader' field)
                    $narratorName = 'Various Readers';
                    if (!empty($apiBook['sections']) && is_array($apiBook['sections'])) {
                        $uniqueReaders = collect($apiBook['sections'])->pluck('reader')->unique()->filter()->all();
                        if (count($uniqueReaders) === 1) {
                            $narratorName = reset($uniqueReaders);
                        } elseif (count($uniqueReaders) > 1) {
                            $narratorName = 'Various Readers';
                        }
                    }

                    $durationStr = $apiBook['totaltime'] ?? 'N/A';
                    $description = !empty($apiBook['description']) ? strip_tags((string) $apiBook['description']) : 'No description available.';
                    $description = Str::limit($description, 1000);

                    $languageCode = $apiBook['language'] ?? 'English';
                    $fullLanguageName = Config::get('languages.' . strtolower($languageCode), $languageCode);

                    // Cover Image Handling
                    $coverImage = null;
                    if (isset($apiBook['coverart_jpg']) && filter_var($apiBook['coverart_jpg'], FILTER_VALIDATE_URL)) {
                        $coverImage = $apiBook['coverart_jpg'];
                    } elseif (isset($apiBook['coverart_thumbnail']) && filter_var($apiBook['coverart_thumbnail'], FILTER_VALIDATE_URL)) {
                        $coverImage = $apiBook['coverart_thumbnail'];
                    } elseif (isset($apiBook['url_cover_image']) && filter_var($apiBook['url_cover_image'], FILTER_VALIDATE_URL)) {
                        $coverImage = $apiBook['url_cover_image'];
                    } elseif (isset($apiBook['url_librivox'])) {
                        $constructedUrl = rtrim($apiBook['url_librivox'], '/') . '/cover_small.jpg';
                        if (filter_var($constructedUrl, FILTER_VALIDATE_URL)) {
                            $coverImage = $constructedUrl;
                        }
                    }
                    if (empty($coverImage) || !filter_var($coverImage, FILTER_VALIDATE_URL)) {
                        $coverImage = 'https://librivox.org/images/librivox_logo_small.png';
                    }

                    $audiobookData = [
                        'title' => $title,
                        'author' => $authorName,
                        'narrator' => $narratorName,
                        'description' => $description,
                        'cover_image' => $coverImage,
                        'duration' => $durationStr,
                        'source_url' => null,
                        'category_id' => $category->id,
                        'language' => $fullLanguageName,
                        'librivox_url' => $apiBook['url_librivox'] ?? null,
                        'librivox_id' => $librivoxId,
                    ];

                    $baseSlug = Str::slug($title);
                    $slug = $baseSlug;
                    $counter = 1;
                    while (Audiobook::where('slug', $slug)->where('librivox_id', '!=', $librivoxId)->exists()) {
                        $slug = $baseSlug . '-' . $counter++;
                    }
                    $audiobookData['slug'] = $slug;

                    $book = null;
                    if (!$isDryRun) {
                        $book = Audiobook::updateOrCreate(
                            ['librivox_id' => $librivoxId],
                            $audiobookData
                        );

                        if ($book->wasRecentlyCreated) {
                            $createdCount++;
                        } elseif ($book->wasChanged()) {
                            $updatedCount++;
                        }
                    } else {
                        $book = (object)['id' => 99999, 'librivox_id' => $librivoxId, 'title' => $title, 'narrator' => $narratorName];
                    }

                    // --- Section Processing ---
                    $audioTracks = $this->libriVoxService->fetchAudiobookTracks($librivoxId);

                    if (!empty($audioTracks)) {
                        if (!$isDryRun) {
                            AudiobookSection::where('audiobook_id', $book->id)->delete();
                        }

                        $sectionNumber = 1;
                        foreach ($audioTracks as $track) {
                            try {
                                $sectionTitle = $track['section_title'] ?? 'Part ' . $sectionNumber;
                                $sourceUrl = $track['listen_url'] ?? null;
                                $duration = $track['playtime'] ?? null;

                                if (empty($sourceUrl)) {
                                    $this->warn("Skipping section '{$sectionTitle}' for book '{$book->title}' due to missing source URL.");
                                    continue;
                                }

                                if (!$isDryRun) {
                                    AudiobookSection::create([
                                        'audiobook_id' => $book->id,
                                        'title' => $sectionTitle,
                                        'section_number' => $sectionNumber,
                                        'source_url' => $sourceUrl,
                                        'duration' => $duration,
                                        'reader_name' => $track['reader'] ?? $book->narrator,
                                    ]);
                                }
                                $sectionNumber++;
                            } catch (\Exception $e) {
                                Log::error("Failed to process section for book ID {$librivoxId}, section number {$sectionNumber}: " . $e->getMessage(), [
                                    'exception' => $e,
                                    'track_data' => $track,
                                ]);
                                $this->error("Error processing section for book '{$book->title}'. See logs for details.");
                            }
                        }
                    } else {
                        $this->warn("No audio tracks found for sections for book: {$book->title} (LibriVox ID: {$librivoxId}).");
                    }

                    $progressBar->advance();
                } catch (\Exception $e) {
                    $errors[] = [
                        'id' => $librivoxId,
                        'error' => $e->getMessage()
                    ];
                    Log::error("Failed to import audiobook with LibriVox ID {$librivoxId}: " . $e->getMessage(), [
                        'exception' => $e,
                        'api_book_data' => $apiBook,
                    ]);
                    $this->error("Error importing audiobook '{$title}'. See logs for details. Skipping to next book.");
                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            $this->info("\nProcessing complete.");
            if ($isDryRun) {
                $this->info("DRY RUN finished. No changes were made to the database.");
            } else {
                $this->info("{$createdCount} audiobooks created, {$updatedCount} audiobooks updated.");
                // Update progress only if not a dry run and offset was not overridden by CLI
                if ($cliOffset === 0) {
                    $progress->offset = $currentOffset + count($apiAudiobooks);
                    $progress->errors = json_encode($errors);
                    $progress->save();
                    $this->info("Next import will start from offset: {$progress->offset}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            Log::error("Full import failed: " . $e->getMessage(), ['exception' => $e]);
            $this->error("Import failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
