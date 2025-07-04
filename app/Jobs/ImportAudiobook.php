<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan; // Import Artisan facade
use Illuminate\Support\Facades\Log;

class ImportAudiobook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('ImportAudiobook job started. Dispatching librivox:full-import command.');

            // Call the librivox:full-import Artisan command
            // This command handles its own pagination, progress, and error logging.
            Artisan::call('librivox:import');

            Log::info('librivox:import command finished within ImportAudiobook job.');

        } catch (\Exception $e) {
            Log::error('ImportAudiobook job failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            // Re-throw the exception to allow Laravel's queue system to handle retries
            throw $e;
        }
    }
}
