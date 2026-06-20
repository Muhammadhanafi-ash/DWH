<?php
/**
 * Enterprise DWH Dashboard - Database Connection Configuration (PostgreSQL PDO)
 */

class Database {
    private static $conn = null;
    
    // Connection Parameters
    private static $config = [
        'host' => 'gondola.proxy.rlwy.net',
        'port' => '19651',
        'dbname' => 'railway',
        'username' => 'postgres',
        'password' => 'JRPbgJxWasoPBrNHRzIQKNNEKYAVoQAO'
    ];

    /**
     * Retrieve global PDO Connection instance (Singleton).
     */
    public static function getConnection() {
        if (self::$conn === null) {
            $dsn = "pgsql:host=" . self::$config['host'] . ";port=" . self::$config['port'] . ";dbname=" . self::$config['dbname'];
            
            try {
                self::$conn = new PDO($dsn, self::$config['username'], self::$config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true
                ]);
                
                // Set UTF-8 client encoding
                self::$conn->exec("SET client_encoding = 'UTF8'");
            } catch (PDOException $e) {
                self::$conn = false;
                error_log("Database connection error: " . $e->getMessage());
                throw $e;
            }
        }
        
        return self::$conn;
    }

    public static function getConfig() {
        return self::$config;
    }
}
