<?php
/**
 * Enterprise DWH Dashboard - Session Management & Authentication Helper
 */

require_once __DIR__ . '/session_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in, if not redirect to login page.
 */
function checkAuth() {
    if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
        header("Location: /login.php");
        exit;
    }
}
