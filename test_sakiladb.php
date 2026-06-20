<?php
header('Content-Type: text/plain');
try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=sakiladb";
    $conn = new PDO($dsn, 'postgres', 'fathan1234', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "CONNECTED TO sakiladb!\n";
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    echo "Tables in sakiladb:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['table_name'] . "\n";
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
?>
