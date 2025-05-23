@extends('layouts.app')

@section('title', 'About Us')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-4xl font-bold text-gray-700 dark:text-gray-300 mb-8">About Us</h1> {{-- Updated title and added styling --}}

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6"> {{-- Updated classes for consistent width and styling --}}
            <p class="text-gray-600 dark:text-gray-400 mb-4">Welcome to Librostream, your premier destination for an immersive audiobook experience. Our mission is to bring the world of literature to your ears, making it easier than ever to discover and enjoy stories that captivate, educate, and inspire.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Our Story</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Librostream was founded by a group of passionate readers and tech enthusiasts who believe in the power of storytelling. We noticed a growing need for a dedicated platform that not only offers a vast library of audiobooks but also provides a seamless and enjoyable listening experience. From classic literature to contemporary bestsellers, from thrilling mysteries to insightful non-fiction, we aim to cater to every taste and preference.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">What We Offer</h2>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mb-4 space-y-2">
                <li><strong>Extensive Library:</strong> Access thousands of audiobooks across a multitude of genres.</li>
                <li><strong>High-Quality Audio:</strong> Enjoy crystal-clear narration from professional voice actors and renowned narrators.</li>
                <li><strong>User-Friendly Interface:</strong> Our platform is designed to be intuitive and easy to navigate, allowing you to find your next listen with ease.</li>
                <li><strong>Cross-Platform Accessibility:</strong> Listen anytime, anywhere, on your preferred devices.</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Our Commitment</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We are committed to continuously improving our platform, expanding our library, and enhancing your listening journey. We value our community of listeners and authors and strive to create an environment where stories can thrive and be celebrated.</p>

            <p class="text-gray-600 dark:text-gray-400">Thank you for choosing Librostream. Happy listening!</p>
        </div>
    </div>
@endsection
