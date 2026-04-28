<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? '';
    $unit = strtoupper($_POST['unit'] ?? '');
    $idbahagian = $_POST['idbahagian'] ?? '';

    // Basic validation
    if (empty($id) || empty($unit) || empty($idbahagian)) {
        echo json_encode([
            'success' => false,
            'message' => 'Sila lengkapkan semua maklumat'
        ]);
        exit;
    }

    try {
        $sql = "UPDATE tunit SET unit = ?, idbahagian = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $unit, $idbahagian, $id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Rekod berjaya dikemaskini'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal mengemaskini rekod'
            ]);
        }

    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ralat sistem'
        ]);
    }
}
