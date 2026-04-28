<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Check if ID is provided
    if (!isset($_POST['id'])) {
        throw new Exception('ID tidak diterima');
    }

    $id = (int)$_POST['id'];
    
    // Validate ID
    if ($id <= 0) {
        throw new Exception('ID tidak sah');
    }

    // Check if record exists
    $check_sql = "SELECT id, no_plat FROM kenderaan_jabatan WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$id]);
    $kenderaan = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kenderaan) {
        throw new Exception("Rekod dengan ID {$id} tidak dijumpai");
    }

    // Delete the record
    $delete_sql = "DELETE FROM kenderaan_jabatan WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    
    if (!$stmt->execute([$id])) {
        throw new Exception("Gagal memadam rekod: " . implode(", ", $stmt->errorInfo()));
    }

    echo json_encode([
        'success' => true,
        'message' => "Kenderaan {$kenderaan['no_plat']} berjaya dipadam!"
    ]);

} catch (Exception $e) {
    error_log("Error in delete_kenderaan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>