<?php
/**
 * auth/logout.php
 * Destroys the session and sends the user back to the home page.
 */
require_once '../includes/functions.php';

$_SESSION = [];
session_destroy();

redirect('../index.php');
