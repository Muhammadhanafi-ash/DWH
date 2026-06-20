<?php
/**
 * Enterprise DWH Dashboard - Session Management & Authentication Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in, if not redirect to login page.
 */
function checkAuth() {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        // Compute path to login page dynamically
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        // Path should lead to /login.php
        header("Location: " . $protocol . $host . "/login.php");
        exit;
    }
}
