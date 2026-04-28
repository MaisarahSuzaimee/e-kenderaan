<?php
require 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $tujuan_perjalanan = trim($_POST['tujuan_perjalanan'] ?? '');

    if (empty($tujuan_perjalanan)) {
        throw new Exception('Sila masukkan tujuan perjalanan');
    }

    // Check if tujuan_perjalanan already exists
    $check = $conn->prepare("SELECT COUNT(*) FROM ttempah_tujuan WHERE tujuan_perjalanan = ?");
    $check->bind_param("s", $tujuan_perjalanan);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        throw new Exception('Tujuan perjalanan ini telah wujud');
    }

    // Insert new record
    $stmt = $conn->prepare("INSERT INTO ttempah_tujuan (tujuan_perjalanan) VALUES (?)");
    $stmt->bind_param("s", $tujuan_perjalanan);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal menambah tujuan perjalanan');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>