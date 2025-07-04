<?php

namespace App\Console\Commands;

use App\Models\Audiobook;
use App\Models\AudiobookSection;
use App\Models\Category;
use App\Services\LibriVoxService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config; // Import Config facade
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchLibriVoxAudiobooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'librivox:fetch {--limit=10 : Number of audiobooks to fetch per request} {--offset=0 : Starting offset for fetching audiobooks} {--since= : Fetch audiobooks added since this date (YYYY-MM-DD)} {--dry-run : Simulate the import without writing to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch audiobooks from the LibriVox API and store them in the database.';

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
        $offset = (int) $this->option('offset');
        $since = (string) $this->option('since'); // Cast to string
        $isDryRun = (bool) $this->option('dry-run'); // Cast to boolean

        $this->info("Fetching {$limit} audiobooks from LibriVox API (offset: {$offset}" . ($since ? ", since: {$since}" : "") . ")...");
        if ($isDryRun) {
            $this->info('DRY RUN: No data will be written to the database.');
        }

        $apiParams = [];
        if ($since) {
            // LibriVox API 'since' parameter takes a UNIX timestamp
            $apiParams['since'] = strtotime($since);
        }

        $apiResponse = $this->libriVoxService->fetchAudiobooks(limit: $limit, offset: $offset, params: $apiParams);
        $apiAudiobooks = $apiResponse['books'] ?? [];
        $numFound = $apiResponse['total_found'] ?? 0;

        if (empty($apiAudiobooks)) {
            $this->info('No audiobooks found in this batch or API request failed.');
            return Command::SUCCESS;
        }

        $this->info(count($apiAudiobooks) . ' audiobooks fetched from API. Processing...');
        $progressBar = $this->output->createProgressBar(count($apiAudiobooks));
        $progressBar->start();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($apiAudiobooks as $apiBook) {
            try {
                // LibriVox API uses 'id' as the unique project ID
                $librivoxId = $apiBook['id'] ?? null;
                $title = $apiBook['title'] ?? null;

                // Log the raw ID and title before processing
                Log::info('Processing LibriVox API Book:', [
                    'raw_librivox_id' => $librivoxId,
                    'raw_title' => $title,
                    'raw_api_book' => $apiBook // Log the full raw data for debugging
                ]);

                // Ensure ID and title are valid
                if (empty($librivoxId) || empty($title)) {
                    $this->warn("Skipping an entry because LibriVox ID or title is empty. ID: '{$librivoxId}', Title: '{$title}'. Full raw data: " . json_encode($apiBook));
                    $progressBar->advance();
                    continue;
                }

                // Category (from 'genres' field)
                $categoryName = 'Uncategorized'; // Default category
                if (!empty($apiBook['genres']) && is_array($apiBook['genres'])) {
                    $genre = reset($apiBook['genres']); // Take the first genre as the category
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
                        $this->info("Populated/Updated slug for category: {$category->name} (Slug: {$category->slug})");
                    }
                } else {
                    $category = (object)['id' => 99999, 'name' => $categoryName, 'slug' => $categorySlug]; // Mock category for dry run
                }


                // Author (from 'authors' field)
                $authorName = 'Unknown Author';
                if (!empty($apiBook['authors']) && is_array($apiBook['authors'])) {
                    $author = reset($apiBook['authors']); // Take the first author
                    $authorName = trim(($author['first_name'] ?? '') . ' ' . ($author['last_name'] ?? ''));
                    if (empty(trim($authorName))) {
                        $authorName = 'Unknown Author';
                    }
                }

                // Narrator (from 'sections' or 'description' - LibriVox API provides 'sections' with 'reader' field)
                $narratorName = 'Various Readers'; // Default
                if (!empty($apiBook['sections']) && is_array($apiBook['sections'])) {
                    // Check if there's a single reader for all sections
                    $uniqueReaders = collect($apiBook['sections'])->pluck('reader')->unique()->filter()->all();
                    if (count($uniqueReaders) === 1) {
                        $narratorName = reset($uniqueReaders);
                    } elseif (count($uniqueReaders) > 1) {
                        $narratorName = 'Various Readers';
                    }
                }


                // Duration from 'totaltime' (e.g., "49:43:15")
                $durationStr = $apiBook['totaltime'] ?? 'N/A';

                // Description (strip HTML tags if necessary)
                $description = !empty($apiBook['description']) ? strip_tags((string) $apiBook['description']) : 'No description available.';
                $description = Str::limit($description, 1000); // Limit description length for DB

                // Language conversion using config file
                $languageCode = $apiBook['language'] ?? 'English'; // LibriVox API often provides full name, but fallback to code
                $fullLanguageName = Config::get('languages.' . strtolower($languageCode), $languageCode); // Use config mapping

                // Cover Image Handling: Prioritize direct coverart links from LibriVox API
                $coverImage = null;
                if (isset($apiBook['coverart_jpg']) && filter_var($apiBook['coverart_jpg'], FILTER_VALIDATE_URL)) {
                    $coverImage = $apiBook['coverart_jpg'];
                } elseif (isset($apiBook['coverart_thumbnail']) && filter_var($apiBook['coverart_thumbnail'], FILTER_VALIDATE_URL)) {
                    $coverImage = $apiBook['coverart_thumbnail'];
                } elseif (isset($apiBook['url_cover_image']) && filter_var($apiBook['url_cover_image'], FILTER_VALIDATE_URL)) {
                    // Fallback to url_cover_image if it's a valid URL
                    $coverImage = $apiBook['url_cover_image'];
                } elseif (isset($apiBook['url_librivox'])) {
                    // Last resort: try to construct from url_librivox, removing potential double slashes
                    $constructedUrl = rtrim($apiBook['url_librivox'], '/') . '/cover_small.jpg';
                    if (filter_var($constructedUrl, FILTER_VALIDATE_URL)) {
                        $coverImage = $constructedUrl;
                    }
                }

                // Final fallback to a generic placeholder if no valid image URL is found
                if (empty($coverImage) || !filter_var($coverImage, FILTER_VALIDATE_URL)) {
                    $coverImage = 'https://librivox.org/images/librivox_logo_small.png'; // Default placeholder
                }

                // Main Audiobook Data
                $audiobookData = [
                    'title' => $title,
                    'author' => $authorName,
                    'narrator' => $narratorName,
                    'description' => $description,
                    'cover_image' => $coverImage,
                    'duration' => $durationStr,
                    'source_url' => null, // Main source_url is null, sections will have actual files
                    'category_id' => $category->id,
                    'language' => $fullLanguageName,
                    'librivox_url' => $apiBook['url_librivox'] ?? null, // Direct LibriVox project page URL
                    'librivox_id' => $librivoxId, // Use LibriVox project ID as librivox_id
                ];

                // Generate a unique slug for the audiobook
                $baseSlug = Str::slug($title);
                $slug = $baseSlug;
                $counter = 1;

                // Check for slug uniqueness and append counter if necessary
                while (Audiobook::where('slug', $slug)->where('librivox_id', '!=', $librivoxId)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }
                $audiobookData['slug'] = $slug;

                $this->info("Processing book: {$title} (LibriVox ID: {$librivoxId}), Slug: {$slug}");
                Log::info("Data for updateOrCreate:", $audiobookData);

                $book = null;
                if (!$isDryRun) {
                    // Create or Update the main Audiobook record
                    $book = Audiobook::updateOrCreate(
                        ['librivox_id' => $librivoxId],
                        $audiobookData
                    );

                    if ($book->wasRecentlyCreated) {
                        $createdCount++;
                        $this->info("Created audiobook: {$book->title} (Slug: {$book->slug})");
                    } elseif ($book->wasChanged()) {
                        $updatedCount++;
                        $this->info("Updated audiobook: {$book->title} (Slug: {$book->slug})");
                    } else {
                        $this->info("Audiobook matched existing record, no changes: {$book->title} (Slug: {$book->slug})");
                    }
                } else {
                    $this->info("DRY RUN: Would create/update audiobook: {$title} (Slug: {$slug})");
                    $book = (object)['id' => 99999, 'librivox_id' => $librivoxId, 'title' => $title, 'narrator' => $narratorName]; // Mock book for dry run
                }


                // --- Section Processing ---
                // Fetch detailed track metadata for sections using the LibriVox audiotracks API
                $audioTracks = $this->libriVoxService->fetchAudiobookTracks($librivoxId);

                if (!empty($audioTracks)) {
                    if (!$isDryRun) {
                        // Clear existing sections for this audiobook to prevent duplicates/stale data
                        AudiobookSection::where('audiobook_id', $book->id)->delete();
                        $this->info("Cleared existing sections for audiobook: {$book->title}");
                    } else {
                        $this->info("DRY RUN: Would clear existing sections for audiobook: {$book->title}");
                    }

                    $sectionNumber = 1;
                    foreach ($audioTracks as $track) {
                        try {
                            $sectionTitle = $track['section_title'] ?? 'Part ' . $sectionNumber;
                            $sourceUrl = $track['listen_url'] ?? null; // Direct listen URL from LibriVox API
                            $duration = $track['playtime'] ?? null; // Playtime in H:M:S format

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
                                    'reader_name' => $track['reader'] ?? $book->narrator, // Prefer section-specific reader, fallback to book narrator
                                ]);
                            } else {
                                $this->info("DRY RUN: Would create section '{$sectionTitle}' for audiobook: {$book->title}");
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
                    $this->info("Imported " . (count($audioTracks)) . " sections for audiobook: {$book->title}");
                } else {
                    $this->warn("No audio tracks found for sections for book: {$book->title} (LibriVox ID: {$librivoxId}).");
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                Log::error("Failed to import audiobook with LibriVox ID {$librivoxId}: " . $e->getMessage(), [
                    'exception' => $e,
                    'api_book_data' => $apiBook,
                ]);
                $this->error("Error importing audiobook '{$title}'. See logs for details. Skipping to next book.");
                $progressBar->advance(); // Ensure progress bar advances even on error
            }
        }

        $progressBar->finish();
        $this->info("\nProcessing complete.");
        if ($isDryRun) {
            $this->info("DRY RUN finished. No changes were made to the database.");
        } else {
            $this->info("{$createdCount} audiobooks created, {$updatedCount} audiobooks updated.");
        }

        return Command::SUCCESS;
    }

    /**
     * Converts ISO 639-2/3 language codes to full language names.
     *
     * @param string $code The ISO language code.
     * @return string The full language name.
     */
    private function convertLanguageCodeToName(string $code): string
    {
        // Use the language mapping from the config file
        return Config::get('languages.' . strtolower($code), ucfirst(strtolower($code)));
    }
}
