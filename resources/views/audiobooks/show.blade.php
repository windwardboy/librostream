@extends('layouts.app')

@section('title', $audiobook->title . ' by ' . $audiobook->author)
@section('meta_description', Str::limit(strip_tags($audiobook->description), 160))
@section('og_type', 'video.other')
@section('og_image', $audiobook->cover_image ?? asset('images/og-image.png'))

@push('head')
{{-- Audiobook Schema Markup --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": ["Audiobook", "Book"], {{-- Added Book type --}}
  "bookFormat": "https://schema.org/AudiobookFormat", {{-- Added bookFormat --}}
  "name": "{{ $audiobook->title }}",
  "author": {
    "@type": "Person",
    "name": "{{ $audiobook->author }}"
  },
  @if($audiobook->narrator)
  "readBy": { {{-- Changed from narrator to readBy --}}
    "@type": "Person",
    "name": "{{ $audiobook->narrator }}"
  },
  @endif
  @if($audiobook->category)
  "genre": "{{ $audiobook->category->name }}",
  @endif
  @if($audiobook->description)
  "description": @json(Str::limit(strip_tags($audiobook->description), 500)), {{-- Fixed json_encode syntax and ensure valid JSON --}}
  @endif
  "url": "{{ route('audiobooks.show', $audiobook->slug) }}",
  @if($audiobook->cover_image)
  "image": "{{ $audiobook->cover_image }}",
  @endif
  @if($audiobook->duration)
    @php
        // Convert HH:MM:SS duration string to ISO 8601 PT#H#M#S format
        $durationParts = explode(':', $audiobook->duration);
        $formattedDuration = 'PT';
        if (isset($durationParts[0]) && (int)$durationParts[0] > 0) {
            $formattedDuration .= (int)$durationParts[0] . 'H';
        }
        if (isset($durationParts[1]) && (int)$durationParts[1] > 0) {
            $formattedDuration .= (int)$durationParts[1] . 'M';
        }
        if (isset($durationParts[2]) && (int)$durationParts[2] > 0) {
            $formattedDuration .= (int)$durationParts[2] . 'S';
        }
        // Handle cases where duration might be "00:00:00" or similar
        if ($formattedDuration === 'PT') {
            $formattedDuration = 'PT0S';
        }
    @endphp
  "duration": "{{ $formattedDuration }}", {{-- Use converted ISO 8601 duration --}}
  @endif
  "publisher": {
    "@type": "Organization",
    "name": "LibriVox",
    "url": "https://librivox.org" {{-- Added LibriVox URL --}}
  },
  "offers": { {{-- Added offers property --}}
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "USD",
    "availability": "https://schema.org/InStock",
    "url": "{{ route('audiobooks.show', $audiobook->slug) }}"
  }
  {{-- Add more properties like aggregateRating, review if available --}}
}
</script>
@endpush

@section('content')
{{-- Removed itemscope and itemtype from this div as JSON-LD is preferred --}}
<div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8" data-audiobook-slug="{{ $audiobook->slug }}" >
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

            {{-- Auto-generated introductory sentence --}}
            <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                Dive into "{{ $audiobook->title }}", a classic
                @if($audiobook->category)
                    <span class="font-semibold">{{ $audiobook->category->name }}</span>
                @else
                    audiobook
                @endif
                by <span class="font-semibold">{{ $audiobook->author }}</span>
                @if($audiobook->narrator)
                    narrated by <span class="font-semibold">{{ $audiobook->narrator }}</span>
                @endif
                .
            </p>

            {{-- Basic Metadata Display --}}
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

            {{-- Templated "Why we love this" box --}}
            <div class="bg-blue-100 dark:bg-blue-900 border-l-4 border-blue-500 dark:border-blue-700 text-blue-700 dark:text-blue-300 p-4 mb-6" role="alert">
                <p class="font-bold">Why We Love This Audiobook</p>
                <p class="text-sm">
                    At Librostream, we're passionate about bringing you timeless stories like "{{ $audiobook->title }}". Freely available and beautifully narrated, it's a perfect example of the literary treasures waiting to be discovered in the public domain.
                </p>
            </div>


            <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 mb-6 w-full" itemprop="description"> {{-- Added w-full --}}
                <h3 class="text-xl font-semibold mb-2">Description:</h3>
                {!! nl2br(e($audiobook->description)) !!}
            </div>

            {{-- Tags Section --}}
            <div class="mt-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Tags:</h3>
                <div class="flex flex-wrap gap-2">
                    @if($audiobook->category)
                        <a href="{{ route('audiobooks.byTag', ['tag' => Str::slug($audiobook->category->name)]) }}" class="text-sm bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded-full hover:underline">{{ $audiobook->category->name }}</a>
                    @endif
                    @if($audiobook->author)
                        <a href="{{ route('audiobooks.byTag', ['tag' => Str::slug($audiobook->author)]) }}" class="text-sm bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded-full hover:underline">{{ $audiobook->author }}</a>
                    @endif
                    @if($audiobook->narrator)
                        <a href="{{ route('audiobooks.byTag', ['tag' => Str::slug($audiobook->narrator)]) }}" class="text-sm bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded-full hover:underline">{{ $audiobook->narrator }}</a>
                    @endif
                    {{-- Add more tags here if other relevant metadata fields exist (e.g., subjects, keywords, year) --}}
                </div>
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

    {{-- Audio Sections List (Full Width Below Top Section) --}}
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
