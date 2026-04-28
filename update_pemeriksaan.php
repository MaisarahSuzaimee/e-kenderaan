<?php
require 'config.php';
header('Content-Type: application/json');

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate data
    if (!isset($data['id']) || empty($data['id'])) {
        throw new Exception('ID tidak sah');
    }
    
    if (!isset($data['no_plat']) || empty($data['no_plat'])) {
        throw new Exception('No. Plat diperlukan');
    }
    
    if (!isset($data['pusat_pemeriksaan']) || empty($data['pusat_pemeriksaan'])) {
        throw new Exception('Pusat Pemeriksaan diperlukan');
    }
    
    if (!isset($data['tarikh_pemeriksaan']) || empty($data['tarikh_pemeriksaan'])) {
        throw new Exception('Tarikh Pemeriksaan diperlukan');
    }
    
    // Prepare data for update
    $id = (int)$data['id'];
    $no_plat = trim($data['no_plat']);
    $pusat_pemeriksaan = trim($data['pusat_pemeriksaan']);
    $tarikh_pemeriksaan = $data['tarikh_pemeriksaan'];
    $catatan = isset($data['catatan']) ? trim($data['catatan']) : '';
    
    // Update the record
    $sql = "UPDATE tpemeriksaan_berkala SET 
            no_plat = ?,
            pusat_pemeriksaan = ?,
            tarikh_pemeriksaan = ?,
            catatan = ?
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $no_plat, $pusat_pemeriksaan, $tarikh_pemeriksaan, $catatan, $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal mengemaskini rekod: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        // No rows were updated, but it might be because no changes were made
        // Check if record exists
        $check_sql = "SELECT id FROM tpemeriksaan_berkala WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            throw new Exception('Rekod tidak dijumpai');
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Rekod berjaya dikemaskini'
    ]);

} catch (Exception $e) {
    error_log("Error in update_pemeriksaan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

