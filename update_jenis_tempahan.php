<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_POST['id']) || !isset($_POST['jenis_tempahan'])) {
        throw new Exception('Missing required fields');
    }

    $id = $_POST['id'];
    $jenis_tempahan = trim($_POST['jenis_tempahan']);

    if (empty($jenis_tempahan)) {
        throw new Exception('Jenis tempahan cannot be empty');
    }

    $stmt = $pdo->prepare("UPDATE ttempah_jenis SET jenis_kenderaan = :jenis_tempahan WHERE id = :id");
    $stmt->execute([
        ':jenis_tempahan' => $jenis_tempahan,
        ':id' => $id
    ]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>