<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Football Review</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo $base_url; ?>/assets/images/favicon.png">
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/main.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app">
        <!-- Navigation -->
        <header class="header">
            <nav class="navbar">
                <div class="container">
                    <div class="navbar-brand">
                        <a href="<?php echo $base_url; ?>/index.php" class="logo">
                            <img src="<?php echo $base_url; ?>/assets/images/logo.png" alt="Football Review">
                            <span>Football Review</span>
                        </a>
                        <button class="navbar-toggler" type="button">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                    <div class="navbar-menu">
                        <ul class="navbar-nav">
                            <li class="nav-item <?php echo is_active('index.php'); ?>">
                                <a href="<?php echo $base_url; ?>/index.php" class="nav-link">Home</a>
                            </li>
                            <li class="nav-item <?php echo is_active('matches.php'); ?>">
                                <a href="<?php echo $base_url; ?>/matches.php" class="nav-link">Matches</a>
                            </li>
                            <li class="nav-item <?php echo is_active('teams.php'); ?>">
                                <a href="<?php echo $base_url; ?>/teams.php" class="nav-link">Teams</a>
                            </li>
                            <li class="nav-item <?php echo is_active('players.php'); ?>">
                                <a href="<?php echo $base_url; ?>/players.php" class="nav-link">Players</a>
                            </li>
                            <?php if (is_logged_in()): ?>
                                <li class="nav-item dropdown">
                                    <a href="#" class="nav-link dropdown-toggle" id="userDropdown">
                                        <i class="fas fa-user"></i> 
                                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Account'); ?>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="<?php echo $base_url; ?>/profile.php">My Profile</a>
                                        <a class="dropdown-item" href="<?php echo $base_url; ?>/my-reviews.php">My Reviews</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="<?php echo $base_url; ?>/logout.php">Logout</a>
                                    </div>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a href="<?php echo $base_url; ?>/login.php" class="btn btn-primary">Login / Register</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <?php show_flash_message(); ?>
