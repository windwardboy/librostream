@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-4xl font-bold text-gray-700 dark:text-gray-300 mb-8">Contact Us</h1>

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Get in Touch</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                If you have any questions, suggestions, or feedback about Librostream, please feel free to contact us via email.
            </p>
            <p class="text-gray-800 dark:text-white font-medium">
                Email: <span id="email-address">admin[AT]librostream[DOT]com</span>
                {{-- Optional: Add a button to reveal the email address --}}
                {{-- <button id="reveal-email" class="ml-2 text-indigo-600 dark:text-indigo-400 hover:underline focus:outline-none">Click to reveal</button> --}}
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Feedback</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We welcome your feedback and suggestions for improving Librostream. Please share your thoughts with us via email at the address above.
            </p>
            <p class="text-gray-600 dark:text-gray-400">
                Librostream provides audiobooks from LibriVox. If you have questions or feedback specifically for LibriVox, you can contact them at:
            </p>
            <p class="text-gray-800 dark:text-white font-medium mt-2">
                LibriVox Email: info[AT]librivox[DOT]org
            </p>
            <p class="text-gray-800 dark:text-white font-medium">
                LibriVox Forum: <a href="http://librivox.org/forum" class="text-indigo-600 dark:text-indigo-400 hover:underline" target="_blank">http://librivox.org/forum</a>
            </p>
        </div>
    </div>

    {{-- Optional JavaScript for email reveal --}}
    {{-- @push('scripts')
    <script>
        document.getElementById('reveal-email')?.addEventListener('click', function() {
            const emailSpan = document.getElementById('email-address');
            if (emailSpan) {
                emailSpan.textContent = 'admin@librostream.com';
                this.style.display = 'none'; // Hide the button after revealing
            }
        });
    </script>
    @endpush --}}
@endsection
