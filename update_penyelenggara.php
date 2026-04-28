<?php
require 'config.php';
header('Content-Type: application/json');

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get form data
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $no_plat = isset($_POST['no_plat']) ? trim($_POST['no_plat']) : '';
    $tarikh_penyelenggaraan = isset($_POST['tarikh_penyelenggaraan']) ? trim($_POST['tarikh_penyelenggaraan']) : '';
    $butir_penyelenggaraan = isset($_POST['butir_penyelenggaraan']) ? trim($_POST['butir_penyelenggaraan']) : '';
    $kos_penyelenggaraan = isset($_POST['kos_penyelenggaraan']) ? floatval($_POST['kos_penyelenggaraan']) : 0;
    $harga = isset($_POST['harga']) && !empty($_POST['harga']) ? floatval($_POST['harga']) : null;
    $ptj_id = isset($_POST['ptj_id']) && !empty($_POST['ptj_id']) ? intval($_POST['ptj_id']) : null;
    
    // Debug information
    error_log("Update data: ID=$id, No Plat=$no_plat, Tarikh=$tarikh_penyelenggaraan, Kos=$kos_penyelenggaraan");
    
    // Validate required fields
    if ($id <= 0) {
        throw new Exception('Invalid record ID');
    }
    
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
    
    // Check if record exists
    $check_sql = "SELECT id FROM penyelenggara_kenderaan WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$id]);
    
    if ($check_stmt->rowCount() === 0) {
        throw new Exception("Record with ID $id not found");
    }
    
    // Build update query
    $sql = "UPDATE penyelenggara_kenderaan SET 
            no_plat = :no_plat, 
            tarikh_penyelenggaraan = :tarikh, 
            butir_penyelenggaraan = :butir, 
            kos_penyelenggaraan = :kos";
    
    $params = [
        ':no_plat' => $no_plat,
        ':tarikh' => $tarikh_penyelenggaraan,
        ':butir' => $butir_penyelenggaraan,
        ':kos' => $kos_penyelenggaraan
    ];
    
    // Add optional parameters
    if ($harga !== null) {
        $sql .= ", harga = :harga";
        $params[':harga'] = $harga;
    } else {
        $sql .= ", harga = NULL";
    }
    
    if ($ptj_id !== null) {
        $sql .= ", ptj_id = :ptj_id";
        $params[':ptj_id'] = $ptj_id;
    } else {
        $sql .= ", ptj_id = NULL";
    }
    
    // Add updated_at timestamp and WHERE clause
    $sql .= ", updated_at = NOW() WHERE id = :id";
    $params[':id'] = $id;
    
    // Execute update query
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($params);
    
    if (!$result) {
        throw new Exception('Failed to update record: ' . implode(', ', $stmt->errorInfo()));
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Penyelenggaraan berjaya dikemaskini!'
    ]);
    
} catch (Exception $e) {
    error_log("Error in update_penyelenggara.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


