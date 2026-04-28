<?php
// Start session and check if user is logged in as superadmin
session_start();
require 'config.php';

// Check if user is logged in and is a superadmin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=system_logs_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV header
fputcsv($output, ['ID', 'Level', 'User', 'Message', 'IP Address', 'Date']);

// Get logs from database
$sql = "SELECT l.*, u.nama as username 
        FROM system_logs l 
        LEFT JOIN penggunajkn u ON l.user_id = u.id 
        ORDER BY l.created_at DESC";
$result = $conn->query($sql);

// Write data rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $username = $row['username'] ?? 'System';
        
        fputcsv($output, [
            $row['id'],
            ucfirst($row['level']),
            $username,
            $row['message'],
            $row['ip_address'],
            date('d/m/Y H:i:s', strtotime($row['created_at']))
        ]);
    }
}

// Close the output stream
fclose($output);

// Log the export
$userId = $_SESSION['user_id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO system_logs (level, user_id, message, ip_address) VALUES (?, ?, ?, ?)");
$message = "System logs exported to CSV";
$level = "info";
$stmt->bind_param("siss", $level, $userId, $message, $ipAddress);
$stmt->execute();

exit;