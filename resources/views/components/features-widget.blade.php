@props(['totalAudiobooks', 'uniqueLanguages', 'uniqueReaders'])

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8 py-8 px-6 bg-white dark:bg-gray-800 shadow-2xl rounded-lg mb-12">
    {{-- Books --}}
    <div class="flex items-center justify-center p-4 transition-transform duration-300 transform hover:-translate-y-2">
        <div class="text-blue-500 dark:text-blue-400 mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <div>
            <div class="text-4xl font-bold text-gray-800 dark:text-white">
                <span class="feature-count" data-count="{{ $totalAudiobooks }}">0</span>
            </div>
            <div class="text-md text-gray-600 dark:text-gray-400 mt-1">Audiobooks</div>
        </div>
    </div>

    {{-- Languages --}}
    <div class="flex items-center justify-center p-4 transition-transform duration-300 transform hover:-translate-y-2">
        <div class="text-green-500 dark:text-green-400 mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-12 w-12">
              <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
            </svg>
        </div>
        <div>
            <div class="text-4xl font-bold text-gray-800 dark:text-white">
                <span class="feature-count" data-count="{{ $uniqueLanguages }}">0</span>
            </div>
            <div class="text-md text-gray-600 dark:text-gray-400 mt-1">Languages</div>
        </div>
    </div>

    {{-- Narrators --}}
    <div class="flex items-center justify-center p-4 transition-transform duration-300 transform hover:-translate-y-2">
        <div class="text-purple-500 dark:text-purple-400 mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
        </div>
        <div>
            <div class="text-4xl font-bold text-gray-800 dark:text-white">
                <span class="feature-count" data-count="{{ $uniqueReaders }}">0</span>
            </div>
            <div class="text-md text-gray-600 dark:text-gray-400 mt-1">Narrators</div>
        </div>
    </div>
</div>
