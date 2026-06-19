<?php
/**
 * Enterprise DWH Dashboard - Main Entry Point Router
 */

require_once __DIR__ . '/includes/session_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: /dashboard/index.php");
    exit;
} else {
    header("Location: /login.php");
    exit;
}
