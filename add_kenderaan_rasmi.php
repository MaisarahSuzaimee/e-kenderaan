<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    $required_fields = ['no_plat', 'model', 'ptj', 'nama_pegawai', 'jawatan', 'gred'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field $field is required");
        }
    }

    // Sanitize input
    $no_plat = trim($_POST['no_plat']);
    $model = trim($_POST['model']);
    $ptj = trim($_POST['ptj']);
    $nama_pegawai = trim($_POST['nama_pegawai']);
    $jawatan = trim($_POST['jawatan']);
    $gred = trim($_POST['gred']);

    // Check if vehicle already exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM kenderaan_rasmi WHERE no_plat = ?");
    $check->execute([$no_plat]);
    if ($check->fetchColumn() > 0) {
        throw new Exception('Kenderaan dengan no plat ini telah wujud');
    }

    // Insert new vehicle
    $sql = "INSERT INTO kenderaan_rasmi (no_plat, model, ptj, nama_pegawai, jawatan, gred) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$no_plat, $model, $ptj, $nama_pegawai, $jawatan, $gred]);

    echo json_encode([
        'success' => true,
        'message' => 'Kenderaan rasmi berjaya ditambah!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>