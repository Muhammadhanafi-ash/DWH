<?php
/**
 * Enterprise DWH Dashboard - Export Redirector
 */

require_once __DIR__ . '/../includes/auth.php';
checkAuth();

// Redirect to reports page which contains full DataTable export buttons
header("Location: /reports/index.php");
exit;
