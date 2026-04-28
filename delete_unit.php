<?php
require 'config.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    try {
        $sql = "DELETE FROM tunit WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal memadam rekod');
        } 
        
        echo json_encode([
            'success' => true,
            'message' => 'Rekod berjaya dipadam'
        ]);
    } catch (Exception $e) {
        echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    }
}