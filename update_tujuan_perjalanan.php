<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
//     exit;
// }

if (
    !isset($_SESSION['user_id']) ||
    !in_array($_SESSION['role'], ['admin', 'superadmin'], true)
) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $tujuan_perjalanan = trim(filter_input(INPUT_POST, 'tujuan_perjalanan', FILTER_SANITIZE_STRING));
    
    if (empty($tujuan_perjalanan)) {
        echo json_encode(['success' => false, 'message' => 'Sila isi tujuan perjalanan']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE ttempah_tujuan SET tujuan_perjalanan = ? WHERE id = ?");
        $stmt->bind_param("si", $tujuan_perjalanan, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Update failed");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengemaskini tujuan perjalanan']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}