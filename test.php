<?php
header('Content-Type: text/plain');

echo "PDO Drivers:\n";
print_r(PDO::getAvailableDrivers());

$configs = [
    [
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'sakila',
        'username' => 'postgres',
        'password' => 'fathan1234'
    ],
    [
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'schema_sakila',
        'username' => 'postgres',
        'password' => 'hanafi123'
    ],
    [
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'postgres',
        'username' => 'postgres',
        'password' => 'fathan1234'
    ],
    [
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'postgres',
        'username' => 'postgres',
        'password' => 'hanafi123'
    ]
];

foreach ($configs as $idx => $cfg) {
    echo "\nTrying Config $idx ({$cfg['dbname']} / {$cfg['username']}):\n";
    $dsn = "pgsql:host=" . $cfg['host'] . ";port=" . $cfg['port'] . ";dbname=" . $cfg['dbname'];
    try {
        $conn = new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "SUCCESSFULLY CONNECTED!\n";
        
        // List databases
        if ($cfg['dbname'] === 'postgres') {
            $stmt = $conn->query("SELECT datname FROM pg_database WHERE datistemplate = false");
            echo "Databases available:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "- " . $row['datname'] . "\n";
            }
        } else {
            $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
            echo "Tables in database:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "- " . $row['table_name'] . "\n";
            }
        }
    } catch (PDOException $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
