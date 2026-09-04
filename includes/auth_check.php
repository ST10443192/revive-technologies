<?php
/**
 * includes/auth_check.php
 * -------------------------------------------------
 * Include this at the TOP of any page that should only
 * be visible to a logged-in user of a specific role.
 *
 * Usage example (top of client/dashboard.php):
 *   require_once '../includes/functions.php';
 *   require_once '../config/database.php';
 *   require_role('client');
 * -------------------------------------------------
 */

function require_role($role) {
    // NOTE: this file is always included from one level deep
    // (client/, freelancer/, admin/), so '../' correctly points
    // back to the project root regardless of what the project
    // folder itself is named or where it's deployed.
    if (!is_logged_in()) {
        set_flash('Please sign in to continue.', 'error');
        redirect('../auth/login.php');
    }

    if (($_SESSION['role'] ?? null) !== $role) {
        set_flash('You do not have permission to view that page.', 'error');
        redirect('../index.php');
    }
}
