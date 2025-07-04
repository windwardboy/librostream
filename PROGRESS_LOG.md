# Librostream Project Log

## Latest Updates (2025-07-04)

### Objective: Implement a Robust, Automated, and Scalable Audiobook Import System

**Summary of Challenges Faced:**
The project has faced persistent issues with audiobook imports across different hosting environments (20i.com, Ploi.io, Laravel Forge). Initial attempts with direct Artisan command execution via cron jobs proved unreliable due to server-level connectivity issues (SSH failures) and resource limitations. Data quality from the Internet Archive API was also a concern (messy language codes, broken images, missing sections).

**Core Problem:** Achieving reliable, automated, and complete audiobook imports with clean data.

**Solution Strategy: Webhook-Triggered, Queue-Based Import using Official LibriVox API**

This revised strategy aims to decouple the import process from direct cron job execution, leveraging Laravel's robust queue system and a secure HTTP trigger for maximum reliability and data integrity.

**Key Components & Implementation Plan:**

1.  **Database Reset (Crucial User Action)**:
    *   **Action Required by User**: Before any new large-scale import, all existing audiobook, audiobook section, and category data in the database **must be cleared**. This ensures a clean slate. This is a manual step (e.g., `php artisan migrate:fresh --seed` locally, or manual truncation on the live server).

2.  **Official LibriVox API Integration (Completed)**:
    *   **`config/languages.php`**: Created to centralize ISO language code to full name mappings.
    *   **`app/Services/LibriVoxService.php`**: Reconfigured to exclusively use official LibriVox API endpoints (`/api/feed/audiobooks` for main data and `/api/feed/audiotracks` for sections), fetch JSON data, and include robust error handling.
    *   **`app/Console/Commands/FetchLibriVoxAudiobooks.php`**: Updated to consume data from the new `LibriVoxService`, correctly map fields (including `cover_image` and `librivox_id`), implement a `--dry-run` flag for testing, use `config/languages.php` for language conversion, and include comprehensive error handling around database operations. It performs a synchronous, complete import of audiobooks and their sections.

3.  **Webhook-Triggered Import Mechanism (New Implementation)**:
    *   **`routes/api.php`**: Added a new secure API endpoint (`/api/import/trigger`) to receive trigger requests. (Completed)
    *   **`app/Http/Controllers/AudiobookController.php`**: Added `triggerImport` method to handle webhook requests and dispatch the `ImportAudiobook` job. (Completed)
    *   **`app/Jobs/ImportAudiobook.php`**: Modified to dispatch the `librivox:full-import` Artisan command, moving the heavy lifting to a background job. (Completed)
    *   **Security**: The webhook endpoint is secured using a shared secret token (`config('app.import_webhook_token')`).

4.  **Queue Worker Configuration (User Action)**:
    *   **Action Required by User**: Ensure a Laravel queue worker is configured and running persistently on your Forge server. This is typically set up under your Site's "Daemons" or Server's "Daemons" section in Forge. This worker will process the `ImportAudiobookJob` instances. (Completed)

5.  **Automation Trigger**:
    *   Instead of a direct cron job for `librivox:full-import`, an external service (e.g., a free online cron job service, or a simple `curl` command from another server) will make a periodic HTTP POST request to the secure `/api/import/trigger` endpoint. This decouples the import trigger from Forge's potentially problematic scheduler SSH connectivity. (Completed: Configured to run every 15 minutes).

6.  **Revised Scheduling (Local Change)**:
    *   **`app/Console/Kernel.php`**: The `librivox:full-import` command is **no longer scheduled directly** in this file. Its execution is now managed by the webhook dispatching jobs. (Completed)

7.  **Cleanup of Old Scheduler (User Action)**:
    *   **Action Required by User**: Delete any manually created `librivox:full-import` schedulers in Laravel Forge to avoid conflicts. The automatically created `php8.3 /home/librostreamcom/librostream.com/artisan schedule:run` cron job should remain active, as it manages other Laravel scheduled tasks (though not the import in this new model). (Completed)

**Current Status & Resolved Issues:**
*   **Images and Layout**: Images are now working correctly on audiobook detail pages, and the "The Librostream Experience" layout issue is resolved.
*   **"Märchen (Index aller Märchen) (LibriVox ID: 66)"**: This book still shows "No audio tracks found for sections". User clarified this is an index page on LibriVox that links to other audiobooks, not a directly playable audiobook. (Decision on how to handle this deferred for future discussion).
*   **Forge Scheduler Failure**: The persistent SSH connectivity issue for scheduled jobs is being bypassed by this new webhook-triggered approach, which is now fully implemented and configured.

**File and Folder Cleanup (Completed):**
*   `app/Console/Commands/FullLibriVoxSectionsImport.php` (Deleted)
*   `app/Console/Commands/BackfillAudiobookSlugs.php` (Deleted)
*   `TROUBLESHOOTING_LIVE_SERVER.md` (Deleted)
*   `app/Console/Commands/FetchAudiobookSections.php` (Deleted)

**Next Steps (Requires User Action for Monitoring):**
1.  **Verify Live Site**: Monitor the live site over the next few days to confirm that audiobooks are populating, images are displaying correctly, and all features are working as expected.
