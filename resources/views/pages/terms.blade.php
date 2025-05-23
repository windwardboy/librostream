@extends('layouts.app')

@section('title', 'Terms of Service')

@section('meta_description', 'Read the Terms of Service for the Librostream audiobook streaming service.')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <h1 class="text-4xl font-bold text-gray-700 dark:text-gray-300 mb-8">Terms of Service</h1> {{-- Added styling --}}

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6"> {{-- Updated classes for consistent width and styling --}}
            <p class="text-gray-600 dark:text-gray-400 mb-4">Last Updated: {{ date('F j, Y') }}</p>

            <p class="text-gray-600 dark:text-gray-400 mb-4">Please read these Terms of Service ("Terms", "Terms of Service") carefully before using the Librostream website (the "Service") operated by Librostream ("us", "we", or "our").</p>

            <p class="text-gray-600 dark:text-gray-400 mb-4">Your access to and use of the Service is conditioned on your acceptance of and compliance with these Terms. These Terms apply to all visitors, users, and others who access or use the Service.</p>

            <p class="text-gray-600 dark:text-gray-400 mb-4">By accessing or using the Service you agree to be bound by these Terms. If you disagree with any part of the terms then you may not access the Service.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">1. Accounts</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">When you create an account with us, you must provide us information that is accurate, complete, and current at all times. Failure to do so constitutes a breach of the Terms, which may result in immediate termination of your account on our Service.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">You are responsible for safeguarding the password that you use to access the Service and for any activities or actions under your password, whether your password is with our Service or a third-party service.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">You agree not to disclose your password to any third party. You must notify us immediately upon becoming aware of any breach of security or unauthorized use of your account.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">2. Intellectual Property</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">The Service and its original content (excluding Content provided by users), features and functionality are and will remain the exclusive property of Librostream and its licensors. The Service is protected by copyright, trademark, and other laws of both the [Your Country] and foreign countries.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of Librostream.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">3. Links To Other Web Sites</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Our Service may contain links to third-party web sites or services that are not owned or controlled by Librostream.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Librostream has no control over, and assumes no responsibility for, the content, privacy policies, or practices of any third party web sites or services. You further acknowledge and agree that Librostream shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with use of or reliance on any such content, goods or services available on or through any such web sites or services.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We strongly advise you to read the terms and conditions and privacy policies of any third-party web sites or services that you visit.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">4. Termination</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We may terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">5. Limitation Of Liability</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">In no event shall Librostream, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from (i) your access to or use of or inability to access or use the Service; (ii) any conduct or content of any third party on the Service; (iii) any content obtained from the Service; and (iv) unauthorized access, use or alteration of your transmissions or content, whether based on warranty, contract, tort (including negligence) or any other legal theory, whether or not we have been informed of the possibility of such damage, and even if a remedy set forth herein is found to have failed of its essential purpose.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">6. Governing Law</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">These Terms shall be governed and construed in accordance with the laws of [Your Country/State], without regard to its conflict of law provisions.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights. If any provision of these Terms is held to be invalid or unenforceable by a court, the remaining provisions of these Terms will remain in effect. These Terms constitute the entire agreement between us regarding our Service, and supersede and replace any prior agreements we might have between us regarding the Service.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">7. Changes</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material we will try to provide at least 30 days notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion.</p>
            <p class="text-gray-600 dark:text-gray-400 mb-4">By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, please stop using the Service.</p>

            <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Contact Us</h2>
            <p class="text-gray-600 dark:text-gray-400">If you have any questions about these Terms, please contact us.</p>
        </div>
    </div>
@endsection
