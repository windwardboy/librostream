<?php

namespace App\Console\Commands;

use App\Models\Audiobook;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillAudiobookSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audiobooks:backfill-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfills missing slugs for existing audiobooks.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting backfill of missing audiobook slugs...');

        $audiobooksWithoutSlugs = Audiobook::whereNull('slug')->get();
        $count = $audiobooksWithoutSlugs->count();

        if ($count === 0) {
            $this->info('No audiobooks found with missing slugs. Nothing to backfill.');
            return Command::SUCCESS;
        }

        $this->info("Found {$count} audiobooks with missing slugs. Processing...");

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $updatedCount = 0;

        foreach ($audiobooksWithoutSlugs as $book) {
            $baseSlug = Str::slug($book->title);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure uniqueness for the new slug
            while (Audiobook::where('slug', $slug)->where('id', '!=', $book->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $book->slug = $slug;
            $book->save();
            $updatedCount++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nBackfill complete. {$updatedCount} slugs updated.");

        return Command::SUCCESS;
    }
}
