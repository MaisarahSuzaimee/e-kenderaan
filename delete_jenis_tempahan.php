<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

try {

    if (!isset($_POST['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID tidak diterima.'
        ]);
        exit;
    }

    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM ttempah_jenis WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true
    ]);
    exit;

} catch (Exception $e) {

    error_log("Error deleting record: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Gagal memadam rekod.'
    ]);
    exit;
}
