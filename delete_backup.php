<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    echo json_encode([
        "success" => false,
        "message" => "Akses tidak dibenarkan"
    ]);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode([
        "success" => false,
        "message" => "CSRF token tidak sah"
    ]);
    exit;
}

// Validate file parameter
if (!isset($_POST['file']) || empty($_POST['file'])) {
    echo json_encode([
        "success" => false,
        "message" => "Fail tidak dinyatakan"
    ]);
    exit;
}

// Sanitize filename
$filename = basename($_POST['file']);

// Backup directory
$backupDir = __DIR__ . '/backups/';
$filePath = $backupDir . $filename;

// Check extension
if (pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
    echo json_encode([
        "success" => false,
        "message" => "Format fail tidak sah"
    ]);
    exit;
}

// Check file exists
if (!file_exists($filePath)) {
    echo json_encode([
        "success" => false,
        "message" => "Fail tidak dijumpai"
    ]);
    exit;
}

// Delete file
if (unlink($filePath)) {

    // Log action
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    

    $stmt = $conn->prepare(
        "INSERT INTO system_logs (level, user_id, message, ip_address)
         VALUES (?, ?, ?, ?)"
    );

    $level = "info";
    $message = "Deleted backup file: " . $filename;

    $stmt->bind_param("siss", $level, $userId, $message, $ipAddress);
    $stmt->execute();

    echo json_encode([
        "success" => true
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Gagal memadam fail"
    ]);

}