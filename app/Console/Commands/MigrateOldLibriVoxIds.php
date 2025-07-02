<?php

namespace App\Console\Commands;

use App\Models\Audiobook;
use App\Services\LibriVoxService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MigrateOldLibriVoxIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'librivox:migrate-old-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate old numeric LibriVox IDs to new Archive.org string identifiers.';

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
        $this->info("Starting migration of old LibriVox IDs to Archive.org identifiers...");

        $audiobooksToMigrate = Audiobook::whereRaw('LENGTH(librivox_id) <= 5') // Likely old numeric IDs
                                        ->whereRaw('librivox_id REGEXP \'^[0-9]+$\'') // Ensure it's purely numeric
                                        ->get();

        if ($audiobooksToMigrate->isEmpty()) {
            $this->info("No old numeric LibriVox IDs found to migrate. Migration complete.");
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($audiobooksToMigrate->count());
        $progressBar->start();

        $migratedCount = 0;
        $skippedCount = 0;

        foreach ($audiobooksToMigrate as $audiobook) {
            $oldIdentifier = (string) ($audiobook->librivox_id ?? '');
            $title = (string) ($audiobook->title ?? '');
            $author = (string) ($audiobook->author ?? '');

            // Search Archive.org by title and author to find the correct identifier
            $response = $this->libriVoxService->fetchAudiobooks(
                limit: 1,
                offset: 0,
                params: [
                    'q' => "title:(\"{$title}\") AND creator:(\"{$author}\") AND subject:(librivox) AND mediatype:audio"
                ]
            );

            $foundBooks = $response['books'] ?? [];
            $newIdentifier = null;

            if (!empty($foundBooks)) {
                // Try to find an exact match or the first reasonable one
                foreach ($foundBooks as $foundBook) {
                    $foundTitle = (string) ($foundBook['title'] ?? '');
                    $foundCreator = (string) ($foundBook['creator'] ?? '');

                    if (Str::slug($foundTitle) === Str::slug($title) &&
                        Str::contains(strtolower($foundCreator), strtolower($author))) {
                        $newIdentifier = (string) ($foundBook['identifier'] ?? '');
                        break;
                    }
                }
                if (!$newIdentifier && isset($foundBooks[0]['identifier'])) {
                    $newIdentifier = (string) ($foundBooks[0]['identifier'] ?? ''); // Fallback to first result
                }
            }

            if ($newIdentifier && $newIdentifier !== $oldIdentifier) {
                // Check if the new identifier already exists for another book (shouldn't happen with unique IDs)
                $existingBookWithNewId = Audiobook::where('librivox_id', $newIdentifier)->first();
                if ($existingBookWithNewId && $existingBookWithNewId->id !== $audiobook->id) {
                    $this->warn("\nSkipping migration for '{$title}' (ID: {$audiobook->id}). New identifier '{$newIdentifier}' already belongs to another audiobook (ID: {$existingBookWithNewId->id}).");
                    $skippedCount++;
                } else {
                    $audiobook->librivox_id = $newIdentifier;
                    $audiobook->save();
                    $migratedCount++;
                }
            } else {
                $this->warn("\nCould not find a new Archive.org identifier for '{$title}' (Old ID: {$oldIdentifier}) or identifier is unchanged.");
                $skippedCount++;
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nMigration complete.");
        $this->info("{$migratedCount} audiobooks migrated, {$skippedCount} skipped.");

        return Command::SUCCESS;
    }
}
