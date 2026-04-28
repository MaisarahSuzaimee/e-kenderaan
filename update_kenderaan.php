<?php
session_start();
require_once 'config.php';

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get form data
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$no_plat = trim($_POST['no_plat'] ?? '');
$ptj = trim($_POST['ptj'] ?? '');
$jenis = trim($_POST['jenis'] ?? '');
$pengeluar = trim($_POST['pengeluar'] ?? '');
$model = trim($_POST['model'] ?? '');
$keadaan_semasa = trim($_POST['keadaan_semasa'] ?? '');
$tahun_pengeluaran = trim($_POST['tahun_pengeluaran'] ?? '');
$idbahagian = trim($_POST['idbahagian'] ?? '');

// Validate required fields
if (empty($id) || empty($no_plat) || empty($ptj) || empty($jenis) || 
    empty($pengeluar) || empty($model) || empty($keadaan_semasa) || 
    empty($tahun_pengeluaran || empty($idbahagian))) {
    echo json_encode([
        'success' => false,
        'message' => 'Sila isi semua maklumat yang diperlukan'
    ]);
    exit;
}

try {
    // Check if record exists
    $check_sql = "SELECT id FROM kenderaan_jabatan WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute(['id' => $id]);
    
    if ($check_stmt->rowCount() === 0) {
        throw new Exception('Rekod tidak dijumpai');
    }
    
    // Update record
    $sql = "UPDATE kenderaan_jabatan 
            SET no_plat = :no_plat,
                id_ptj = :ptj,
                id_jenis = :jenis,
                pengeluar = :pengeluar,
                model = :model,
                keadaan_semasa = :keadaan_semasa,
                tahun_pengeluaran = :tahun_pengeluaran,
                id_bahagian = :idbahagian,
                updated_at = NOW()
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'no_plat' => $no_plat,
        'ptj' => $ptj,
        'jenis' => $jenis,
        'pengeluar' => $pengeluar,
        'model' => $model,
        'keadaan_semasa' => $keadaan_semasa,
        'tahun_pengeluaran' => $tahun_pengeluaran,
        'idbahagian' => $idbahagian,
        'id' => $id

    ]);
    
    // Check if update was successful
    if ($stmt->rowCount() > 0) {
        // Fetch the updated record
        $select_sql = "SELECT * FROM kenderaan_jabatan WHERE id = :id";
        $select_stmt = $pdo->prepare($select_sql);
        $select_stmt->execute(['id' => $id]);
        $updated_record = $select_stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Maklumat kenderaan berjaya dikemaskini',
            'data' => $updated_record
        ]);
    } else {
        // No changes were made
        echo json_encode([
            'success' => true,
            'message' => 'Tiada perubahan dibuat pada rekod'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Database error in update_kenderaan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ralat pangkalan data: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>