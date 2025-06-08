@extends('layouts.app')

@section('title', 'All Audiobooks')

@section('content')
    <div class="relative">
        {{-- Hero Section --}}
        <section class="animated-gradient text-white py-24 sm:py-32 rounded-lg shadow-xl relative overflow-hidden">
            <div class="container mx-auto px-6 text-center relative z-10">
                <h2 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-4 leading-tight tracking-tight">
                    Stream Free Public Domain Audiobooks
                </h2>
                <p class="text-lg sm:text-xl text-blue-100 dark:text-blue-200 mb-8 max-w-3xl mx-auto">
                    Explore a vast collection of audiobooks from LibriVox, all in the public domain and 100% free to stream.
                </p>
                <a href="#audiobook-grid" class="bg-white text-indigo-600 font-bold py-3 px-8 rounded-full hover:bg-gray-100 transition duration-300 shadow-lg transform hover:scale-105">
                    Browse Collection
                </a>
            </div>
        </section>

        {{-- Features Widget (Overlapping) --}}
        <div class="relative px-4 sm:px-6 lg:px-8 -mt-16 z-20">
            @if(isset($totalAudiobooks, $uniqueLanguages, $uniqueReaders))
                <x-features-widget
                    :totalAudiobooks="$totalAudiobooks"
                    :uniqueLanguages="$uniqueLanguages"
                    :uniqueReaders="$uniqueReaders"
                />
            @endif
        </div>
    </div>

    {{-- Continue Listening Section (Populated by JS) --}}
    <section id="continue-listening-section" class="mb-12 hidden"> {{-- Hidden by default, shown by JS if items exist --}}
        <h2 class="text-3xl font-bold text-gray-700 dark:text-gray-300 mb-6">Continue Listening</h2>
        <div id="continue-listening-list" class="space-y-4">
            {{-- JS will populate this list --}}
        </div>
    </section>

    {{-- Filter Section --}}
    <section class="mb-12 p-6 bg-white dark:bg-gray-800 shadow-md rounded-lg">
        <form action="{{ route('audiobooks.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 items-end">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search (Title, Author, Narrator)</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-100">
            </div>
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Genre</label>
                <select name="category_id" id="category_id" class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-100">
                    <option value="">All Genres</option>
                    @if(isset($categories)) {{-- Will be passed from controller --}}
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div>
                <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Language</label>
                <select name="language" id="language" class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-100">
                    <option value="">All Languages</option>
                    @if(isset($languages)) {{-- Will be passed from controller --}}
                        @foreach ($languages as $language)
                            <option value="{{ $language }}" {{ request('language') == $language ? 'selected' : '' }}>
                                {{ $language }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="md:col-span-1 flex justify-end lg:col-span-1 space-x-2"> {{-- Adjust col-span for larger screens and add space between buttons --}}
                <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                    Filter
                </button>
                @if(request('search') || request('category_id') || request('language')) {{-- Show clear button only if filters are active --}}
                    <a href="{{ route('audiobooks.index') }}" class="inline-flex justify-center py-2 px-6 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Ad Placeholder (Below Filter) --}}
    <div id="ad-container-index-1" class="my-8 text-center">
        <!-- Ad will be loaded here -->
        <div class="bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 p-4 rounded-md">
            Advertisement Placeholder
        </div>
    </div>

    {{-- Latest Audiobook Releases Section --}}
    @unless(request('search')) {{-- Hide this section if a search query is present --}}
        @if (isset($latestAudiobooks) && $latestAudiobooks->count() > 0)
        <section class="mb-12">
            <h2 class="text-3xl font-bold text-gray-700 dark:text-gray-300 mb-6">Latest Releases</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @foreach ($latestAudiobooks as $audiobook)
                    <x-audiobook-card :audiobook="$audiobook" />
                @endforeach
            </div>
        </section>
    @endif {{-- End if not search query and latest audiobooks exist --}}
    @endunless {{-- This was missing --}}

    {{-- Main content heading for the grid --}}
    <h1 id="audiobook-grid" class="text-4xl font-bold text-gray-700 dark:text-gray-300 mb-8 pt-8 border-t border-gray-200 dark:border-gray-700">All Audiobooks</h1>
    @if (isset($audiobooks) && $audiobooks->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach ($audiobooks as $audiobook)
                <x-audiobook-card :audiobook="$audiobook" />
            @endforeach
        </div>

        {{-- Pagination Links --}}
        <div class="mt-12 pt-8">
            {{ $audiobooks->links() }}
        </div>
    @else
        <p class="text-gray-600 dark:text-gray-400">No audiobooks found.</p>
    @endif
@endsection

@push('scripts')
<script id="audiobook-data-json" type="application/json">
    {
        "audiobooks": @json($audiobooks->items()), {{-- Pass only the items for the current page --}}
        "latestAudiobooks": @json($latestAudiobooks ?? []) {{-- Added null coalescing as fallback --}}
    }
</script>
@endpush
