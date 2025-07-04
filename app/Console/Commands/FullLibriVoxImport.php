<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FullLibriVoxImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'librivox:full-import {--limit=100 : Number of audiobooks to fetch per batch} {--delay=1 : Delay in seconds between batches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously fetches audiobooks from LibriVox (Archive.org) until no new books are found.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $offset = 0;
        $totalFetched = 0;
        $continueFetching = true;

        $this->info("Starting full LibriVox audiobook import...");

        while ($continueFetching) {
            $this->info("Fetching batch from offset {$offset} with limit {$limit}...");

            // Call the existing librivox:fetch command
            $resultCode = Artisan::call('librivox:fetch', [
                '--limit' => $limit,
                '--offset' => $offset,
            ]);

            $output = Artisan::output();
            $this->info($output);

            // Check if any audiobooks were actually fetched in this batch
            // We need to parse the output to determine this.
            // The librivox:fetch command outputs "X audiobooks fetched from API."
            preg_match('/(\d+) audiobooks fetched from API\./', $output, $matches);
            $fetchedInBatch = isset($matches[1]) ? (int) $matches[1] : 0;

            if ($fetchedInBatch > 0) {
                $totalFetched += $fetchedInBatch;
                $offset += $limit;
                $this->info("Fetched {$fetchedInBatch} audiobooks in this batch. Total fetched: {$totalFetched}.");

                // Stop if the total fetched count reaches or exceeds the user-defined limit
                if ($this->option('limit') !== null && $totalFetched >= (int) $this->option('limit')) {
                    $this->info("Reached the requested limit of " . $this->option('limit') . " audiobooks. Ending import.");
                    $continueFetching = false;
                } else {
                    sleep($delay); // Pause to respect API rate limits
                }
            } else {
                $this->info("No new audiobooks found in this batch. Ending import.");
                $continueFetching = false;
            }

            if ($resultCode !== 0) {
                $this->error("librivox:fetch command failed with code {$resultCode}. Aborting full import.");
                $continueFetching = false;
            }
        }

        $this->info("Full LibriVox audiobook import complete. Total audiobooks processed: {$totalFetched}.");

        return Command::SUCCESS;
    }
}
