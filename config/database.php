<?php
/**
 * Database Configuration File
 * Uses PDO for secure database connections
 */

// Database credentials
define('DB_HOST', 'centerbeam.proxy.rlwy.net');
define('DB_PORT', '56716'); // VERY IMPORTANT
define('DB_NAME', 'railway');
define('DB_USER', 'root');
define('DB_PASS', 'gnZmhCIKxuJOMLDgSbtPSMQlLkjwHrYa');

try {

    // DSN with PORT added
    $dsn = "mysql:host=" . DB_HOST . 
           ";port=" . DB_PORT . 
           ";dbname=" . DB_NAME . 
           ";charset=utf8mb4";

    // PDO options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Create PDO connection
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // echo "Connected successfully";

} catch (PDOException $e) {

    die("Database connection failed: " . $e->getMessage());

}
?>