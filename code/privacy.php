<?php
require_once 'includes/config.php';
$page_title = 'Privacy Policy';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Privacy Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
<?php include 'includes/partials/header.php'; ?>

<main class="flex-grow container mx-auto px-4 py-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h1 class="text-3xl font-bold leading-6 text-gray-900">Privacy Policy</h1>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6 prose max-w-none">
            <h3>1. Information We Collect</h3>
            <p>We collect information you provide directly to us, such as when you create an account, post a review, or communicate with us. This includes your username, email address, and any content you post.</p>

            <h3>2. How We Use Your Information</h3>
            <p>We use the information we collect to operate and maintain our services, to communicate with you, and to personalize your experience.</p>

            <h3>3. Information Sharing</h3>
            <p>We do not share your personal information with third parties except as described in this policy or with your consent.</p>

            <h3>4. Data Security</h3>
            <p>We take reasonable measures to help protect information about you from loss, theft, misuse and unauthorized access.</p>

            <h3>5. Cookies</h3>
            <p>We use cookies and similar technologies to collect information about your interactions with our site and services.</p>
        </div>
    </div>
</main>

<?php include 'includes/partials/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
