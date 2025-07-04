<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LibriVoxService;
use App\Models\Audiobook;
use App\Models\AudiobookSection;
use App\Models\ImportProgress;
use Illuminate\Support\Facades\Log;

class FullLibriVoxImport extends Command
{
    protected $signature = 'librivox:full-import 
        {--limit=5 : Number of audiobooks to process per run}
        {--skip=66 : Comma-separated IDs to skip}';

    protected $description = 'Reliably imports audiobooks from LibriVox API with progress tracking';

    public function handle(LibriVoxService $service)
    {
        $limit = (int)$this->option('limit');
        $skipIds = array_map('trim', explode(',', $this->option('skip')));
        
        // Get or create progress tracking
        $progress = ImportProgress::firstOrCreate(
            ['type' => 'librivox_audiobooks'],
            ['last_id' => null, 'offset' => 0, 'errors' => '[]']
        );

        $this->info("Starting import from offset {$progress->offset}...");

        try {
            $result = $service->fetchAudiobooks($limit, $progress->offset);
            $batch = $result['books'] ?? [];
            
            if (empty($batch)) {
                $this->info("No more audiobooks found. Import complete.");
                return Command::SUCCESS;
            }

            $processed = 0;
            $errors = json_decode($progress->errors, true);

            foreach ($batch as $audiobookData) {
                if (in_array($audiobookData['librivox_id'], $skipIds)) {
                    $this->info("Skipping audiobook ID {$audiobookData['librivox_id']}");
                    continue;
                }

                try {
                    // Import audiobook and sections
                    $audiobook = Audiobook::updateOrCreate(
                        ['librivox_id' => $audiobookData['librivox_id']],
                        $audiobookData
                    );

                    $sections = $service->fetchAudiobookTracks($audiobookData['librivox_id']);
                    AudiobookSection::where('audiobook_id', $audiobook->id)->delete();
                    foreach ($sections as $section) {
                        AudiobookSection::create($section);
                    }

                    $progress->last_id = $audiobookData['librivox_id'];
                    $processed++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'id' => $audiobookData['librivox_id'],
                        'error' => $e->getMessage()
                    ];
                    Log::error("Failed to import audiobook {$audiobookData['librivox_id']}: " . $e->getMessage());
                }
            }

            $progress->offset += $limit;
            $progress->errors = json_encode($errors);
            $progress->save();

            $this->info("Processed {$processed} audiobooks in this batch.");
            $totalFound = isset($result['total_found']) ? $result['total_found'] : 'unknown';
            $this->info("Total found: " . $totalFound);
            $this->info("Next offset: {$progress->offset}, Last ID: {$progress->last_id}");
            
            if (!empty($errors)) {
                $this->warn(count($errors) . " errors encountered in this batch.");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            Log::error("Full import failed: " . $e->getMessage());
            $this->error("Import failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
