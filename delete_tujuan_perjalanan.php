<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('ID tidak sah');
    }

    $id = (int)$_POST['id'];

    // Delete the record
    $delete_sql = "DELETE FROM ttempah_tujuan WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
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
?>



