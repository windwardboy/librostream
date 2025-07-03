<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FullLibriVoxSectionsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'librivox:full-sections-import {--limit=100 : Number of audiobooks to process per batch for sections} {--delay=1 : Delay in seconds between batches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously fetches and stores audiobook sections for existing audiobooks.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $totalProcessed = 0;
        $continueProcessing = true;

        $this->info("Starting full LibriVox audiobook sections import...");

        while ($continueProcessing) {
            $this->info("Processing batch with limit {$limit} for sections...");

            // Call the existing librivox:fetch-sections command
            $resultCode = Artisan::call('librivox:fetch-sections', [
                '--limit' => $limit,
            ]);

            $output = Artisan::output();
            $this->info($output);

            // Check if any audiobooks were processed in this batch
            // The librivox:fetch-sections command outputs "Processed X audiobooks."
            preg_match('/Processed (\d+) audiobooks\./', $output, $matches);
            $processedInBatch = isset($matches[1]) ? (int) $matches[1] : 0;

            if ($processedInBatch > 0) {
                $totalProcessed += $processedInBatch;
                $this->info("Processed sections for {$processedInBatch} audiobooks in this batch. Total processed: {$totalProcessed}.");
                sleep($delay); // Pause to respect API rate limits
            } else {
                $this->info("No audiobooks found needing section processing in this batch. Ending import.");
                $continueProcessing = false;
            }

            if ($resultCode !== 0) {
                $this->error("librivox:fetch-sections command failed with code {$resultCode}. Aborting full import.");
                $continueProcessing = false;
            }
        }

        $this->info("Full LibriVox audiobook sections import complete. Total audiobooks whose sections were processed: {$totalProcessed}.");

        return Command::SUCCESS;
    }
}
