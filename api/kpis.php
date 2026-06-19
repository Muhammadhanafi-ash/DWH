<?php
/**
 * Enterprise DWH Dashboard - KPI Cards & Columns Meta API
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
$filters = [
    'start_date' => $_REQUEST['start_date'] ?? null,
    'end_date' => $_REQUEST['end_date'] ?? null,
    'year' => $_REQUEST['year'] ?? null,
    'month' => $_REQUEST['month'] ?? null,
    'category' => $_REQUEST['category'] ?? null,
    'region' => $_REQUEST['region'] ?? null,
];

$kpis = DWHHelper::getKPIs($factTable, $filters);

// Extract dynamic columns list using a fast PDO LIMIT 0 execution
$columnsList = [];
$db = Database::getConnection();
if ($db) {
    try {
        $qInfo = DWHHelper::buildDynamicQuery($factTable, $filters, null, null, null, 0);
        $stmt = $db->prepare($qInfo['sql']);
        $stmt->execute($qInfo['params']);
        $colCount = $stmt->columnCount();
        for ($i = 0; $i < $colCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            $columnsList[] = $meta['name'];
        }
    } catch (Exception $e) {
        // Fallback to basic columns if joins fail
        $columns = DWHHelper::getTableColumns($factTable);
        foreach ($columns as $c) {
            $columnsList[] = $c['column_name'];
        }
    }
}

$kpis['columns'] = $columnsList;

echo json_encode($kpis);
