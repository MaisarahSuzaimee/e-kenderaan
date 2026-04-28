<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Validate required fields
    if (!isset($_POST['idptj']) || !isset($_POST['bahagian'])) {
        throw new Exception('Missing required fields');
    }
    
    $idptj = trim($_POST['idptj']);
    $bahagian = strtoupper($_POST['bahagian']);
    $unit = isset($_POST['unit']) ? trim($_POST['unit']) : '';
    
    // Validate data
    if (empty($idptj) || empty($bahagian)) {
        throw new Exception('Sila isi semua maklumat yang diperlukan');
    }
    
    // Check if bahagian already exists for this PTJ
    $check = $pdo->prepare("SELECT COUNT(*) FROM tbahagian WHERE idptj = ? AND bahagian = ?");
    $check->execute([$idptj, $bahagian]);
    if ($check->fetchColumn() > 0) {
        throw new Exception('Bahagian ini telah wujud untuk PTJ ini');
    }

    // Start transaction
    $pdo->beginTransaction();
    
    // Find the maximum ID currently in the table and add 1
    $max_id_query = $pdo->query("SELECT COALESCE(MAX(id), 0) FROM tbahagian");
    $max_id = (int)$max_id_query->fetchColumn();
    $next_id = $max_id + 1;
    
    // Make sure next_id is not 0
    if ($next_id <= 0) {
        $next_id = 1;
    }
    
    // Insert with explicit ID to avoid the AUTO_INCREMENT issue
    $stmt = $pdo->prepare("INSERT INTO tbahagian (id, idptj, bahagian) VALUES (?, ?, ?)");
    $stmt->execute([$next_id, $idptj, $bahagian]);
    
    // Get the ID of the newly inserted bahagian
    $bahagianId = $next_id;

    // Insert unit if provided
    if (!empty($unit) && $bahagianId) {
        // Find the maximum ID for unit table
        $max_unit_id_query = $pdo->query("SELECT COALESCE(MAX(id), 0) FROM tunit");
        $max_unit_id = (int)$max_unit_id_query->fetchColumn();
        $next_unit_id = $max_unit_id + 1;
        
        // Make sure next_unit_id is not 0
        if ($next_unit_id <= 0) {
            $next_unit_id = 1;
        }
        
        $unit_stmt = $pdo->prepare("INSERT INTO tunit (id, idbahagian, unit) VALUES (?, ?, ?)");
        $unit_stmt->execute([$next_unit_id, $bahagianId, $unit]);
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Bahagian berjaya ditambah',
        'id' => $bahagianId
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in add_bahagian.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}


