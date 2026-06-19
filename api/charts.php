<?php
/**
 * Enterprise DWH Dashboard - Charts Dataset API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

// Ensure user is authenticated
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/dwh_helper.php';

// Get request parameters
$factTable = $_REQUEST['fact_table'] ?? 'fact_sales';
$aggregation = $_REQUEST['aggregation'] ?? null;
$filters = [
    'start_date' => $_REQUEST['start_date'] ?? null,
    'end_date' => $_REQUEST['end_date'] ?? null,
    'year' => $_REQUEST['year'] ?? null,
    'month' => $_REQUEST['month'] ?? null,
    'category' => $_REQUEST['category'] ?? null,
    'region' => $_REQUEST['region'] ?? null,
];

$charts = DWHHelper::getCharts($factTable, $filters, $aggregation);

echo json_encode($charts);
