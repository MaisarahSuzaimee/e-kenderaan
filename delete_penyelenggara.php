<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Validate and sanitize input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Validate required fields
    if ($id <= 0) {
        throw new Exception('ID tidak sah');
    }
    
    // Check if record exists
    $check_sql = "SELECT id FROM penyelenggara_kenderaan WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$id]);
    
    if ($check_stmt->rowCount() === 0) {
        throw new Exception("Rekod dengan ID {$id} tidak dijumpai");
    }
    
    // Delete the record
    $delete_sql = "DELETE FROM penyelenggara_kenderaan WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    
    if (!$stmt->execute([$id])) {
        throw new Exception("Gagal memadam rekod: " . implode(', ', $stmt->errorInfo()));
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Rekod berjaya dipadam'
    ]);
    
} catch (Exception $e) {
    error_log("Error in delete_penyelenggara.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
