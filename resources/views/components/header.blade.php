<header class="bg-blue-600 dark:bg-blue-800 text-white shadow-md">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">
            <a href="{{ url('/') }}" class="hover:text-blue-200 dark:hover:text-blue-300">Librostream</a>
        </h1>

        <div class="flex items-center">
            {{-- Dark mode toggle button --}}
            <button id="theme-toggle" type="button" class="text-gray-200 dark:text-gray-300 hover:text-white dark:hover:text-white focus:outline-none focus:text-white dark:focus:text-white mr-4" aria-label="Toggle dark mode">
                {{-- Sun icon (light mode) --}}
                <svg id="theme-toggle-light-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                {{-- Moon icon (dark mode) --}}
                <svg id="theme-toggle-dark-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>

            {{-- Mobile menu button --}}
            <div class="flex items-center sm:hidden">
                <button id="mobile-menu-button" type="button" class="text-gray-200 dark:text-gray-300 hover:text-white dark:hover:text-white focus:outline-none focus:text-white dark:focus:text-white" aria-label="Toggle menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            {{-- Desktop menu --}}
            <nav class="hidden sm:flex space-x-4">
                <a href="{{ url('/') }}" class="hover:text-blue-200 dark:hover:text-blue-300 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                {{-- Removed redundant Audiobooks link --}}
                <a href="{{ url('/') }}#continue-listening-section" class="hover:text-blue-200 dark:hover:text-blue-300 px-3 py-2 rounded-md text-sm font-medium">Continue Listening</a>
                <a href="{{ route('pages.about') }}" class="hover:text-blue-200 dark:hover:text-blue-300 px-3 py-2 rounded-md text-sm font-medium">About</a>
                {{-- Add more links as needed --}}
            </nav>
        </div>
    </div>

    {{-- Mobile menu (hidden by default) --}}
    <div id="mobile-menu" class="hidden sm:hidden bg-blue-700 dark:bg-blue-900 px-2 pt-2 pb-3 space-y-1">
        <a href="{{ url('/') }}" class="block text-gray-200 dark:text-gray-300 hover:bg-blue-500 dark:hover:bg-blue-700 hover:text-white dark:hover:text-white px-3 py-2 rounded-md text-base font-medium">Home</a>
        {{-- Removed redundant Audiobooks link --}}
        <a href="{{ url('/') }}#continue-listening-section" class="block text-gray-200 dark:text-gray-300 hover:bg-blue-500 dark:hover:bg-blue-700 hover:text-white dark:hover:text-white px-3 py-2 rounded-md text-base font-medium">Continue Listening</a>
        <a href="{{ route('pages.about') }}" class="block text-gray-200 dark:text-gray-300 hover:bg-blue-500 dark:hover:bg-blue-700 hover:text-white dark:hover:text-white px-3 py-2 rounded-md text-base font-medium">About</a>
        {{-- Add more links as needed --}}
    </div>
</header>
