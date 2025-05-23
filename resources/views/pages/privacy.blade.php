@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-4xl font-bold text-gray-700 dark:text-gray-300 mb-8">Privacy Policy</h1> {{-- Added styling --}}

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6"> {{-- Updated classes for consistent width and styling --}}
            <p class="text-gray-600 dark:text-gray-400 mb-4">Last Updated: {{ date('F j, Y') }}</p>

            <p class="text-gray-600 dark:text-gray-400 mb-4">Librostream ("us", "we", or "our") operates the Librostream website (the "Service"). This page informs you of our policies regarding the collection, use, and disclosure of personal data when you use our Service and the choices you have associated with that data.</p>

            <p class="text-gray-600 dark:text-gray-400 mb-4">We use your data to provide and improve the Service. By using the Service, you agree to the collection and use of information in accordance with this policy. Unless otherwise defined in this Privacy Policy, terms used in this Privacy Policy have the same meanings as in our Terms of Service.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">1. Information Collection and Use</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We collect several different types of information for various purposes to provide and improve our Service to you.</p>
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Types of Data Collected</h3>
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Personal Data</h4>
            <p class="text-gray-600 dark:text-gray-400 mb-4">While using our Service, we may ask you to provide us with certain personally identifiable information that can be used to contact or identify you ("Personal Data"). Personally identifiable information may include, but is not limited to:</p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mb-4 space-y-1">
                <li>Email address</li>
                <li>First name and last name</li>
                <li>Cookies and Usage Data</li>
            </ul>
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Usage Data</h4>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We may also collect information on how the Service is accessed and used ("Usage Data"). This Usage Data may include information such as your computer's Internet Protocol address (e.g. IP address), browser type, browser version, the pages of our Service that you visit, the time and date of your visit, the time spent on those pages, unique device identifiers and other diagnostic data.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">2. Use of Data</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Librostream uses the collected data for various purposes:</p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mb-4 space-y-1">
                <li>To provide and maintain our Service</li>
                <li>To notify you about changes to our Service</li>
                <li>To allow you to participate in interactive features of our Service when you choose to do so</li>
                <li>To provide customer support</li>
                <li>To gather analysis or valuable information so that we can improve our Service</li>
                <li>To monitor the usage of our Service</li>
                <li>To detect, prevent and address technical issues</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">3. Transfer Of Data</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Your information, including Personal Data, may be transferred to — and maintained on — computers located outside of your state, province, country or other governmental jurisdiction where the data protection laws may differ than those from your jurisdiction.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">If you are located outside [Your Country] and choose to provide information to us, please note that we transfer the data, including Personal Data, to [Your Country] and process it there.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Your consent to this Privacy Policy followed by your submission of such information represents your agreement to that transfer.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Librostream will take all steps reasonably necessary to ensure that your data is treated securely and in accordance with this Privacy Policy and no transfer of your Personal Data will take place to an organization or a country unless there are adequate controls in place including the security of your data and other personal information.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">4. Disclosure Of Data</h2>
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Legal Requirements</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Librostream may disclose your Personal Data in the good faith belief that such action is necessary to:</p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mb-4 space-y-1">
                <li>To comply with a legal obligation</li>
                <li>To protect and defend the rights or property of Librostream</li>
                <li>To prevent or investigate possible wrongdoing in connection with the Service</li>
                <li>To protect the personal safety of users of the Service or the public</li>
                <li>To protect against legal liability</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">5. Security of Data</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">The security of your data is important to us, but remember that no method of transmission over the Internet, or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your Personal Data, we cannot guarantee its absolute security.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">6. Changes to This Privacy Policy</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We will let you know via email and/or a prominent notice on our Service, prior to the change becoming effective and update the "effective date" at the top of this Privacy Policy.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Contact Us</h2>
            <p class="text-gray-600 dark:text-gray-400">If you have any questions about this Privacy Policy, please contact us.</p>
        </div>
    </div>
@endsection
