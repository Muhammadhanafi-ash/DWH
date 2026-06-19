<?php
/**
 * Enterprise DWH Dashboard - DataTables Server-Side Processing API
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

$db = Database::getConnection();
if (!$db) {
    echo json_encode([
        "draw" => 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ]);
    exit;
}

// Get params
$factTable = $_REQUEST['fact_table'] ?? 'fact_sales';
$draw = (int)($_POST['draw'] ?? $_GET['draw'] ?? 1);
$start = (int)($_POST['start'] ?? $_GET['start'] ?? 0);
$length = (int)($_POST['length'] ?? $_GET['length'] ?? 10);
$searchValue = $_POST['search']['value'] ?? $_GET['search']['value'] ?? '';

// Build filters array
$filters = [
    'start_date' => $_REQUEST['start_date'] ?? null,
    'end_date' => $_REQUEST['end_date'] ?? null,
    'year' => $_REQUEST['year'] ?? null,
    'month' => $_REQUEST['month'] ?? null,
    'category' => $_REQUEST['category'] ?? null,
    'region' => $_REQUEST['region'] ?? null,
    'search' => $searchValue
];

try {
    // 1. Get Total (unfiltered) Records
    $totalQuery = "SELECT COUNT(*) FROM public." . '"' . str_replace('"', '""', $factTable) . '"';
    $recordsTotal = (int)$db->query($totalQuery)->fetchColumn();
    
    // 2. Get Filtered Record Count
    $countInfo = DWHHelper::buildDynamicQuery($factTable, $filters, "COUNT(*)");
    $stmtCount = $db->prepare($countInfo['sql']);
    $stmtCount->execute($countInfo['params']);
    $recordsFiltered = (int)$stmtCount->fetchColumn();
    
    // 3. Build Ordering
    $orderBy = null;
    $columns = DWHHelper::getTableColumns($factTable);
    $orderColIdx = (int)($_POST['order'][0]['column'] ?? $_GET['order'][0]['column'] ?? 0);
    $orderDir = trim($_POST['order'][0]['dir'] ?? $_GET['order'][0]['dir'] ?? 'desc');
    if ($orderDir !== 'asc' && $orderDir !== 'desc') {
        $orderDir = 'desc';
    }
    
    if (isset($columns[$orderColIdx])) {
        $orderBy = "f." . '"' . str_replace('"', '""', $columns[$orderColIdx]['column_name']) . '"' . " " . $orderDir;
    } else {
        // Fallback to primary key or first column
        if (!empty($columns)) {
            $orderBy = "f." . '"' . str_replace('"', '""', $columns[0]['column_name']) . '"' . " DESC";
        }
    }
    
    // 4. Get Data Rows
    $dataInfo = DWHHelper::buildDynamicQuery($factTable, $filters, null, null, $orderBy, $length, $start);
    $stmtData = $db->prepare($dataInfo['sql']);
    $stmtData->execute($dataInfo['params']);
    $data = $stmtData->fetchAll(PDO::FETCH_ASSOC);
    
    // Post-process row fields to make them pretty and human-readable (e.g. format decimals)
    $prettifiedData = [];
    foreach ($data as $row) {
        $prettifiedRow = [];
        foreach ($row as $key => $val) {
            // Check numeric values and format them
            if (is_numeric($val) && (strpos($key, 'amount') !== false || strpos($key, 'sales') !== false || strpos($key, 'cost') !== false)) {
                $prettifiedRow[$key] = '$' . number_format((float)$val, 2);
            } else if (is_null($val)) {
                $prettifiedRow[$key] = '-';
            } else if (is_bool($val)) {
                $prettifiedRow[$key] = $val ? 'Aktif' : 'Nonaktif';
            } else {
                $prettifiedRow[$key] = $val;
            }
        }
        $prettifiedData[] = $prettifiedRow;
    }
    
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "data" => $prettifiedData
    ]);
} catch (Exception $e) {
    error_log("Tables API Error: " . $e->getMessage());
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => $e->getMessage()
    ]);
}
