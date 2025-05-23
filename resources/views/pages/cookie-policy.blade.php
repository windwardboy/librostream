@extends('layouts.app')

@section('title', 'Cookie Policy')
@section('meta_description', 'Read the Cookie Policy for the Librostream audiobook streaming service.')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-4xl font-bold text-gray-700 dark:text-gray-300 mb-8">Cookie Policy</h1>

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <p class="text-gray-600 dark:text-gray-400 mb-4">Last Updated: {{ date('F j, Y') }}</p>

            <p class="text-gray-600 dark:text-gray-400 mb-4">This Cookie Policy explains how Librostream ("we", "us", or "our") uses cookies and similar technologies when you visit our website.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">What are Cookies?</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Cookies are small text files that are placed on your computer or mobile device when you visit a website. They are widely used to make websites work or to work more efficiently, as well as to provide information to the owners of the site.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">How We Use Cookies</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We use cookies for the following purposes:</p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mb-4 space-y-2">
                <li>**Strictly Necessary Cookies:** These cookies are essential for the website to function properly. They enable core functionalities like security, network management, and accessibility. You cannot opt-out of these cookies.</li>
                <li>**Session Cookies:** These are temporary cookies that are deleted when you close your browser. They are used to maintain your session as you navigate the site.</li>
                <li>**XSRF-TOKEN:** This cookie is used for security purposes to prevent Cross-Site Request Forgery attacks.</li>
                <li>**Cloudflare Cookie (__cf_bm):** This cookie is set by Cloudflare to provide security features and bot management.</li>
            </ul>

            <p class="text-gray-600 dark:text-gray-400 mb-4">We plan to integrate Google Analytics in the future to collect information about how visitors use our website. Google Analytics uses cookies to collect anonymous information such as the number of visitors to the site, where visitors have come from, and the pages they visited. This information is used to help us improve the website.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Your Choices Regarding Cookies</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">You can control and manage cookies in various ways. Please keep in mind that removing or blocking cookies can negatively impact your user experience and parts of our website may no longer be fully accessible.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Most web browsers allow you to control cookies through their settings. You can typically find these settings in the "Options" or "Preferences" menu of your browser.</p>
            <p class="text-gray-600 dark:text-gray-400">To find out more about how to manage cookies, visit <a href="https://www.allaboutcookies.org" class="text-indigo-600 dark:text-indigo-400 hover:underline" target="_blank" rel="noopener noreferrer">www.allaboutcookies.org</a>.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Changes to This Cookie Policy</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We may update our Cookie Policy from time to time. We will notify you of any changes by posting the new Cookie Policy on this page.</p>
            <p class="text-gray-600 dark:text-gray-400">You are advised to review this Cookie Policy periodically for any changes. Changes to this Cookie Policy are effective when they are posted on this page.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Contact Us</h2>
            <p class="text-gray-600 dark:text-gray-400">If you have any questions about this Cookie Policy, please contact us via the contact information provided on our <a href="{{ route('pages.contact') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Contact Us page</a>.</p>
        </div>
    </div>
@endsection
