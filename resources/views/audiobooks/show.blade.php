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
  "@type": ["Audiobook", "Book"],
  "bookFormat": "https://schema.org/AudiobookFormat",
  "name": "{{ $audiobook->title }}",
  "author": {
    "@type": "Person",
    "name": "{{ $audiobook->author }}"
  },
  @if($audiobook->narrator)
  "readBy": {
    "@type": "Person",
    "name": "{{ $audiobook->narrator }}"
  },
  @endif
  @if($audiobook->category)
  "genre": "{{ $audiobook->category->name }}",
  @endif
  @if($audiobook->description)
  "description": @json(Str::limit(strip_tags($audiobook->description), 500)),
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
        if ($formattedDuration === 'PT') {
            $formattedDuration = 'PT0S';
        }
    @endphp
  "duration": "{{ $formattedDuration }}",
  @endif
  "publisher": {
    "@type": "Organization",
    "name": "LibriVox",
    "url": "https://librivox.org"
  },
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "USD",
    "availability": "https://schema.org/InStock",
    "url": "{{ route('audiobooks.show', $audiobook->slug) }}"
  }
}
</script>
@endpush

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8" data-audiobook-slug="{{ $audiobook->slug }}" >
    <div class="mb-6">
        <a href="{{ route('audiobooks.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">&laquo; Back to All Audiobooks</a>
    </div>

    <div class="md:flex md:space-x-8 mb-8">
        <div class="md:w-1/3 mb-6 md:mb-0">
            @if($audiobook->cover_image)
                <img src="{{ $audiobook->cover_image }}" alt="Cover image for {{ $audiobook->title }}" class="w-full h-auto object-cover rounded-lg shadow-md" itemprop="image">
            @else
                <div class="w-full h-96 bg-gray-300 dark:bg-gray-700 flex items-center justify-center rounded-lg shadow-md">
                    <span class="text-gray-500 dark:text-gray-400">No Image Available</span>
                </div>
            @endif
        </div>

        <div class="md:w-2/3">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 dark:text-white mb-2" itemprop="name">{{ $audiobook->title }}</h1>

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

            <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 mb-6 w-full" itemprop="description">
                <h3 class="text-xl font-semibold mb-2">Description:</h3>
                {!! nl2br(e($audiobook->description)) !!}
            </div>

            {{-- Templated "Why we love this" box --}}
            <div class="bg-blue-100 dark:bg-blue-900 border-l-4 border-blue-500 dark:border-blue-700 text-blue-700 dark:text-blue-300 p-4 mb-6" role="alert">
                <p class="font-bold">Why We Love This Audiobook</p>
                <p class="text-sm">
                    At Librostream, we're passionate about bringing you timeless stories like "{{ $audiobook->title }}". Freely available and beautifully narrated, it's a perfect example of the literary treasures waiting to be discovered in the public domain.
                </p>
            </div>

            {{-- The Librostream Experience --}}
            <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-6 my-8">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">The Librostream Experience</h3>
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-lg font-medium text-gray-900 dark:text-white">Modern Audio Player</p>
                            <p class="text-gray-600 dark:text-gray-400">Enjoy a clean, fast, and easy-to-use player designed for seamless listening.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-lg font-medium text-gray-900 dark:text-white">Seamless Progress Tracking</p>
                            <p class="text-gray-600 dark:text-gray-400">Pick up right where you left off, on any device.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-lg font-medium text-gray-900 dark:text-white">Expertly Curated Library</p>
                            <p class="text-gray-600 dark:text-gray-400">Discover timeless classics and hidden gems, all in the public domain.</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-lg font-medium text-gray-900 dark:text-white">Always 100% Free</p>
                            <p class="text-gray-600 dark:text-gray-400">No fees, no sign-ups, ever. Just free audiobooks.</p>
                        </div>
                    </li>
                </ul>
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
                </div>
            </div>

            @if($audiobook->librivox_url)
            <div class="mt-4 w-full">
                <a href="{{ $audiobook->librivox_url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 dark:text-blue-400 hover:underline" itemprop="url">
                    View on LibriVox.org &rarr;
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Main Audio Player Container -->
    <div id="main-audio-container" class="my-6 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg sticky top-4 z-10">
        <audio controls class="w-full" id="main-audio-player">
            Your browser does not support the audio element.
        </audio>
    </div>

    <div id="ad-container-show-1" class="my-8 text-center">
        <div class="bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-md">
            Advertisement Placeholder
        </div>
    </div>

    <div class="mt-6 w-full">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Audio Sections:</h3>
        @if($audiobook->sections->count() > 0)
            <ul class="space-y-2">
                @foreach($audiobook->sections as $section)
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainAudioContainer = document.getElementById('main-audio-container');
    const mainPlayer = document.getElementById('main-audio-player');
    
    // Create mini-player container
    const miniPlayerContainer = document.createElement('div');
    miniPlayerContainer.id = 'mini-player';
    // Use 'hidden' for initial state, and 'lg:hidden' to hide on large screens and up
    miniPlayerContainer.className = 'fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 shadow-lg border-t border-gray-200 dark:border-gray-700 p-2 hidden lg:hidden z-50';
    
    // Create a clone of the main player for the mini-player
    const miniPlayer = mainPlayer.cloneNode(true);
    miniPlayer.id = 'mini-audio-player';
    miniPlayer.classList.add('w-full'); // Ensure mini-player also takes full width
    miniPlayerContainer.appendChild(miniPlayer);
    document.body.appendChild(miniPlayerContainer);
    
    // Sync players
    mainPlayer.addEventListener('play', () => {
        miniPlayer.currentTime = mainPlayer.currentTime;
        miniPlayer.play();
    });
    mainPlayer.addEventListener('pause', () => miniPlayer.pause());
    mainPlayer.addEventListener('timeupdate', () => {
        if (Math.abs(mainPlayer.currentTime - miniPlayer.currentTime) > 0.5) {
            miniPlayer.currentTime = mainPlayer.currentTime;
        }
    });

    miniPlayer.addEventListener('play', () => {
        mainPlayer.currentTime = miniPlayer.currentTime;
        mainPlayer.play();
    });
    miniPlayer.addEventListener('pause', () => mainPlayer.pause());
    miniPlayer.addEventListener('timeupdate', () => {
        if (Math.abs(miniPlayer.currentTime - mainPlayer.currentTime) > 0.5) {
            mainPlayer.currentTime = miniPlayer.currentTime;
        }
    });
    
    // Show/hide mini-player based on scroll and screen size
    window.addEventListener('scroll', function() {
        const mainPlayerRect = mainAudioContainer.getBoundingClientRect();
        // Check if main player is scrolled out of view AND if screen is smaller than large (lg) breakpoint
        const isSmallScreen = window.innerWidth < 1024; // Tailwind's 'lg' breakpoint is 1024px
        const shouldShowMiniPlayer = mainPlayerRect.bottom < 0 && isSmallScreen;

        miniPlayerContainer.classList.toggle('hidden', !shouldShowMiniPlayer);
    });

    // Initial check on load
    const initialMainPlayerRect = mainAudioContainer.getBoundingClientRect();
    const initialIsSmallScreen = window.innerWidth < 1024;
    const initialShouldShowMiniPlayer = initialMainPlayerRect.bottom < 0 && initialIsSmallScreen;
    miniPlayerContainer.classList.toggle('hidden', !initialShouldShowMiniPlayer);

    // Handle window resize to adjust mini-player visibility
    window.addEventListener('resize', function() {
        const mainPlayerRect = mainAudioContainer.getBoundingClientRect();
        const isSmallScreen = window.innerWidth < 1024;
        const shouldShowMiniPlayer = mainPlayerRect.bottom < 0 && isSmallScreen;
        miniPlayerContainer.classList.toggle('hidden', !shouldShowMiniPlayer);
    });
});
</script>
@endpush
