<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

try {
    // Validate ID parameter
    if (!isset($_GET['id'])) {
        throw new Exception('ID parameter tidak ditemui');
    }
    
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if ($id === false || $id <= 0) {
        throw new Exception('ID tidak sah: ' . $_GET['id']);
    }
    
    // Log the delete attempt
    error_log("Attempting to delete record with ID: $id");
    
    // Check if record exists before deleting
    $check_sql = "SELECT id FROM tpemeriksaan_berkala WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception("Rekod dengan ID $id tidak dijumpai");
    }
    
    // Delete the record
    $delete_sql = "DELETE FROM tpemeriksaan_berkala WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception('Gagal memadam rekod: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("Tiada rekod dipadam untuk ID $id");
    }
    
    // Log success
    error_log("Successfully deleted record with ID: $id");
    
    echo json_encode([
        'success' => true,
        'message' => 'Pemeriksaan berkala berjaya dipadam!',
        'debug_info' => [
            'id' => $id,
            'affected_rows' => $stmt->affected_rows
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in delete_pemeriksaan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug_info' => [
            'id' => $_GET['id'] ?? 'not set',
            'filtered_id' => $id ?? 'not set'
        ]
    ]);
}
?>


