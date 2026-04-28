<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_POST['idptj']) || !isset($_POST['penempatan'])) {
        throw new Exception('Missing required fields');
    }

    $idptj = trim($_POST['idptj']);
    $penempatan = trim($_POST['penempatan']);

    if (empty($penempatan) || empty($idptj)) {
        throw new Exception('Semua medan diperlukan');
    }

    // Check if penempatan already exists for this PTJ
    $check = $pdo->prepare("SELECT COUNT(*) FROM tpenempatan WHERE idptj = ? AND penempatan = ?");
    $check->execute([$idptj, $penempatan]);
    if ($check->fetchColumn() > 0) {
        throw new Exception('Penempatan ini telah wujud untuk PTJ ini');
    }

    // Insert new record
    $stmt = $pdo->prepare("INSERT INTO tpenempatan (idptj, penempatan) VALUES (:idptj, :penempatan)");
    $stmt->execute([
        ':idptj' => $idptj,
        ':penempatan' => $penempatan
    ]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>