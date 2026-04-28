<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_POST['namaptj'])) {
        throw new Exception('Missing required fields');
    }

    $namaptj = trim($_POST['namaptj']);
    // $namaringkas = trim($_POST['namaringkas']);

    if (empty($namaptj) ) {
        throw new Exception('Semua medan diperlukan');
    }

    // Check if PTJ already exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM ptjs WHERE nama_ptj = ?");
    $check->execute([$namaptj]);
    if ($check->fetchColumn() > 0) {
        throw new Exception('PTJ ini telah wujud');
    }

    // Insert new PTJ
    $stmt = $pdo->prepare("INSERT INTO ptjs (nama_ptj) VALUES (?)");
    $stmt->execute([$namaptj]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}