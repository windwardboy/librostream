# Librostream Project Log

### Session: 2023-10-27

**Project:** Librostream.com

**Summary:**
The project was recently migrated from a restrictive host (20i.com) to a more robust stack (Digital Ocean + Ploi.io) to resolve critical debugging and execution limitations.

**Challenge:**
The current process for importing ~20,000 audiobooks is manual, unreliable, and slow. It relies on an Artisan command that can only process ~100 items at a time before timing out or failing. This requires constant manual intervention.

Previous attempts to import from the **Librivox** and **Internet Archive** APIs were problematic due1 to the restrictive hosting environment, leading to complex workarounds. Specific issues were encountered with inconsistent or missing metadata such as **tags, narrators, and languages**, making the import logic fragile.

**Goal:**
Architect and implement a robust, automated, and scalable solution for importing all audiobooks without manual oversight or server timeouts.

**Proposed Solution: Queue-Based Imports**
1.  An Artisan command (`librostream:queue-imports`) will be created. Its sole purpose is to read the source of all audiobooks (e.g., a CSV file, a directory scan) and dispatch an individual `ImportAudiobook` job for each one onto a queue.
2.  An `ImportAudiobook` Job class will be created. It will contain the logic to process and import a *single* audiobook, making the process resilient to individual failures.
3.  A queue worker will be configured (using the `database` or `redis` driver) and set up to run persistently on the server via Ploi to process these jobs from the queue in the background.

**Next Steps:**
Begin implementation of the queue-based import system, starting with configuring the queue driver and creating the `ImportAudiobook` Job.

### Session: 2025-07-03

**Project:** Librostream.com

**Summary:**
Focused on resolving critical deployment and application errors on the new Digital Ocean + Ploi.io stack, automating audiobook imports, and fixing homepage data counters.

**Work Completed:**
1.  **Initial HTTP ERROR 500 Resolution:** Diagnosed and resolved the `HTTP ERROR 500` on the live site. The issue was traced to incorrect file permissions on `storage` and `bootstrap/cache` directories. Resolved by setting ownership to `librostream-3odwm:librostream-3odwm` and permissions to `775`.
2.  **Homepage Counter Fix:** Modified `app/Http/Controllers/AudiobookController.php` to correctly count audiobooks by filtering for non-null slugs, ensuring the homepage counter reflects only visible audiobooks. This fix was verified on localhost.
3.  **Audiobook Import Automation Commands Created:**
    *   `app/Console/Commands/FullLibriVoxImport.php`: New Artisan command to continuously fetch main audiobook data from LibriVox (Archive.org) using pagination.
    *   `app/Console/Commands/FullLibriVoxSectionsImport.php`: New Artisan command to continuously fetch and store sections for existing audiobooks.

**Current Challenges & Status:**
1.  **Persistent Deployment Issues with Ploi.io:**
    *   **Git Permission Denied:** `error: could not lock config file .git/config: Permission denied` during `git pull`.
    *   **Chmod Script Error:** `chmod: cannot access 'ploi-e23888159c3a6d2c4ad47fa41f7116d3.sh': No such file or directory`.
    *   **Webhook Failure:** GitHub Actions successfully triggers the Ploi.io deploy webhook, but Ploi.io does not initiate or log any deployment activity.
    *   **Server Access Limitations:** The `ploi` SSH user lacks `sudo` privileges, and Ploi.io's dashboard does not provide a file manager or a way to run commands as `root` or `librostream-3odwm`, preventing manual resolution of permission issues after deployments.
    *   **Ploi.io Support Unresponsive:** Previous attempts to get support for these deployment issues have been unsuccessful.

### Session: 2025-07-03 (Continued)

**Summary:**
Re-assessed audiobook import strategy following user feedback on previous import failures and preference for a "slow but complete" synchronous import, including all associated data (sections) at once, and automation via cron jobs. Addressed issues with messy language codes and missing images/audio files. Decided to **start from scratch** by switching the import source from Internet Archive API to the official LibriVox API to ensure cleaner data.

