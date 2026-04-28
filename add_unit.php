<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    error_log("Received POST data: " . print_r($_POST, true));

    $unit = strtoupper($_POST['unit'] ?? '');
    $idbahagian = trim($_POST['idbahagian'] ?? '');

    // Validate input
    if (empty($unit) || empty($idbahagian)) {
        echo json_encode([
            'success' => false,
            'message' => 'Sila isi semua maklumat yang diperlukan'
        ]);
        exit;
    }

    try {
        $sql = "INSERT INTO tunit (unit, idbahagian) 
                VALUES (:unit, :idbahagian)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':unit' => $unit,
            ':idbahagian' => $idbahagian
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Rekod berjaya ditambah'
        ]);

    } catch (PDOException $e) {
        error_log("Error in add_unit.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ralat pangkalan data'
        ]);
    }
}
