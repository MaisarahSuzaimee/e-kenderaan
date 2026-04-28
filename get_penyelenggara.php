<?php
require 'config.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('ID is required');
    }
    
    $id = intval($_GET['id']);
    
    $sql = "SELECT * FROM penyelenggara_kenderaan WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        throw new Exception('Record not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $record
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
