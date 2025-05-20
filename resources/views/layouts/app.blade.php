<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Good practice for forms --}}

    <title>@yield('title') - {{ config('app.name', 'Librostream') }}</title>
    <meta name="description" content="@yield('meta_description', config('app.name', 'Librostream') . ' is a free platform for streaming LibriVox audiobooks.')">

    {{-- PWA Manifest --}}
    <link rel="manifest" href="/manifest.json">

    {{-- Scripts and CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Additional head content can be yielded here --}}
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 flex flex-col min-h-screen">
    <x-header />

    <main class="flex-grow container mx-auto px-6 py-8">
        @yield('content')
    </main>

    <x-footer />

    {{-- Additional scripts can be yielded here --}}
    @stack('scripts')

    {{-- Register Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(registration => {
                        console.log('Service Worker registered:', registration);
                    })
                    .catch(error => {
                        console.error('Service Worker registration failed:', error);
                    });
            });
        }
    </script>
</body>
</html>
