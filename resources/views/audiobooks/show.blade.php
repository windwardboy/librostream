@extends('layouts.app')

@section('title', $audiobook->title . ' by ' . $audiobook->author)
@section('meta_description', Str::limit($audiobook->description, 160))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8" data-audiobook-slug="{{ $audiobook->slug }}" itemscope itemtype="https://schema.org/Audiobook">
    <div class="mb-6">
        <a href="{{ route('audiobooks.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">&laquo; Back to All Audiobooks</a>
    </div>

    {{-- Top Section: Image and Details (Two Columns on MD+) --}}
    <div class="md:flex md:space-x-8 mb-8"> {{-- Added mb-8 for spacing below this section --}}
        {{-- Left Column: Cover Image --}}
        <div class="md:w-1/3 mb-6 md:mb-0">
            @if($audiobook->cover_image)
                <img src="{{ $audiobook->cover_image }}" alt="Cover image for {{ $audiobook->title }}" class="w-full h-auto object-cover rounded-lg shadow-md" itemprop="image">
            @else
                <div class="w-full h-96 bg-gray-300 dark:bg-gray-700 flex items-center justify-center rounded-lg shadow-md">
                    <span class="text-gray-500 dark:text-gray-400">No Image Available</span>
                </div>
            @endif
        </div>

        {{-- Right Column: Details (excluding player/sections) --}}
        <div class="md:w-2/3">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 dark:text-white mb-2" itemprop="name">{{ $audiobook->title }}</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-1">By: <span class="font-semibold" itemprop="author" itemscope itemtype="https://schema.org/Person"><span itemprop="name">{{ $audiobook->author }}</span></span></p>
            @if($audiobook->narrator)
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">Narrated by: <span class="font-semibold" itemprop="narrator" itemscope itemtype="https://schema.org/Person"><span itemprop="name">{{ $audiobook->narrator }}</span></span></p>
            @endif

            @if($audiobook->category)
                <p class="text-md text-gray-700 dark:text-gray-300 mb-4">
                    Genre:
                    <span class="text-sm bg-indigo-100 dark:bg-indigo-700 text-indigo-700 dark:text-indigo-200 px-3 py-1 rounded-full" itemprop="genre">{{ $audiobook->category->name }}</span>
                </p>
            @endif
            
            <p class="text-md text-gray-700 dark:text-gray-300 mb-4"><strong>Duration:</strong> <span itemprop="duration">{{ $audiobook->duration ?? 'N/A' }}</span></p>

            <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 mb-6 w-full" itemprop="description"> {{-- Added w-full --}}
                <h3 class="text-xl font-semibold mb-2">Description:</h3>
                {!! nl2br(e($audiobook->description)) !!}
            </div>

            @if($audiobook->librivox_url)
            <div class="mt-4 w-full"> {{-- Added w-full --}}
                <a href="{{ $audiobook->librivox_url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 dark:text-blue-400 hover:underline" itemprop="url">
                    View on LibriVox.org &rarr;
                </a>
            </div>
            @endif
        </div>
    </div> {{-- End Top Section Flex --}}

    {{-- Audio Player (Full Width Below Top Section) --}}
    <audio controls class="w-full mb-6" id="main-audio-player">
        Your browser does not support the audio element.
    </audio>

    {{-- Ad Placeholder (Below Player) --}}
    <div id="ad-container-show-1" class="my-8 text-center">
        <!-- Ad will be loaded here -->
        <div class="bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-md">
            Advertisement Placeholder
        </div>
    </div>

    {{-- Audio Sections List (Full Width Below Player) --}}
    <div class="mt-6 w-full">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Audio Sections:</h3>
        @if($audiobook->sections->count() > 0)
            <ul class="space-y-2"> {{-- Reduced spacing slightly --}}
                @foreach($audiobook->sections as $section)
                    {{-- Added cursor-pointer and hover effect to indicate clickability --}}
                    <li class="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg shadow-sm cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                        data-section-id="{{ $section->id }}"
                        data-src="{{ $section->source_url }}"
                        data-title="{{ $section->title }}">
                        <h4 class="text-lg font-medium text-gray-800 dark:text-white">
                            Section {{ $section->section_number }}: {{ $section->title }}
                            @if($section->duration)
                                <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">({{ $section->duration }})</span>
                            @endif
                        </h4>
                        @if($section->reader_name)
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Read by: {{ $section->reader_name }}</p>
                        @endif
                        {{-- Removed individual audio player --}}
                        @if(!$section->source_url)
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Audio source not available for this section.</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-600 dark:text-gray-400">No audio sections found for this audiobook.</p>
        @endif
    </div>
</div>
@endsection
