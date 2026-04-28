<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_POST['jenis_kenderaan'])) {
        throw new Exception('Missing required fields');
    }

    $jenis_kenderaan = trim($_POST['jenis_kenderaan']);

    if (empty($jenis_kenderaan)) {
        throw new Exception('Jenis kenderaan cannot be empty');
    }

    // Check if jenis_kenderaan already exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM ttempah_jenis WHERE jenis_kenderaan = ?");
    $check->execute([$jenis_kenderaan]);
    if ($check->fetchColumn() > 0) {
        throw new Exception('Jenis kenderaan already exists');
    }

    // Insert new record
    $stmt = $pdo->prepare("INSERT INTO ttempah_jenis (jenis_kenderaan) VALUES (:jenis_kenderaan)");
    $stmt->execute([':jenis_kenderaan' => $jenis_kenderaan]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
