<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    $required_fields = ['no_plat', 'ptj', 'jenis', 'pengeluar', 'model', 'keadaan_semasa', 'tahun_pengeluaran', 'idbahagian'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Field $field is required");
        }
    }

    // Sanitize input
    $no_plat = trim($_POST['no_plat']);
    $ptj = trim($_POST['ptj']);
    $jenis = trim($_POST['jenis']);
    $pengeluar = trim($_POST['pengeluar']);
    $model = trim($_POST['model']);
    $keadaan_semasa = trim($_POST['keadaan_semasa']);
    $tahun_pengeluaran = trim($_POST['tahun_pengeluaran']);
    $idbahagian = trim($_POST['idbahagian']);

    // Check if vehicle already exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM kenderaan_jabatan WHERE no_plat = ?");
    $check->execute([$no_plat]);
    if ($check->fetchColumn() > 0) {
        throw new Exception('Kenderaan dengan no plat ini telah wujud');
    }

    // Insert new vehicle
    $sql = "INSERT INTO kenderaan_jabatan (no_plat, id_ptj, id_jenis, pengeluar, model, keadaan_semasa, tahun_pengeluaran, id_bahagian) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$no_plat, $ptj, $jenis, $pengeluar, $model, $keadaan_semasa, $tahun_pengeluaran, $idbahagian]);

    echo json_encode([
        'success' => true,
        'message' => 'Kenderaan berjaya ditambah!'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>