<?php

namespace App\Console\Commands;

use App\Models\Audiobook;
use App\Models\Category;
use App\Services\LibriVoxService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncLibriVoxAudiobooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'librivox:sync {--batch-size=100 : The number of audiobooks to fetch per API request}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automated command to sync all audiobooks from Archive.org, updating existing ones and creating new ones.';

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
        $batchSize = (int) $this->option('batch-size');
        $totalCreated = 0;
        $totalUpdated = 0;

        // Iterate through years to fetch all audiobooks
        $startYear = 2005; // LibriVox was founded in 2005
        $endYear = (int) date('Y');

        for ($year = $startYear; $year <= $endYear; $year++) {
            $this->info("\nFetching audiobooks for the year {$year}...");
            $dateRange = "{$year}-01-01 TO {$year}-12-31";

            $offset = 0;
            $totalForYear = 0;
            $processedForYear = 0;

            do {
                $response = $this->libriVoxService->fetchAudiobooks(limit: $batchSize, offset: $offset, params: ['date_range' => $dateRange]);
                $apiAudiobooks = $response['books'] ?? [];
                $totalForYear = $response['total_found'] ?? 0;

                if (empty($apiAudiobooks)) {
                    break;
                }

                foreach ($apiAudiobooks as $apiBook) {
                    $identifier = (string) ($apiBook['identifier'] ?? '');
                    $title = (string) ($apiBook['title'] ?? '');

                    if (empty($identifier) || empty($title)) {
                        $this->warn("\nSkipping an entry due to missing identifier or title. Raw data: " . json_encode($apiBook));
                        continue;
                    }

                    $identifier = trim($identifier);
                    $title = trim($title);

                    // Category (from 'subject' field)
                    $categoryName = 'Uncategorized';
                    if (!empty($apiBook['subject'])) {
                        $subjects = is_array($apiBook['subject']) ? $apiBook['subject'] : (is_string($apiBook['subject']) ? explode(';', $apiBook['subject']) : []);
                        $categoryName = trim((string) ($subjects[0] ?? 'Uncategorized'));
                    }

                    $categorySlug = Str::slug($categoryName);
                    $category = Category::firstOrCreate(['name' => $categoryName], ['slug' => $categorySlug]);

                    if (!$category->wasRecentlyCreated && (is_null($category->slug) || $category->slug !== $categorySlug)) {
                        $category->slug = $categorySlug;
                        $category->save();
                    }

                    // Author (from 'creator' field)
                    $authorName = 'Unknown Author';
                    if (!empty($apiBook['creator'])) {
                        $creators = is_array($apiBook['creator']) ? $apiBook['creator'] : (is_string($apiBook['creator']) ? explode(';', (string) $apiBook['creator']) : []);
                        $authorName = trim((string) ($creators[0] ?? 'Unknown Author'));
                    }

                    // --- Enhanced Narrator Extraction Logic (more robust) ---
                    $narratorName = 'Various Readers'; // Default
                    $descriptionString = (string) ($apiBook['description'] ?? '');

                    if (!empty($descriptionString)) {
                        // Attempt to find "Read by [Narrator Name]" in description
                        if (preg_match('/Read (?:in [^ ]+ )?by ([^.]+)/i', $descriptionString, $matches)) {
                            $extractedNarrator = trim((string) ($matches[1] ?? ''));
                            // Filter out common phrases that are not actual names
                            if (!empty($extractedNarrator) && !Str::contains(strtolower($extractedNarrator), ['librivox volunteers', 'volunteer readers', 'various readers', 'various'])) {
                                $narratorName = $extractedNarrator;
                            }
                        }
                    }
                    // If still default, try to use creator if it seems like a single person
                    if ($narratorName === 'Various Readers' && !empty($apiBook['creator'])) {
                        $creators = is_array($apiBook['creator']) ? $apiBook['creator'] : (is_string($apiBook['creator']) ? explode(';', (string) $apiBook['creator']) : []);
                        if (count($creators) === 1 && !Str::contains(strtolower((string) ($creators[0] ?? '')), ['various', 'volunteers'])) {
                            $narratorName = trim((string) ($creators[0] ?? ''));
                        }
                    }
                    // --- End Enhanced Narrator Extraction Logic ---

                    $durationStr = (string) ($apiBook['runtime'] ?? 'N/A');
                    $description = !empty($descriptionString) ? strip_tags($descriptionString) : 'No description available.';
                    $description = Str::limit($description, 1000);

                    $audiobookData = [
                        'title' => $title,
                        'author' => $authorName,
                        'narrator' => $narratorName,
                        'description' => $description,
                        'cover_image' => $identifier ? "https://archive.org/services/img/{$identifier}" : null,
                        'duration' => $durationStr,
                        'source_url' => null,
                        'category_id' => $category->id,
                        'language' => (string) ($apiBook['language'] ?? 'English'),
                        'librivox_url' => (string) ($apiBook['url'] ?? null),
                    ];

                    $baseSlug = Str::slug($title);
                    $slug = $baseSlug;
                    $counter = 1;
                    while (Audiobook::where('slug', $slug)->where('librivox_id', '!=', $identifier)->exists()) {
                        $slug = $baseSlug . '-' . $counter++;
                    }
                    $audiobookData['slug'] = $slug;

                    $book = Audiobook::where('librivox_id', $identifier)->first();

                    if (!$book) {
                        $existingBySlug = Audiobook::where('slug', Str::slug($title))->first();
                        if ($existingBySlug && is_numeric($existingBySlug->librivox_id)) {
                            $book = $existingBySlug;
                            $book->librivox_id = $identifier; // Update to new Archive.org identifier
                            $book->save();
                            $this->info("Updated old audiobook ID for '{$title}' from numeric to '{$identifier}'.");
                            $totalUpdated++; // Count this as an update
                        }
                    }

                    if ($book) {
                        $book->fill($audiobookData);
                        if ($book->isDirty()) {
                            $book->save();
                            $totalUpdated++;
                        }
                    } else {
                        Audiobook::create(array_merge($audiobookData, ['librivox_id' => $identifier]));
                        $totalCreated++;
                    }
                }

                $offset += $batchSize;
                $processedForYear += count($apiAudiobooks);

            } while ($processedForYear < $totalForYear);
        }

        $this->info("\nSync complete.");
        $this->info("{$totalCreated} audiobooks created, {$totalUpdated} audiobooks updated.");

        return Command::SUCCESS;
    }
}
