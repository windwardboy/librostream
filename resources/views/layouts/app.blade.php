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

    {{-- Custom Cookie Consent Banner --}}
    <div id="cookie-consent-banner" class="fixed bottom-0 right-0 z-50 w-full md:max-w-sm bg-gray-800 text-white p-4 shadow-lg transform translate-y-full transition-transform duration-300 ease-in-out">
        <div class="container mx-auto flex items-center justify-between">
            <p class="text-sm mr-4">
                This website uses cookies to ensure you get the best experience. <a href="{{ route('pages.cookie-policy') }}" class="text-indigo-400 hover:underline">Learn more</a>
            </p>
            <button id="accept-cookies" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded">
                Got it!
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cookieBanner = document.getElementById('cookie-consent-banner');
            const acceptButton = document.getElementById('accept-cookies');
            const consentCookieName = 'cookie_consent';

            // Function to set a cookie
            function setCookie(name, value, days) {
                let expires = "";
                if (days) {
                    const date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "")  + expires + "; path=/";
            }

            // Function to get a cookie
            function getCookie(name) {
                const nameEQ = name + "=";
                const ca = document.cookie.split(';');
                for(let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            // Check if consent cookie exists
            if (!getCookie(consentCookieName)) {
                // If not, show the banner after a short delay (for the transition)
                setTimeout(() => {
                    cookieBanner.classList.remove('translate-y-full');
                }, 100); // Adjust delay as needed
            }

            // Add event listener to the accept button
            acceptButton.addEventListener('click', function() {
                setCookie(consentCookieName, 'true', 365); // Set cookie for 365 days
                cookieBanner.classList.add('translate-y-full'); // Hide the banner
                // Note: For full GDPR compliance with non-essential cookies (like GA),
                // you would add logic here to load those scripts after consent.
            });
        });
    </script>
</body>
</html>
