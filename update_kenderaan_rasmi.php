<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    $required_fields = ['id', 'no_plat', 'model', 'ptj', 'nama_pegawai', 'jawatan', 'gred'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field $field is required");
        }
    }

    // Sanitize input
    $id = (int)$_POST['id'];
    $no_plat = trim($_POST['no_plat']);
    $model = trim($_POST['model']);
    $ptj = trim($_POST['ptj']);
    $nama_pegawai = trim($_POST['nama_pegawai']);
    $jawatan = trim($_POST['jawatan']);
    $gred = trim($_POST['gred']);

    // Check if ID exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM kenderaan_rasmi WHERE id = ?");
    $check->execute([$id]);
    if ($check->fetchColumn() == 0) {
        throw new Exception('Rekod tidak dijumpai');
    }

    // Check if no_plat already exists for another record
    $check_plat = $pdo->prepare("SELECT COUNT(*) FROM kenderaan_rasmi WHERE no_plat = ? AND id != ?");
    $check_plat->execute([$no_plat, $id]);
    if ($check_plat->fetchColumn() > 0) {
        throw new Exception('No plat ini telah digunakan oleh kenderaan lain');
    }

    // Update the record
    $sql = "UPDATE kenderaan_rasmi 
            SET no_plat = ?, model = ?, ptj = ?, nama_pegawai = ?, jawatan = ?, gred = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$no_plat, $model, $ptj, $nama_pegawai, $jawatan, $gred, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Kenderaan rasmi berjaya dikemaskini!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>