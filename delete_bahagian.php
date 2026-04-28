<?php
session_start();
require 'config.php';

if (isset($_POST['id'])) {
    try {
        $id = (int)$_POST['id'];
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Delete related units first
        $sql_unit = "DELETE FROM tunit WHERE idbahagian = ?";
        $stmt_unit = $pdo->prepare($sql_unit);
        $stmt_unit->execute([$id]);
        
        // Then delete the bahagian
        $sql_bahagian = "DELETE FROM tbahagian WHERE id = ?";
        $stmt_bahagian = $pdo->prepare($sql_bahagian);
        $stmt_bahagian->execute([$id]);
        
        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Rekod berjaya dipadam'
        ]);
        
        // header("Location: Bahagian.php?success=2");
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        // header("Location: Bahagian.php?error=" . urlencode($e->getMessage()));
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ID'
    ]);
    // header("Location: Bahagian.php?error=Invalid_ID");
}
exit;