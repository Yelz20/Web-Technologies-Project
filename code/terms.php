<?php
require_once 'includes/config.php';
$page_title = 'Terms of Service';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Terms of Service</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">
<?php include 'includes/partials/header.php'; ?>

<main class="flex-grow container mx-auto px-4 py-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h1 class="text-3xl font-bold leading-6 text-gray-900">Terms of Service</h1>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Last updated: <?php echo date('F d, Y'); ?></p>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6 prose max-w-none">
            <h3>1. Acceptance of Terms</h3>
            <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>

            <h3>2. Use of Service</h3>
            <p>You agree to use this site for lawful purposes only. You are responsible for all content you post and for ensuring your account information is accurate.</p>

            <h3>3. User Accounts</h3>
            <p>You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account.</p>

            <h3>4. Content</h3>
            <p>Reviews and comments posted by users are the opinions of those users and do not reflect the views of Football Review. We reserve the right to remove content that violates our community guidelines.</p>

            <h3>5. Termination</h3>
            <p>We reserve the right to terminate or suspend your account immediately, without prior notice or liability, for any reason whatsoever, including without limitation if you breach the Terms.</p>
        </div>
    </div>
</main>

<?php include 'includes/partials/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
