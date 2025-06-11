<?php

namespace App\Console\Commands;

use App\Models\Audiobook;
use App\Models\AudiobookSection; // Import the AudiobookSection model
use App\Models\Category;
use App\Services\LibriVoxService; // This service now fetches from Archive.org
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
    protected $description = 'Fetch audiobooks from the Archive.org (LibriVox collection) API and store them in the database.';

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
        $since = $this->option('since'); // 'since' parameter might need custom handling for Archive.org

        $this->info("Fetching {$limit} audiobooks from Archive.org (LibriVox collection) API (offset: {$offset}" . ($since ? ", since: {$since}" : "") . ")...");

        $apiParams = [];
        if ($since) {
            // Archive.org's 'publicdate' field can be used with a range query
            // Example: 'publicdate:[2023-01-01 TO *]'
            // This would require adjusting the 'q' parameter in LibriVoxService
            // For simplicity, we'll pass it, but the service needs to interpret it.
            $apiParams['publicdate'] = $since;
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
            // Archive.org uses 'identifier' as the unique ID
            if (empty($apiBook['identifier']) || empty($apiBook['title'])) {
                $this->warn("Skipping an entry due to missing identifier or title.");
                $progressBar->advance();
                continue;
            }

            // Category (from 'subject' field)
            $categoryName = 'Uncategorized'; // Default category
            if (!empty($apiBook['subject'])) {
                // Subjects can be semicolon-separated, take the first one or process as needed
                $subjects = is_array($apiBook['subject']) ? $apiBook['subject'] : explode(';', $apiBook['subject']);
                $categoryName = trim($subjects[0]);
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

            // Author (from 'creator' field)
            $authorName = 'Unknown Author';
            if (!empty($apiBook['creator'])) {
                // Creator can be a string or an array, take the first one or process as needed
                $creators = is_array($apiBook['creator']) ? $apiBook['creator'] : explode(';', $apiBook['creator']);
                $authorName = trim($creators[0]);
            }

            // Narrator (Archive.org search API doesn't directly provide narrator, often part of creator/description)
            // For now, default to 'Various Readers' or try to extract from description if possible
            $narratorName = 'Various Readers';
            // You might need more advanced logic here if narrator is consistently in 'creator' or 'description'

            // Duration from 'runtime' (e.g., "01:23:45")
            $durationStr = $apiBook['runtime'] ?? 'N/A';

            // Description (strip HTML tags if necessary, Archive.org descriptions are usually plain text)
            $description = !empty($apiBook['description']) ? strip_tags($apiBook['description']) : 'No description available.';
            $description = Str::limit($description, 1000); // Limit description length if necessary for DB

            // Main Audiobook Data
            $audiobookData = [
                'title' => trim($apiBook['title']),
                'author' => $authorName,
                'narrator' => $narratorName, // Will be 'Various Readers' for now
                'description' => $description,
                'cover_image' => $apiBook['image'] ?? null, // 'image' field from Archive.org
                'duration' => $durationStr,
                'source_url' => null, // Main source_url is null, sections will have actual files
                'category_id' => $category->id,
                'language' => $apiBook['language'] ?? 'English',
                'librivox_url' => $apiBook['url'] ?? null, // 'url' field from Archive.org is the item page
            ];

            // Generate a unique slug for the audiobook
            $baseSlug = Str::slug($apiBook['title']);
            $slug = $baseSlug;
            $counter = 1;

            // Check for slug uniqueness and append counter if necessary
            while (Audiobook::where('slug', $slug)->where('librivox_id', '!=', $apiBook['identifier'])->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $audiobookData['slug'] = $slug;

            $this->info("Processing book: {$apiBook['title']} (Identifier: {$apiBook['identifier']}), Slug: {$slug}");
            $this->info("Data for updateOrCreate: " . json_encode($audiobookData));

            // Create or Update the main Audiobook record
            // Use updateOrCreate with librivox_id (now mapped to Archive.org identifier) as the key
            $book = Audiobook::updateOrCreate(
                ['librivox_id' => $apiBook['identifier']], // Use Archive.org identifier as librivox_id
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

            // --- Section Processing (Simplified/Removed for initial Archive.org integration) ---
            // The Archive.org search API does not provide detailed section data or direct audio URLs.
            // Fetching sections would require a separate API call per audiobook to the Item API (e.g., /metadata/{identifier}/files)
            // For now, we will skip section processing.
            $this->warn("Skipping section processing for book '{$apiBook['title']}' (Identifier: {$apiBook['identifier']}). Sections need to be fetched via Archive.org Item API.");
            // You might want to delete old sections if they exist from previous LibriVox imports
            // AudiobookSection::where('audiobook_id', $book->id)->delete();


            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nProcessing complete.");
        $this->info("{$createdCount} audiobooks created, {$updatedCount} audiobooks updated.");

        return Command::SUCCESS;
    }
}
