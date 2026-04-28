<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$jeniskenderaan = isset($_POST['jeniskenderaan'])
    ? trim($_POST['jeniskenderaan'])
    : '';

if ($id <= 0 || $jeniskenderaan === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Data tidak lengkap.'
    ]);
    exit;
}

try {
    $sql = "UPDATE ttempah_jenis SET jenis_kenderaan = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$jeniskenderaan, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Jenis kenderaan berjaya dikemaskini!'
    ]);
    exit;

} catch (PDOException $e) {

    error_log("Error updating record: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Ralat semasa mengemaskini data.'
    ]);
    exit;
}
