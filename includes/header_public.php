<?php
/**
 * includes/header_public.php
 * Navbar shown on the public website (index.php) for visitors
 * who are not logged in yet.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? h($page_title) . ' - Bee\'s Events & Decor' : 'Bee\'s Events & Decor'; ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container">
        <a href="index.php" class="brand">
            <img src="assets/img/logo-icon.png" alt="Bee's Events & Decor" class="logo-img">
            <span class="brand-name">Bee's Events &amp; Decor</span>
        </a>
        <div class="nav-links">
            <a href="index.php#services">Services</a>
            <a href="index.php#gallery">Gallery</a>
            <a href="index.php#testimonials">Testimonials</a>
            <a href="index.php#contact">Contact</a>
        </div>
        <div class="nav-actions">
            <a href="auth/login.php" class="btn btn-outline btn-sm">Sign In</a>
            <a href="auth/register.php" class="btn btn-primary btn-sm">Register</a>
        </div>
    </div>
</nav>
<main>
