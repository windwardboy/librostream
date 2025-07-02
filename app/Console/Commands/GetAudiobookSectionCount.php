<?php

namespace App\Console\Commands;

use App\Models\AudiobookSection;
use Illuminate\Console\Command;

class GetAudiobookSectionCount extends Command
{
    protected $signature = 'db:count-sections';
    protected $description = 'Get the total count of audiobook sections in the database.';

    public function handle()
    {
        $count = AudiobookSection::count();
        $this->info("Total audiobook sections in database: {$count}");
        return Command::SUCCESS;
    }
}
