@props(['audiobook'])

<div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
    @if($audiobook->slug)
        <a href="{{ route('audiobooks.show', $audiobook->slug) }}" class="block">
            @if($audiobook->cover_image)
                <img src="{{ $audiobook->cover_image }}" alt="Cover image for {{ $audiobook->title }}" class="w-full h-64 object-cover">
            @else
                <div class="w-full h-64 bg-gray-300 dark:bg-gray-700 flex items-center justify-center">
                    <span class="text-gray-500 dark:text-gray-400">No Image</span>
                </div>
            @endif
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2 truncate" title="{{ $audiobook->title }}">{{ $audiobook->title }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">By: {{ $audiobook->author }}</p>
                @if($audiobook->narrator)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Narrated by: {{ $audiobook->narrator }}</p>
                @endif
                @if($audiobook->category)
                    <p class="text-xs text-gray-500 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 inline-block px-2 py-1 rounded-full mt-2">
                        {{ $audiobook->category->name }}
                    </p>
                @endif
            </div>
        </a>
    @else
        {{-- Fallback for audiobooks without a slug --}}
        <div class="block">
            @if($audiobook->cover_image)
                <img src="{{ $audiobook->cover_image }}" alt="Cover image for {{ $audiobook->title }}" class="w-full h-64 object-cover">
            @else
                <div class="w-full h-64 bg-gray-300 dark:bg-gray-700 flex items-center justify-center">
                    <span class="text-gray-500 dark:text-gray-400">No Image</span>
                </div>
            @endif
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2 truncate" title="{{ $audiobook->title }}">{{ $audiobook->title }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">By: {{ $audiobook->author }}</p>
                @if($audiobook->narrator)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Narrated by: {{ $audiobook->narrator }}</p>
                @endif
                @if($audiobook->category)
                    <p class="text-xs text-gray-500 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 inline-block px-2 py-1 rounded-full mt-2">
                        {{ $audiobook->category->name }}
                    </p>
                @endif
            </div>
        </div>
    @endif
</div>
