<?php

namespace App\Console\Commands;

use App\Models\Audiobook; // Import the Audiobook model
use Illuminate\Console\Command;

class GetOldIdCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:count-old-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the total count of audiobooks with old numeric LibriVox IDs.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = Audiobook::whereRaw('LENGTH(librivox_id) <= 5') // Likely old numeric IDs
                                ->whereRaw('librivox_id REGEXP \'^[0-9]+$\'') // Ensure it's purely numeric
                                ->count();
        $this->info("Total audiobooks with old numeric IDs: {$count}");

        return Command::SUCCESS;
    }
}
