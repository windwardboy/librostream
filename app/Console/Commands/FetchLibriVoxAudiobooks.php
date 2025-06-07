<?php

namespace App\Console\Commands;

use App\Models\Audiobook;
use App\Models\AudiobookSection; // Import the AudiobookSection model
use App\Models\Category;
use App\Services\LibriVoxService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FetchLibriVoxAudiobooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'librivox:fetch {--limit=10 : Number of audiobooks to fetch per request} {--offset=0 : Starting offset for fetching audiobooks} {--since= : Fetch audiobooks added since this date (YYYY-MM-DD)}';

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
        $since = $this->option('since');

        $this->info("Fetching {$limit} audiobooks from LibriVox API (offset: {$offset}" . ($since ? ", since: {$since}" : "") . ")...");

        $apiParams = [];
        if ($since) {
            $apiParams['since'] = $since;
        }

        $apiAudiobooks = $this->libriVoxService->fetchAudiobooks(limit: $limit, offset: $offset, params: $apiParams);

        if (empty($apiAudiobooks)) {
            $this->info('No audiobooks found or API request failed.');
            return Command::SUCCESS;
        }

        $this->info(count($apiAudiobooks) . ' audiobooks fetched from API. Processing...');
        $progressBar = $this->output->createProgressBar(count($apiAudiobooks));
        $progressBar->start();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($apiAudiobooks as $apiBook) {
            if (empty($apiBook['id']) || empty($apiBook['title'])) {
                $this->warn("Skipping an entry due to missing ID or title.");
                $progressBar->advance();
                continue;
            }

            // Category
            $categoryName = 'Uncategorized'; // Default category
            if (!empty($apiBook['genres']) && !empty($apiBook['genres'][0]['name'])) {
                $categoryName = trim($apiBook['genres'][0]['name']);
            } else {
                 $categoryName = 'Uncategorized'; // Default category
            }

            // Generate slug for the category
            $categorySlug = Str::slug($categoryName);

            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['slug' => $categorySlug] // Save the generated slug
            );

            // If the category already existed but didn't have a slug, update it
            if (!$category->wasRecentlyCreated && (is_null($category->slug) || $category->slug !== $categorySlug)) {
                $category->slug = $categorySlug;
                $category->save();
                $this->info("Populated/Updated slug for category: {$category->name} (Slug: {$category->slug})");
            }


            // Author
            $authorName = 'Unknown Author';
            if (!empty($apiBook['authors']) && !empty($apiBook['authors'][0]['last_name'])) {
                $authorName = trim($apiBook['authors'][0]['first_name'] . ' ' . $apiBook['authors'][0]['last_name']);
            }

            // Narrator
            $narratorName = 'Various Readers'; // Default
            if (!empty($apiBook['sections']) && !empty($apiBook['sections'][0]['readers']) && !empty($apiBook['sections'][0]['readers'][0]['display_name'])) {
                // Check if all readers are the same for all sections (simplistic check)
                $firstReader = $apiBook['sections'][0]['readers'][0]['display_name'];
                $allSameReader = true;
                foreach ($apiBook['sections'] as $section) {
                    if (empty($section['readers']) || $section['readers'][0]['display_name'] !== $firstReader) {
                        $allSameReader = false;
                        break;
                    }
                }
                if ($allSameReader) {
                    $narratorName = $firstReader;
                }
            }


            // Duration from totaltimesecs
            $durationStr = 'N/A';
            if (isset($apiBook['totaltimesecs']) && is_numeric($apiBook['totaltimesecs'])) {
                $durationStr = gmdate('H:i:s', (int)$apiBook['totaltimesecs']);
            } elseif (!empty($apiBook['totaltime'])) {
                 $durationStr = $apiBook['totaltime']; // Fallback to string if secs not available
            }


            // Description (strip HTML tags)
            $description = !empty($apiBook['description']) ? strip_tags($apiBook['description']) : 'No description available.';
            $description = Str::limit($description, 1000); // Limit description length if necessary for DB

            // Main Audiobook Data
            $audiobookData = [
                'title' => trim($apiBook['title']),
                'author' => $authorName,
                'narrator' => $narratorName,
                'description' => $description,
                'cover_image' => null, // Placeholder - implement strategy later
                'duration' => $durationStr, // Total duration
                'source_url' => null, // Main source_url is now null, sections have the actual files
                'category_id' => $category->id,
                'language' => $apiBook['language'] ?? 'English',
                'librivox_url' => $apiBook['url_librivox'] ?? null,
            ];

            // Attempt to fetch cover image from Archive.org via RSS feed URL
            $coverImageUrl = null;
            if (!empty($apiBook['url_rss'])) {
                $this->info("Attempting to fetch cover image for '{$apiBook['title']}' from RSS URL: {$apiBook['url_rss']}");
                try {
                    $response = Http::timeout(60)->get($apiBook['url_rss']); // Increased timeout to 60 seconds

                    if ($response->successful()) {
                        $this->info("Successfully fetched RSS URL. Parsing HTML...");
                        // Regex to find the image URL within the <itunes:image> tag
                        if (preg_match('/<itunes:image[^>]+href="([^"]+)"/i', $response->body(), $matches)) {
                            $coverImageUrl = $matches[1];
                            $this->info("Found image URL in itunes:image tag: {$coverImageUrl}");
                        } elseif (preg_match('/<img[^>]+src="([^"]+)"/i', $response->body(), $matches)) {
                             // Fallback regex to find the first image src in the body if itunes:image is not found
                            $coverImageUrl = $matches[1];
                            if (!filter_var($coverImageUrl, FILTER_VALIDATE_URL)) {
                                // Attempt to make it absolute if it looks like a relative path
                                $baseUrl = parse_url($apiBook['url_rss'], PHP_URL_SCHEME) . '://' . parse_url($apiBook['url_rss'], PHP_URL_HOST);
                                $coverImageUrl = rtrim($baseUrl, '/') . '/' . ltrim($coverImageUrl, '/');
                                $this->info("Found relative image URL in img tag, converted to absolute: {$coverImageUrl}");
                            } else {
                                $this->info("Found absolute image URL in img tag: {$coverImageUrl}");
                            }
                        } else {
                            $this->warn("No image URL found in itunes:image or img tags for book '{$apiBook['title']}' (ID: {$apiBook['id']}).");
                        }
                    } else {
                        $this->warn("Failed to fetch RSS URL for book '{$apiBook['title']}' (ID: {$apiBook['id']}). Status: " . $response->status());
                    }
                } catch (\Exception $e) {
                    $this->warn("Error fetching or parsing RSS URL for book '{$apiBook['title']}' (ID: {$apiBook['id']}): " . $e->getMessage());
                }
            } else {
                $this->info("No RSS URL available for book '{$apiBook['title']}' (ID: {$apiBook['id']}). Skipping cover image fetch.");
            }

            // Update cover_image in the data array if found
            $audiobookData['cover_image'] = $coverImageUrl;

            // Generate a unique slug for the audiobook
            $baseSlug = Str::slug($apiBook['title']);
            $slug = $baseSlug;
            $counter = 1;

            // Check for slug uniqueness and append counter if necessary
            while (Audiobook::where('slug', $slug)->where('librivox_id', '!=', $apiBook['id'])->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $audiobookData['slug'] = $slug;

            $this->info("Processing book: {$apiBook['title']} (ID: {$apiBook['id']}), Slug: {$slug}");
            $this->info("Data for updateOrCreate: " . json_encode($audiobookData));

            // Create or Update the main Audiobook record
            // Use updateOrCreate with librivox_id as the key
            $book = Audiobook::updateOrCreate(
                ['librivox_id' => $apiBook['id']],
                $audiobookData // Pass all data, including the generated slug
            );

            // Explicitly save the slug if it was null (for existing records before slug migration)
            if (is_null($book->slug)) {
                 $baseSlug = Str::slug($apiBook['title']);
                 $slug = $baseSlug;
                 $counter = 1;

                 // Check for slug uniqueness and append counter if necessary
                 while (Audiobook::where('slug', $slug)->where('id', '!=', $book->id)->exists()) {
                     $slug = $baseSlug . '-' . $counter++;
                 }
                 $book->slug = $slug;
                 $book->save(); // Save the model to update the slug
                 $updatedCount++; // Count this as an update
                 $this->info("Populated slug for existing audiobook: {$book->title} (Slug: {$slug})");

            } elseif ($book->wasRecentlyCreated) {
                $createdCount++;
                $this->info("Created audiobook: {$book->title} (Slug: {$book->slug})");
            } elseif ($book->wasChanged()) {
                $updatedCount++;
                $this->info("Updated audiobook: {$book->title} (Slug: {$book->slug})");
            } else {
                $this->info("Audiobook matched existing record, no changes: {$book->title} (Slug: {$book->slug})");
            }

            // Process Sections
            if (!empty($apiBook['sections'])) {
                // Use updateOrCreate for sections based on audiobook_id and librivox_section_id
                foreach ($apiBook['sections'] as $apiSection) {
                    // Skip section if no listen_url is found or librivox_section_id is missing
                    if (empty($apiSection['listen_url']) || empty($apiSection['id'])) {
                         $this->warn("Skipping section '{$apiSection['title']}' for book '{$apiBook['title']}' (ID: {$apiBook['id']}) due to missing source_url or librivox_section_id.");
                         continue;
                    }

                    // Duration for section (from playtime in seconds)
                    $sectionDurationStr = 'N/A';
                    if (isset($apiSection['playtime']) && is_numeric($apiSection['playtime'])) {
                         $sectionDurationStr = gmdate('H:i:s', (int)$apiSection['playtime']);
                    } elseif (!empty($apiSection['playtime_string'])) {
                         $sectionDurationStr = $apiSection['playtime_string']; // Fallback
                    }

                    // Reader Name for section
                    $sectionReaderName = 'Unknown Reader';
                    if (!empty($apiSection['readers']) && !empty($apiBook['sections'][0]['readers'][0]['display_name'])) {
                        // Check if all readers are the same for all sections (simplistic check)
                        $firstReader = $apiBook['sections'][0]['readers'][0]['display_name'];
                        $allSameReader = true;
                        foreach ($apiBook['sections'] as $section) {
                            if (empty($section['readers']) || empty($section['readers'][0]['display_name']) || $section['readers'][0]['display_name'] !== $firstReader) {
                                $allSameReader = false;
                                break;
                            }
                        }
                        if ($allSameReader) {
                            $narratorName = $firstReader;
                        }
                    }


                    $sectionData = [
                        'audiobook_id' => $book->id,
                        'section_number' => (int) $apiSection['section_number'],
                        'title' => trim(strip_tags($apiSection['title'])), // Strip HTML tags
                        'source_url' => $apiSection['listen_url'],
                        'duration' => $sectionDurationStr,
                        'reader_name' => $sectionReaderName, // Add reader name
                        'librivox_section_id' => $apiSection['id'], // Ensure librivox_section_id is used
                    ];

                    // Use updateOrCreate based on audiobook_id and librivox_section_id
                    AudiobookSection::updateOrCreate(
                        ['audiobook_id' => $book->id, 'librivox_section_id' => $apiSection['id']],
                        $sectionData
                    );
                }
            } else {
                 $this->warn("No sections found for book '{$apiBook['title']}' (ID: {$apiBook['id']}).");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nProcessing complete.");
        $this->info("{$createdCount} audiobooks created, {$updatedCount} audiobooks updated.");

        return Command::SUCCESS;
    }
}
