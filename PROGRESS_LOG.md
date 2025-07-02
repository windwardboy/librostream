# Librostream Project Log

### Session: 2023-10-27

**Project:** Librostream.com

**Summary:**
The project was recently migrated from a restrictive host (20i.com) to a more robust stack (Digital Ocean + Ploi.io) to resolve critical debugging and execution limitations.

**Challenge:**
The current process for importing ~20,000 audiobooks is manual, unreliable, and slow. It relies on an Artisan command that can only process ~100 items at a time before timing out or failing. This requires constant manual intervention.

Previous attempts to import from the **Librivox** and **Internet Archive** APIs were problematic due to the restrictive hosting environment, leading to complex workarounds. Specific issues were encountered with inconsistent or missing metadata such as **tags, narrators, and languages**, making the import logic fragile.

**Goal:**
Architect and implement a robust, automated, and scalable solution for importing all audiobooks without manual oversight or server timeouts.

**Proposed Solution: Queue-Based Imports**
1.  An Artisan command (`librostream:queue-imports`) will be created. Its sole purpose is to read the source of all audiobooks (e.g., a CSV file, a directory scan) and dispatch an individual `ImportAudiobook` job for each one onto a queue.
2.  An `ImportAudiobook` Job class will be created. It will contain the logic to process and import a *single* audiobook, making the process resilient to individual failures.
3.  A queue worker will be configured (using the `database` or `redis` driver) and set up to run persistently on the server via Ploi to process these jobs from the queue in the background.

**Next Steps:**
Begin implementation of the queue-based import system, starting with configuring the queue driver and creating the `ImportAudiobook` Job.