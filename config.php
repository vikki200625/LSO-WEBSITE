<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_default = 'donationdb';

function db_connect($dbName = null) {
    global $db_host, $db_user, $db_pass, $db_default;
    if ($dbName === null) $dbName = $db_default;
    $conn = new mysqli($db_host, $db_user, $db_pass, $dbName);
    if ($conn->connect_error) {
        http_response_code(500);
        die('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (PHP_VERSION_ID < 70300) {
        session_set_cookie_params(0, '/', '', $secure, true);
    } else {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    session_start();
}