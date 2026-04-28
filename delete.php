<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Sesi tamat. Sila log masuk semula.']);
    exit;
}

try {
    // Validate input
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('ID tidak sah');
    }

    $id = (int)$_POST['id'];
    $pemohon = $_SESSION['username'];

    // First check if the record exists and belongs to the user
    $check_sql = "SELECT id, kelulusan FROM tempahan_kenderaan WHERE id = ? AND pemohon = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $id, $pemohon);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Rekod tidak dijumpai atau bukan milik anda');
    }
    
    // Check if the record can be deleted based on its status
    $record = $result->fetch_assoc();
    if (!in_array($record['kelulusan'], ['BARU', 'TIDAK LULUS'])) {
        throw new Exception('Hanya tempahan dengan status BARU atau TIDAK LULUS boleh dipadam');
    }
    
    // Start a transaction to ensure all operations succeed or fail together
    $conn->begin_transaction();
    
    // First delete related notifications
    $delete_notifications_sql = "DELETE FROM notifications WHERE booking_id = ?";
    $notifications_stmt = $conn->prepare($delete_notifications_sql);
    $notifications_stmt->bind_param("i", $id);
    $notifications_stmt->execute();
    
    // Then delete the booking record
    $delete_sql = "DELETE FROM tempahan_kenderaan WHERE id = ? AND pemohon = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("is", $id, $pemohon);
    
    if (!$stmt->execute()) {
        // Roll back the transaction if deletion fails
        $conn->rollback();
        throw new Exception('Gagal memadam rekod: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        // Roll back the transaction if no rows were affected
        $conn->rollback();
        throw new Exception('Tiada rekod dipadam');
    }
    
    // Commit the transaction if everything succeeded
    $conn->commit();
    
    // Log the successful deletion
    error_log("User {$pemohon} successfully deleted booking ID: {$id}");
    
    echo json_encode([
        'success' => true,
        'message' => 'Rekod berjaya dipadam'
    ]);

} catch (Exception $e) {
    // Ensure transaction is rolled back on error
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    error_log("Delete error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
