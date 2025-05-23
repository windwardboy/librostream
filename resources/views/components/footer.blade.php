<footer class="bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 py-8 text-center">
    <div class="container mx-auto px-6">
        <p class="text-sm">
            &copy; {{ date('Y') }} Librostream. All rights reserved.
        </p>
        <nav class="mt-4 space-x-4">
            <a href="{{ route('pages.privacy') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">Privacy Policy</a>
            <span class="text-xs text-gray-400 dark:text-gray-500">|</span>
            <a href="{{ route('pages.terms') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">Terms of Service</a>
            <span class="text-xs text-gray-400 dark:text-gray-500">|</span>
            <a href="{{ route('pages.cookie-policy') }}" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">Cookie Policy</a>
            <span class="text-xs text-gray-400 dark:text-gray-500">|</span>
            <a href="#" class="text-xs text-gray-600 dark:text-gray-400 hover:underline">Back to top</a>
            {{-- Add more legal links as needed --}}
        </nav>
    </div>
</footer>
