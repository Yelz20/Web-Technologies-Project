<?php
// Include configuration and functions
require_once 'config.php';

// Check if user is logged in, if not redirect to login page
if (!is_logged_in()) {
    set_flash_message('error', 'Please login to access the dashboard');
    redirect('login.php');
}

// Set page title
$page_title = 'Dashboard';

// Include header
include 'includes/header.php';

// Get user data
$user = get_user_data();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football Review - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="bg-gray-100">
<?php

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? 'User'); ?>!</h1>
        <p>Here's what's happening with your account.</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <h3>My Reviews</h3>
                <p>Total reviews submitted</p>
                <div class="stat-number">24</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-thumbs-up"></i>
            </div>
            <div class="stat-info">
                <h3>Likes Received</h3>
                <p>Total likes on your reviews</p>
                <div class="stat-number">156</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-comment"></i>
            </div>
            <div class="stat-info">
                <h3>Comments</h3>
                <p>Total comments received</p>
                <div class="stat-number">42</div>
            </div>
        </div>
    </div>

    <div class="recent-activity">
        <h2>Recent Activity</h2>
        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="activity-content">
                    <p>You reviewed <a href="match.php?id=123">Manchester United vs Liverpool</a></p>
                    <span class="activity-time">2 hours ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-thumbs-up"></i>
                </div>
                <div class="activity-content">
                    <p>Your review on <a href="match.php?id=122">Barcelona vs Real Madrid</a> received 5 likes</p>
                    <span class="activity-time">1 day ago</span>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="activity-content">
                    <p>New comment on your <a href="match.php?id=120">Arsenal vs Chelsea</a> review</p>
                    <span class="activity-time">2 days ago</span>
                </div>
            </div>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="matches.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Review
            </a>
            <a href="my-reviews.php" class="btn btn-outline">
                <i class="fas fa-list"></i> View All Reviews
            </a>
            <a href="profile.php" class="btn btn-outline">
                <i class="fas fa-user-edit"></i> Edit Profile
            </a>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
<script src="assets/js/main.js"></script>
</body>
</html>
?>