**Problem:**
Previous import attempts resulted in system crashes and corrupted/incomplete data. The user prefers a single, synchronous import process per audiobook that includes all its sections, rather than separate import steps or a queue-based system. Automation via cron jobs is also required. Additionally, language data from the Internet Archive API was in ISO codes (e.g., `cat`, `mul`, `deu`, `ita`) instead of full language names, and some images/audio files were not present. The core issue was identified as data quality from the Internet Archive API, leading to the decision to switch to the official LibriVox API.

**Revised Plan for Robust, Complete, and Automated Audiobook Import (using Official LibriVox API) - Incorporating Advisor Feedback:**

1.  **Database Reset (Crucial User Action)**:
    *   **Action Required by User**: Before any new import, all existing audiobook, audiobook section, and category data in the database **must be cleared**. This ensures a clean slate, preventing conflicts or corruption from previous, problematic imports. This is a manual step (e.g., truncating tables).

2.  **Create `config/languages.php`**:
    *   **Action by Cline**: Created a new configuration file (`config/languages.php`) to centrally store ISO language code to full name mappings. (Completed)

3.  **Reconfigure `app/Services/LibriVoxService.php`**:
    *   **Action by Cline**: Updated `baseUrl` for audiobooks to `https://librivox.org/api/feed/audiobooks` and `itemApiBaseUrl` to `https://librivox.org/api/feed/audiotracks`.
    *   **Action by Cline**: Modified `fetchAudiobooks` to request `format=json`, `extended=1`, and `coverart=1`, and parse the JSON response.
    *   **Action by Cline**: Modified `fetchAudiobookFiles` (renamed to `fetchAudiobookTracks` for clarity) to use the `audiotracks` endpoint with `project_id` and parse its JSON response for section details.
    *   **Enhancement (Error Handling)**: Added robust `try/catch` blocks around all API calls within this service with detailed logging of failures. (Completed)

4.  **Revise `app/Console/Commands/FetchLibriVoxAudiobooks.php`**:
    *   **Action by Cline**: Updated to consume JSON data from the reconfigured `LibriVoxService`.
    *   **Action by Cline**: Adjusted data mapping logic for LibriVox API's response structure, including `cover_image` and `librivox_id`.
    *   **Enhancement (Dry Run)**: Implemented a `--dry-run` flag to simulate imports without database writes.
    *   **Enhancement (Language Code Mapping)**: Integrated the `config/languages.php` for language conversion.
    *   **Enhancement (Error Handling + Logging)**: Added robust `try/catch` blocks around database write operations and logging of failures per audiobook ID.
    *   **Action by Cline**: Ensured synchronous, complete import for each audiobook (main data + sections from respective LibriVox API endpoints).
    *   **Enhancement (Progress Feedback)**: Maintained and enhanced progress feedback.
    *   **Fix (Broken Images)**: Prioritized `coverart_jpg` and `coverart_thumbnail` fields from LibriVox API for `cover_image` URL. (Completed)

5.  **Update `PROGRESS_LOG.md`**:
    *   **Action by Cline**: Updated this log to reflect the complete shift to the official LibriVox API and all incorporated enhancements. (Completed)

**Current Status & Remaining Issues:**
*   **Images and Layout**: Confirmed by user that images are now working correctly on audiobook detail pages and the layout issue is resolved.
*   **"Märchen (Index aller Märchen) (LibriVox ID: 66)"**: This book still shows "No audio tracks found for sections". User clarified this is an index page on LibriVox that links to other audiobooks, not a directly playable audiobook. (Decision on how to handle this deferred).

**Next Steps (Requires User Action for Deployment & Full Automation):**
1.  **Deployment**: User confirmed migration to Laravel Forge and resolution of deployment issues. Code changes have been successfully deployed to the live server via Git push.
2.  **Verify Live Site**: After successful deployment, confirm the homepage counters are correct and the "Hello World" test marker is visible.
3.  **Set up Cron Job in Forge**: The `librivox:full-import` command has been successfully configured as a nightly scheduled task within Laravel Forge, automating the full import process. User will monitor and report back.
