<?php
/**
 * Enterprise DWH Dashboard - Analytical Summary & Insights API
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
$factTable = $_GET['fact_table'] ?? 'fact_sales';
$filters = [
    'start_date' => $_GET['start_date'] ?? null,
    'end_date' => $_GET['end_date'] ?? null,
    'year' => $_GET['year'] ?? null,
    'month' => $_GET['month'] ?? null,
    'category' => $_GET['category'] ?? null,
    'region' => $_GET['region'] ?? null,
];

$insights = DWHHelper::getBusinessInsights($factTable, $filters);
$stats = DWHHelper::getSummaryStatistics($factTable, $filters);

echo json_encode([
    'insights' => $insights,
    'stats' => [
        'min' => $stats['min_val'] ?? 0,
        'max' => $stats['max_val'] ?? 0,
        'avg' => $stats['avg_val'] ?? 0,
        'count' => $stats['count_val'] ?? 0
    ]
]);
