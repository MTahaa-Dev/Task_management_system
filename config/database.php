<?php
/**
 * Database Configuration File
 * Uses PDO for secure database connections
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'task_management_system');
define('DB_USER', 'root'); // Change this in production
define('DB_PASS', '');     // Change this in production

try {
    // Set DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Set PDO options
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Prevent SQL injection by using real prepared statements
    ];
    
    // Create a PDO instance
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    // If there is an error with the connection, stop the script and display it
    // In production, this should be logged instead of displayed to the user
    die("Database connection failed: " . $e->getMessage());
}
?>
