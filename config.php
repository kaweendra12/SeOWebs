<?php
// Configure DB_HOST, DB_USER, DB_PASSWORD, and DB_NAME in the PHP host.
// Defaults keep the project ready to run with a standard local WampServer setup.
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'lms';

// $conn is the shared database connection used throughout this project.
$conn = new mysqli($host, $user, $pass, $database);

if ($conn->connect_error) {
    error_log('Library database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    die('The library database is unavailable. Check the server configuration.');
}

// Use this helper whenever text from the database is shown in HTML.
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
