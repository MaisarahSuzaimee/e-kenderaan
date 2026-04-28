<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('ID tidak sah');
    }

    $id = (int)$_POST['id'];

    // Delete the record directly from tjenis table
    // Remove the dependency check since tkenderaan table doesn't exist
    $delete_sql = "DELETE FROM ttempah_jenis WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    
    if (!$stmt->execute([$id])) {
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
