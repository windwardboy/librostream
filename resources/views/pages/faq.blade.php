@extends('layouts.app')

@section('title', 'FAQ')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-4xl font-bold text-gray-700 dark:text-gray-300 mb-8">Frequently Asked Questions</h1>

        <div class="space-y-6">
            {{-- Placeholder FAQ Item 1 --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Question 1: What is Librostream?</h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Answer 1: Librostream is a free streaming service for public domain audiobooks from sources like LibriVox.
                </p>
            </div>

            {{-- Placeholder FAQ Item 2 --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Question 2: Are the audiobooks really free?</h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Answer 2: Yes, all audiobooks on Librostream are in the public domain and are completely free to stream.
                </p>
            </div>

            {{-- Placeholder FAQ Item 3 --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Question 3: Where do the audiobooks come from?</h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Answer 3: Audiobooks are sourced from trusted public domain archives like LibriVox and Archive.org.
                </p>
            </div>

            {{-- Add more FAQ items as needed --}}
        </div>
    </div>

    {{-- Optional: Add Schema Markup for FAQPage --}}
    @push('scripts')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [{
        "@type": "Question",
        "name": "What is Librostream?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Librostream is a free streaming service for public domain audiobooks from sources like LibriVox."
        }
      }, {
        "@type": "Question",
        "name": "Are the audiobooks really free?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Yes, all audiobooks on Librostream are in the public domain and are completely free to stream."
        }
      }, {
        "@type": "Question",
        "name": "Where do the audiobooks come from?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Audiobooks are sourced from trusted public domain archives like LibriVox and Archive.org."
        }
      }]
    }
    </script>
    @endpush
@endsection
