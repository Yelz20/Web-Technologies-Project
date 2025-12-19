<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Cookie Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php include 'includes/partials/header.php'; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Cookie Policy</h1>
        <p class="text-sm text-gray-600 mb-6">Last updated: <?= date('F j, Y') ?></p>

        <div class="prose max-w-none">
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">What Are Cookies</h2>
            <p class="text-gray-700 mb-4">
                Cookies are small text files that are stored on your device when you visit our website. They help us provide you with a better experience by remembering your preferences and understanding how you use our platform.
            </p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">How We Use Cookies</h2>
            <p class="text-gray-700 mb-4">We use cookies for the following purposes:</p>
            
            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Essential Cookies</h3>
            <p class="text-gray-700 mb-4">
                These cookies are necessary for the website to function properly. They enable core functionality such as security, network management, and accessibility. Without these cookies, our services cannot operate correctly.
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Session Cookies:</strong> Keep you logged in during your visit</li>
                <li><strong>Security Cookies:</strong> Protect against unauthorized access and fraud</li>
                <li><strong>CSRF Tokens:</strong> Prevent cross-site request forgery attacks</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Functional Cookies</h3>
            <p class="text-gray-700 mb-4">
                These cookies allow us to remember your preferences and choices to provide you with a personalized experience.
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Remember Me:</strong> Keeps you logged in across sessions if you choose</li>
                <li><strong>Language Preferences:</strong> Remembers your language selection</li>
                <li><strong>Theme Preferences:</strong> Stores your display preferences</li>
            </ul>

            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Analytics Cookies</h3>
            <p class="text-gray-700 mb-4">
                We use analytics cookies to understand how visitors interact with our website, helping us improve our services.
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li>Pages visited and time spent on site</li>
                <li>Click patterns and navigation behavior</li>
                <li>Error messages and loading times</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Third-Party Cookies</h2>
            <p class="text-gray-700 mb-4">
                We may use third-party services that set cookies on our behalf. These include:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Social Media:</strong> Cookies from social media platforms to enable sharing features</li>
                <li><strong>Analytics Providers:</strong> Tools like Google Analytics to measure website performance</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Managing Cookies</h2>
            <p class="text-gray-700 mb-4">
                You can control and manage cookies in your browser settings. Most browsers allow you to:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li>View and delete cookies</li>
                <li>Block cookies from specific websites</li>
                <li>Block all cookies</li>
                <li>Clear all cookies when you close your browser</li>
            </ul>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 my-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Please note that blocking or deleting cookies may prevent you from accessing certain features of our website and may affect your user experience.
                        </p>
                    </div>
                </div>
            </div>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Cookie Duration</h2>
            <p class="text-gray-700 mb-4">
                Our cookies have different lifespans:
            </p>
            <ul class="list-disc pl-6 text-gray-700 mb-4 space-y-2">
                <li><strong>Session Cookies:</strong> Deleted when you close your browser</li>
                <li><strong>Persistent Cookies:</strong> Remain on your device for up to 30 days</li>
                <li><strong>Remember Me Cookies:</strong> Last for up to 1 year if enabled</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Updates to This Policy</h2>
            <p class="text-gray-700 mb-4">
                We may update this Cookie Policy from time to time to reflect changes in our practices or for operational, legal, or regulatory reasons. We will notify you of any significant changes by posting the updated policy on this page.
            </p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">Contact Us</h2>
            <p class="text-gray-700 mb-4">
                If you have any questions about our use of cookies, please contact us through our <a href="chat.php" class="text-blue-600 hover:underline">chat interface</a>.
            </p>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <a href="index.php" class="text-blue-600 hover:text-blue-800 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Home
            </a>
        </div>
    </div>
</div>

<?php include 'includes/partials/footer.php'; ?>
</body>
</html>
