<?php
/**
 * config/database.php
 * -------------------------------------------------
 * Single place that opens the connection to MySQL.
 * Every other PHP file includes this file to get $pdo.
 *
 * If your XAMPP MySQL username/password are different,
 * change them here ONLY - nowhere else.
 * -------------------------------------------------
 */

$DB_HOST = 'localhost';
$DB_NAME = 'revive_technologies';
$DB_USER = 'root';
$DB_PASS = '';          // default XAMPP password is empty

/** @var PDO $pdo  Shared database connection, available to any file that requires this one. */
try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
