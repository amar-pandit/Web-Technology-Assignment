<?php
/**
 * config/db.php - database connection and global settings.
 * Change the credentials below to match your MySQL server.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_management');

// Upload settings
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads');   // private folder
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024);              // 2 MB
define('MAX_DOC_SIZE', 5 * 1024 * 1024);                // 5 MB

// Make mysqli throw exceptions so errors can be handled with try/catch
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Log the real error, show a generic message to the user
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Database connection failed. Please check config/db.php and make sure MySQL is running.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
