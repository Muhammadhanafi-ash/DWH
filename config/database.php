<?php
/**
 * Enterprise DWH Dashboard - Database Connection Configuration (PostgreSQL PDO)
 */

class Database {
    private static $conn = null;
    
    // Connection Parameters — uses Railway env vars with fallback to defaults
    private static $config = null;

    private static function loadConfig() {
        if (self::$config === null) {
            self::$config = [
                'host'     => getenv('PGHOST')     ?: 'gondola.proxy.rlwy.net',
                'port'     => getenv('PGPORT')     ?: '19651',
                'dbname'   => getenv('PGDATABASE') ?: 'railway',
                'username' => getenv('PGUSER')     ?: 'postgres',
                'password' => getenv('PGPASSWORD') ?: 'JRPbgJxWasoPBrNHRzIQKNNEKYAVoQAO'
            ];
        }
        return self::$config;
    }

    /**
     * Retrieve global PDO Connection instance (Singleton).
     */
    public static function getConnection() {
        if (self::$conn === null) {
            $cfg = self::loadConfig();
            $dsn = "pgsql:host=" . $cfg['host'] . ";port=" . $cfg['port'] . ";dbname=" . $cfg['dbname'];
            
            try {
                self::$conn = new PDO($dsn, $cfg['username'], $cfg['password'], [
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
        return self::loadConfig();
    }
}
