<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $namapengeluar = trim($_POST['namapengeluar'] ?? '');

    if (empty($namapengeluar)) {
        echo json_encode([
            'success' => false,
            'message' => 'Sila masukkan nama pengeluar!'
        ]);
        exit;
    }

    try {
        // Check if pengeluar already exists
        $check_sql = "SELECT COUNT(*) FROM tpengeluar WHERE namapengeluar = ?";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$namapengeluar]);

        if ($check_stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Pengeluar ini telah wujud!'
            ]);
            exit;
        }

        // Insert new pengeluar
        $sql = "INSERT INTO tpengeluar (namapengeluar) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$namapengeluar]);

        echo json_encode([
            'success' => true,
            'message' => 'Pengeluar berjaya ditambah!'
        ]);

    } catch (PDOException $e) {
        error_log("Error adding record: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Ralat menambah rekod!'
        ]);
    }
}
