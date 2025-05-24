@extends('layouts.app')

@section('title', 'Audiobooks tagged with ' . $tagName . ' - Librostream')
@section('meta_description', 'Browse audiobooks tagged with ' . $tagName . ' on Librostream.')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Audiobooks tagged with: "{{ $tagName }}"</h1>

    @if($audiobooks->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($audiobooks as $audiobook)
                {{-- Include or replicate the card structure from your index page --}}
                {{-- Assuming you have a partial like _audiobook_card.blade.php or similar --}}
                {{-- For now, a simple list item --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                    @if($audiobook->cover_image)
                        <img src="{{ $audiobook->cover_image }}" alt="Cover image for {{ $audiobook->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-300 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-gray-500 dark:text-gray-400">No Image</span>
                        </div>
                    @endif
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white truncate">{{ $audiobook->title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 truncate">by {{ $audiobook->author }}</p>
                        @if($audiobook->category)
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $audiobook->category->name }}</p>
                        @endif
                        <a href="{{ route('audiobooks.show', $audiobook->slug) }}" class="mt-3 inline-block text-blue-600 dark:text-blue-400 hover:underline text-sm">Listen Now &rarr;</a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination Links --}}
        <div class="mt-8">
            {{ $audiobooks->links() }}
        </div>
    @else
        <p class="text-gray-600 dark:text-gray-400">No audiobooks found for this tag.</p>
    @endif
</div>
@endsection
