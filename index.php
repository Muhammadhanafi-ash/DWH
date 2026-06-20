<?php
/**
 * Enterprise DWH Dashboard - Main Entry Point Router
 */

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
