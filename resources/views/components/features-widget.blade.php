@props(['totalAudiobooks', 'uniqueLanguages', 'uniqueReaders'])

<div class="grid grid-cols-1 md:grid-cols-3 gap-8 py-8 px-6 bg-white dark:bg-gray-800 shadow-2xl rounded-lg mb-12">
    <div class="feature-item flex flex-col items-center text-center p-4 transition-transform duration-300 transform hover:-translate-y-2">
        <div class="feature-icon text-blue-600 dark:text-blue-400 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <div class="feature-count text-4xl font-bold text-gray-800 dark:text-white" data-count="{{ $totalAudiobooks }}">0</div>
        <div class="feature-label text-md text-gray-600 dark:text-gray-400 mt-1">Audiobooks</div>
    </div>
    <div class="feature-item flex flex-col items-center text-center p-4 transition-transform duration-300 transform hover:-translate-y-2">
        <div class="feature-icon text-green-600 dark:text-green-400 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m4 13l4-4M7.5 21l-4.5-4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="feature-count text-4xl font-bold text-gray-800 dark:text-white" data-count="{{ $uniqueLanguages }}">0</div>
        <div class="feature-label text-md text-gray-600 dark:text-gray-400 mt-1">Languages</div>
    </div>
    <div class="feature-item flex flex-col items-center text-center p-4 transition-transform duration-300 transform hover:-translate-y-2">
        <div class="feature-icon text-purple-600 dark:text-purple-400 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            </svg>
        </div>
        <div class="feature-count text-4xl font-bold text-gray-800 dark:text-white" data-count="{{ $uniqueReaders }}">0</div>
        <div class="feature-label text-md text-gray-600 dark:text-gray-400 mt-1">Narrators</div>
    </div>
</div>
