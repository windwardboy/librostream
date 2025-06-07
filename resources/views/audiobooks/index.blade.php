@extends('layouts.app')

@section('title', 'All Audiobooks')

@section('content')
    {{-- Hero Section --}}
    <section class="bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-700 dark:to-indigo-800 text-white py-20 mb-12 rounded-lg shadow-xl">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-5xl font-extrabold mb-4 leading-tight">Stream Free Public Domain Audiobooks</h2>
            <p class="text-xl text-blue-100 dark:text-blue-200 mb-8">
                Explore a vast collection of audiobooks from LibriVox, all in the public domain and 100% free to stream.
            </p>
            {{-- Optional: Call to action button --}}
            {{-- <a href="#audiobook-grid" class="bg-white text-indigo-600 font-bold py-3 px-8 rounded-full hover:bg-gray-100 transition duration-300">Browse Collection</a> --}}
        </div>
    </section>

    {{-- Features Widget --}}
    @if(isset($totalAudiobooks, $uniqueLanguages, $uniqueReaders))
        <x-features-widget
            :totalAudiobooks="$totalAudiobooks"
            :uniqueLanguages="$uniqueLanguages"
            :uniqueReaders="$uniqueReaders"
        />
    @endif

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
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        @if($audiobook->slug) {{-- Add check for slug --}}
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
                    @else {{-- Display card without link if slug is missing --}}
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
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $audiobooks->appends(request()->query())->links() }}
        </div>
    @else
        <p class="text-gray-600 dark:text-gray-400">No audiobooks found.</p>
    @endif
@endsection

@push('scripts')
<script id="audiobook-data-json" type="application/json">
    {
        "audiobooks": @json($audiobooks->items()), {{-- Pass only the items for the current page --}}
        "latestAudiobooks": @json($latestAudiobooks)
    }
</script>
@endpush

</final_file_content>

IMPORTANT: For any future changes to this file, use the final_file_content shown above as your reference. This content reflects the current state of the file, including any auto-formatting (e.g., if you used single quotes but the formatter converted them to double quotes). Always base your SEARCH/REPLACE operations on this final version to ensure accuracy.

<environment_details>
# VSCode Visible Files
../../../xampp/htdocs/librostream/resources/views/audiobooks/index.blade.php

# VSCode Open Tabs
../../../xampp/htdocs/librostream/app/Http/Middleware/TrustProxies.php
../../../xampp/htdocs/librostream/public/manifest.json
../../../xampp/htdocs/librostream/app/Http/Controllers/ContactController.php
../../../xampp/htdocs/librostream/resources/views/pages/contact.blade.php
../../../xampp/htdocs/librostream/app/Http/Controllers/FaqController.php
../../../xampp/htdocs/librostream/resources/views/components/header.blade.php
../../../xampp/htdocs/librostream/resources/views/pages/about.blade.php
../../../xampp/htdocs/librostream/resources/views/pages/terms.blade.php
../../../xampp/htdocs/librostream/resources/views/pages/privacy.blade.php
../../../xampp/htdocs/librostream/resources/views/pages/faq.blade.php
../../../xampp/htdocs/librostream/app/Http/Controllers/CookiePolicyController.php
../../../xampp/htdocs/librostream/resources/views/pages/cookie-policy.blade.php
../../../xampp/htdocs/librostream/resources/views/components/footer.blade.php
../../../xampp/htdocs/librostream/resources/views/layouts/app.blade.php
../../../xampp/htdocs/librostream/resources/views/audiobooks/show.blade.php
../../../xampp/htdocs/librostream/routes/web.php
../../../xampp/htdocs/librostream/resources/views/audiobooks/by-tag.blade.php
../../../xampp/htdocs/librostream/database/migrations/2025_05_31_140549_alter_audiobook_sections_table_increase_title_length.php
../../../xampp/htdocs/librostream/database/migrations/2025_05_31_160040_add_slug_to_categories_table.php
../../../xampp/htdocs/librostream/database/migrations/2025_06_04_131045_add_unique_constraint_to_audiobook_sections_table.php
../../../xampp/htdocs/librostream/database/migrations/2025_06_04_135038_drop_section_number_unique_constraint_from_audiobook_sections_table.php
../../../xampp/htdocs/librostream/database/migrations/2025_06_04_140700_drop_old_section_unique_constraint.php
../../../xampp/htdocs/librostream/database/migrations/2025_06_04_163814_change_audiobook_section_title_to_text.php
../../../xampp/htdocs/librostream/app/Console/Commands/FetchLibriVoxAudiobooks.php
../../../xampp/htdocs/librostream/app/Http/Controllers/AudiobookController.php
../../../xampp/htdocs/librostream/resources/views/components/features-widget.blade.php
../../../xampp/htdocs/librostream/resources/views/audiobooks/index.blade.php

# Current Time
6/7/2025, 9:59:28 PM (Europe/London, UTC+1:00)

# Context Window Usage
231,242 / 1,048.576K tokens used (22%)

# Current Mode
ACT MODE
</environment_details>
