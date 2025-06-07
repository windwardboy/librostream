@props(['totalAudiobooks', 'uniqueLanguages', 'uniqueReaders'])

<div class="features-widget grid grid-cols-1 md:grid-cols-3 gap-8 py-8 px-6 bg-white dark:bg-gray-800 shadow-md rounded-lg mb-12">
    <div class="feature-item flex flex-col items-center text-center p-4 border-r md:border-r-0 last:border-r-0 border-gray-200 dark:border-gray-700">
        <div class="feature-icon text-4xl mb-2 text-blue-600 dark:text-blue-400">📚</div> {{-- Book icon --}}
        <div class="feature-count text-3xl font-bold text-gray-800 dark:text-white">{{ $totalAudiobooks }}</div>
        <div class="feature-label text-sm text-gray-600 dark:text-gray-400">Books</div>
    </div>
    <div class="feature-item flex flex-col items-center text-center p-4 border-r md:border-r-0 last:border-r-0 border-gray-200 dark:border-gray-700">
        <div class="feature-icon text-4xl mb-2 text-green-600 dark:text-green-400">🗣️</div> {{-- Language icon --}}
        <div class="feature-count text-3xl font-bold text-gray-800 dark:text-white">{{ $uniqueLanguages }}</div>
        <div class="feature-label text-sm text-gray-600 dark:text-gray-400">Languages</div>
    </div>
    <div class="feature-item flex flex-col items-center text-center p-4">
        <div class="feature-icon text-4xl mb-2 text-purple-600 dark:text-purple-400">🎧</div> {{-- Reader icon --}}
        <div class="feature-count text-3xl font-bold text-gray-800 dark:text-white">{{ $uniqueReaders }}</div>
        <div class="feature-label text-sm text-gray-600 dark:text-gray-400">Readers</div>
    </div>
</div>
