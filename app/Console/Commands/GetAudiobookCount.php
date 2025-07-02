<?php

namespace App\Console\Commands;

use App\Models\Audiobook; // Import the Audiobook model
use Illuminate\Console\Command;

class GetAudiobookCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:count-audiobooks'; // Changed signature for clarity

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the total count of audiobooks in the database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = Audiobook::count(); // Get the count of audiobooks
        $this->info("Total audiobooks in database: {$count}"); // Display the count

        return Command::SUCCESS;
    }
}
