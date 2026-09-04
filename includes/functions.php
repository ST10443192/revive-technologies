<?php
/**
 * includes/functions.php
 * -------------------------------------------------
 * Small reusable helper functions.
 * -------------------------------------------------
 */

// Start the session once, on every page, before any HTML is sent.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape output to prevent XSS when printing user data in HTML. */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/** Is someone currently logged in? */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/** Redirect to another page and stop executing the current script. */
function redirect($path) {
    header("Location: {$path}");
    exit;
}

/** Format a number as South African Rand, e.g. R 1,250.00 */
function rand_format($amount) {
    return 'R ' . number_format((float)$amount, 2);
}

/** Format a date as "12 Sep 2026" */
function nice_date($date) {
    return date('d M Y', strtotime($date));
}

/** Flash message helper - stores a message to show once on the next page load. */
function set_flash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
