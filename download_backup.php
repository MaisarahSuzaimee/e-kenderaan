<?php
// Start session and check if user is logged in as superadmin
session_start();
require 'config.php';

// Check if user is logged in and is a superadmin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

// Check if file parameter is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header("Location: backup_restore.php?error=no_file");
    exit();
}

// Sanitize filename
$filename = basename($_GET['file']);

// Set backup directory
$backupDir = 'backups/';
$filePath = $backupDir . $filename;

// Check if file exists and has .sql extension
if (!file_exists($filePath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
    header("Location: backup_restore.php?error=invalid_file");
    exit();
}

// Log the download
$userId = $_SESSION['user_id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO system_logs (level, user_id, message, ip_address) VALUES (?, ?, ?, ?)");
$message = "Downloaded backup file: " . $filename;
$level = "info";
$stmt->bind_param("siss", $level, $userId, $message, $ipAddress);
$stmt->execute();

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Clear output buffer
ob_clean();
flush();

// Read file and output to browser
readfile($filePath);
exit;

