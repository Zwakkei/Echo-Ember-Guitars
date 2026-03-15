<?php
// ============================================
// DATABASE CONFIGURATION EXAMPLE
// Copy this file to database.php and update with your credentials
// ============================================

// Local development (XAMPP)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'echo_ember_guitars');

// Production (InfinityFree) - Uncomment when deploying
/*
define('DB_HOST', 'sql123.infinityfree.com'); // Get from control panel
define('DB_USER', 'if0_12345678'); // Your database username
define('DB_PASS', 'your_strong_password'); // Your database password
define('DB_NAME', 'if0_12345678_echoember'); // Your database name
*/

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}
?>