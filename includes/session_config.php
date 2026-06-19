<?php
/**
 * Enterprise DWH Dashboard - Session Configuration Bootstrap
 * 
 * Must be included BEFORE any session_start() call.
 * Configures session cookies to work correctly behind Railway's 
 * HTTPS reverse proxy (and other PaaS platforms).
 */

// Detect if we're behind a reverse proxy with HTTPS termination (Railway, Heroku, etc.)
$isSecure = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
);

// Configure session cookie parameters for proper behavior behind proxy
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,            // Session cookie (expires when browser closes)
        'path'     => '/',          // Available across entire site
        'domain'   => '',           // Current domain only
        'secure'   => $isSecure,    // Only send over HTTPS when detected
        'httponly'  => true,         // Not accessible via JavaScript
        'samesite' => 'Lax'         // Prevents CSRF while allowing normal navigation
    ]);
}
