<?php
require 'config.php';
header('Content-Type: application/json');

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate data
    if (!isset($data['no_plat']) || empty($data['no_plat'])) {
        throw new Exception('No. Plat diperlukan');
    }
    
    if (!isset($data['pusat_pemeriksaan']) || empty($data['pusat_pemeriksaan'])) {
        throw new Exception('Pusat Pemeriksaan diperlukan');
    }
    
    if (!isset($data['tarikh_pemeriksaan']) || empty($data['tarikh_pemeriksaan'])) {
        throw new Exception('Tarikh Pemeriksaan diperlukan');
    }
    
    // Prepare data for insertion
    $no_plat = trim($data['no_plat']);
    $pusat_pemeriksaan = trim($data['pusat_pemeriksaan']);
    $tarikh_pemeriksaan = $data['tarikh_pemeriksaan'];
    $catatan = isset($data['catatan']) ? trim($data['catatan']) : '';
    
    // Insert the record
    $sql = "INSERT INTO tpemeriksaan_berkala (no_plat, pusat_pemeriksaan, tarikh_pemeriksaan, catatan) 
            VALUES (?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $no_plat, $pusat_pemeriksaan, $tarikh_pemeriksaan, $catatan);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal menambah rekod: ' . $stmt->error);
    }
    
    // Get the new ID
    $new_id = $conn->insert_id;
    
    if (!$new_id) {
        throw new Exception('Gagal mendapatkan ID rekod baru');
    }
    
    // Log the new ID for debugging
    error_log("New record added with ID: $new_id");
    
    // Verify the record was added with the correct ID
    $verify_sql = "SELECT * FROM tpemeriksaan_berkala WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $new_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Rekod ditambah tetapi tidak dapat diverifikasi');
    }
    
    $new_record = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Rekod berjaya ditambah',
        'id' => $new_id,
        'data' => $new_record
    ]);

} catch (Exception $e) {
    error_log("Error in add_pemeriksaan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>


