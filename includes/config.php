<?php
// Database Configuration — UPDATE THESE IF NEEDED
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crackers_db');

// Site Configuration
define('SITE_NAME', 'Sri Shenbaga Crackers');
define('SITE_TAGLINE', 'Best Quality Fireworks Online @ Affordable Prices');
define('SITE_PHONE1', '99945 25990');
define('SITE_PHONE2', '99656 29748');
define('SITE_EMAIL', 'srishenbagacrackers@gmail.com');
define('SITE_ADDRESS', 'Near Sattur Tollgate, Sattur - 626203');

// Auto-detect BASE_URL (works for localhost/crackers/ AND domain root)
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $doc_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? getcwd());
    // project root = the "crackers" folder (parent of "includes")
    $project  = realpath(__DIR__ . '/..');
    $rel      = str_replace('\\', '/', substr($project, strlen($doc_root)));
    return rtrim($protocol . '://' . $host . $rel, '/');
}
if (!defined('BASE_URL')) define('BASE_URL', getBaseUrl());

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="padding:20px;background:#c0392b;color:#fff;font-family:sans-serif;border-radius:8px;margin:20px;">
                <h2>&#9888; Database Connection Failed</h2>
                <p>Please import <b>database.sql</b> and update credentials in <b>includes/config.php</b></p>
                <p><b>Error:</b> ' . htmlspecialchars($conn->connect_error) . '</p>
            </div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function calcDiscountPrice($actual_price, $discount_percent) {
    return round($actual_price * (1 - $discount_percent / 100), 2);
}

function formatPrice($price) {
    return '&#8377;' . number_format((float)$price, 2);
}

function generateOrderNumber() {
    return 'CRK' . date('Ymd') . strtoupper(substr(uniqid(), -5));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
