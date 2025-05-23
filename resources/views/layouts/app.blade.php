<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- Good practice for forms --}}

    <title>@yield('title') - {{ config('app.name', 'Librostream') }}</title>
    <meta name="description" content="@yield('meta_description', config('app.name', 'Librostream') . ' is a free platform for streaming LibriVox audiobooks.')">

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" sizes="32x32" type="image/png">
    <link rel="icon" href="{{ asset('images/icons/favicon-96x96.png') }}" sizes="96x96" type="image/png">
    <link rel="icon" href="{{ asset('images/icons/favicon-120x120.png') }}" sizes="120x120" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.png') }}" sizes="180x180">
    <link rel="icon" href="{{ asset('images/icons/favicon-512x512.png') }}" sizes="512x512" type="image/png">

    {{-- PWA Manifest --}}
    <link rel="manifest" href="/manifest.json">

    {{-- Scripts and CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Organization Schema Markup --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Librostream",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('images/logo.png') }}", {{-- Assuming you have a logo.png in public/images --}}
      "sameAs": [
        {{-- Add links to your social media profiles here if you have them --}}
        {{-- "https://www.twitter.com/yourprofile", --}}
        {{-- "https://www.facebook.com/yourprofile" --}}
      ]
    }
    </script>

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
