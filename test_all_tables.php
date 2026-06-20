<?php
header('Content-Type: text/plain');
$databases = ['postgres', 'sakila', 'sakiladb'];
foreach ($databases as $dbName) {
    try {
        $dsn = "pgsql:host=localhost;port=5432;dbname={$dbName}";
        $conn = new PDO($dsn, 'postgres', 'fathan1234', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "=== TABLES IN DATABASE {$dbName} ===\n";
        $stmt = $conn->query("SELECT schemaname, tablename FROM pg_tables WHERE schemaname NOT IN ('pg_catalog', 'information_schema') ORDER BY schemaname, tablename");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Schema: {$row['schemaname']} | Table: {$row['tablename']}\n";
        }
    } catch (Exception $e) {
        echo "Database {$dbName} Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
?>
