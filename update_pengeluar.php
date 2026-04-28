<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $namapengeluar = trim($_POST['namapengeluar'] ?? '');

    if ($id <= 0 || empty($namapengeluar)) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak sah'
        ]);
        exit;
    }

    try {
        $sql = "UPDATE tpengeluar SET namapengeluar = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$namapengeluar, $id]);

        echo json_encode([
            'success' => true,
            'message' => 'Pengeluar berjaya dikemaskini'
        ]);

    } catch (PDOException $e) {
        error_log("Error updating record: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ralat semasa mengemaskini rekod'
        ]);
    }
}
