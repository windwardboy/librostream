<?php

namespace App\Console\Commands;

use App\Models\Audiobook;
use App\Models\AudiobookSection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchAudiobookSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'librivox:fetch-sections {--limit=100 : Number of audiobooks to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store audiobook sections (audio files) from Archive.org for existing audiobooks.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $processedCount = 0;
        $sectionsCreated = 0;
        $sectionsUpdated = 0;

        $this->info("Starting to fetch audiobook sections from Archive.org...");

        // Fetch audiobooks that don't have sections yet, or have very few sections,
        // AND have a valid-looking Archive.org identifier (non-numeric string)
        $audiobooks = Audiobook::where(function ($query) {
                                    $query->whereDoesntHave('sections')
                                          ->orWhereHas('sections', function ($subQuery) {
                                              $subQuery->havingRaw('count(*) < 2'); // Books with less than 2 sections
                                          });
                                })
                                ->whereNotNull('librivox_id')
                                ->whereRaw('LENGTH(librivox_id) > 5') // Filter out short, old numeric IDs
                                ->whereRaw('librivox_id REGEXP \'^[a-zA-Z0-9_.-]+$\'') // Ensure it looks like an Archive.org identifier
                                ->limit($limit)
                                ->get();

        if ($audiobooks->isEmpty()) {
            $this->info("No audiobooks found needing section processing or matching valid Archive.org identifiers. Sync complete.");
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($audiobooks->count());
        $progressBar->start();

        foreach ($audiobooks as $audiobook) {
            $identifier = $audiobook->librivox_id;
            // Double check identifier format here as well, though the query should filter most
            if (empty($identifier) || is_numeric($identifier) || Str::length($identifier) <= 5) {
                $this->warn("\nSkipping audiobook '{$audiobook->title}' (ID: {$audiobook->id}) due to invalid Archive.org identifier: '{$identifier}'.");
                $progressBar->advance();
                continue;
            }

            $itemApiUrl = "https://archive.org/details/{$identifier}/files";

            try {
                $response = Http::timeout(120)->get($itemApiUrl); // Increased timeout for potentially large file lists

                if ($response->successful()) {
                    $data = $response->json();
                    $files = $data['files'] ?? [];

                    if (empty($files)) {
                        $this->warn("\nNo files found for audiobook '{$audiobook->title}' (Identifier: {$identifier}).");
                        $progressBar->advance();
                        continue;
                    }

                    // Delete existing sections for this audiobook to re-sync them
                    $audiobook->sections()->delete();

                    $sectionNumber = 1;
                    foreach ($files as $file) {
                        // Only process MP3 files and exclude non-audio files like "cover.jpg" or "index.mp3" (full book)
                        // Also exclude files that are clearly not chapters (e.g., very short, or specific formats)
                        if (isset($file['format']) && Str::contains(strtolower($file['format']), 'mp3') &&
                            !Str::contains(strtolower($file['name']), ['_64kb.mp3', '_128kb.mp3', 'index.mp3', 'cover.mp3', '.jpg', '.jpeg', '.png', '.txt', '.xml', '.json', '.torrent'])) {

                            $sectionTitle = pathinfo($file['name'], PATHINFO_FILENAME);
                            $sectionUrl = "https://archive.org/download/{$identifier}/{$file['name']}";

                            // Attempt to extract section number from filename if present (e.g., "book_chapter_01.mp3")
                            if (preg_match('/_(\d+)\.mp3$/', $file['name'], $matches)) {
                                $sectionNumber = (int) $matches[1];
                            } elseif (preg_match('/^(\d+)_/', $file['name'], $matches)) {
                                $sectionNumber = (int) $matches[1];
                            }
                            // If no number is found in the filename, the original $sectionNumber will be used,
                            // and it will be incremented at the end of the loop.

                            $section = AudiobookSection::updateOrCreate(
                                [
                                    'audiobook_id' => $audiobook->id,
                                    'section_number' => $sectionNumber,
                                ],
                                [
                                    'title' => $sectionTitle,
                                    'source_url' => $sectionUrl,
                                    'duration' => $file['length'] ?? null, // Archive.org API might have 'length' for duration
                                    'reader_name' => $file['creator'] ?? 'Various Readers', // Sometimes creator is per file
                                ]
                            );

                            if ($section->wasRecentlyCreated) {
                                $sectionsCreated++;
                            } elseif ($section->wasChanged()) {
                                $sectionsUpdated++;
                            }
                            $sectionNumber++; // Increment for next section if not explicitly numbered
                        }
                    }
                    $processedCount++;

                } else {
                    $this->warn("\nFailed to fetch files for audiobook '{$audiobook->title}' (Identifier: {$identifier}). Status: {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->error("\nError fetching sections for '{$audiobook->title}' (Identifier: {$identifier}): " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nSection fetching complete.");
        $this->info("Processed {$processedCount} audiobooks. {$sectionsCreated} sections created, {$sectionsUpdated} sections updated.");

        return Command::SUCCESS;
    }
}
