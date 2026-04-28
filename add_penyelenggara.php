<?php
require 'config.php';
header('Content-Type: application/json');

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get form data
    $no_plat = isset($_POST['no_plat']) ? trim($_POST['no_plat']) : '';
    $tarikh_penyelenggaraan = isset($_POST['tarikh_penyelenggaraan']) ? trim($_POST['tarikh_penyelenggaraan']) : '';
    $butir_penyelenggaraan = isset($_POST['butir_penyelenggaraan']) ? trim($_POST['butir_penyelenggaraan']) : '';
    $kos_penyelenggaraan = isset($_POST['kos_penyelenggaraan']) ? floatval($_POST['kos_penyelenggaraan']) : 0;
    $harga = isset($_POST['harga']) && !empty($_POST['harga']) ? floatval($_POST['harga']) : null;
    $ptj_id = isset($_POST['ptj_id']) && !empty($_POST['ptj_id']) ? intval($_POST['ptj_id']) : null;
    
    // Validate required fields
    if (empty($no_plat)) {
        throw new Exception('No. Plat diperlukan');
    }
    
    if (empty($tarikh_penyelenggaraan)) {
        throw new Exception('Tarikh penyelenggaraan diperlukan');
    }
    
    if (empty($butir_penyelenggaraan)) {
        throw new Exception('Butir penyelenggaraan diperlukan');
    }
    
    if ($kos_penyelenggaraan <= 0) {
        throw new Exception('Kos penyelenggaraan mesti lebih dari 0');
    }
    
    // Use PDO for database operations
    $sql = "INSERT INTO penyelenggara_kenderaan 
            (no_plat, tarikh_penyelenggaraan, butir_penyelenggaraan, kos_penyelenggaraan, harga, ptj_id, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $no_plat,
        $tarikh_penyelenggaraan,
        $butir_penyelenggaraan,
        $kos_penyelenggaraan,
        $harga,
        $ptj_id
    ]);
    
    if (!$result) {
        throw new Exception('Gagal menambah rekod: ' . implode(', ', $stmt->errorInfo()));
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Penyelenggaraan berjaya ditambah!',
        'id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log("Error in add_penyelenggara.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
