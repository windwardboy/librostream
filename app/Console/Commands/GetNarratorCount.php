<?php

namespace App\Console\Commands;

use App\Models\Audiobook; // Import the Audiobook model
use Illuminate\Console\Command;

class GetNarratorCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:count-narrators'; // Changed signature for clarity

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the total count of distinct narrators in the database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = Audiobook::distinct('narrator')->count(); // Get the count of distinct narrators
        $this->info("Total distinct narrators in database: {$count}"); // Display the count

        return Command::SUCCESS;
    }
}
