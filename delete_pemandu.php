<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID tidak sah');
    }

    $id = (int)$_GET['id'];

    // For debugging - log the ID we're trying to delete
    error_log("Attempting to delete pemandu with ID: " . $id);

    // First, let's check if the ID exists
    $check_sql = "SELECT id FROM tpemandu WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("ID pemandu tidak dijumpai");
    }

    // Try to delete directly from tpemandu first
    $delete_sql = "DELETE FROM tpemandu WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);

    // For debugging - log any SQL errors
    if (!$stmt->execute()) {
        error_log("SQL Error: " . $stmt->error);
        throw new Exception("Gagal memadam rekod: " . $stmt->error);
    }

    // If we got here, deletion was successful
    echo json_encode([
        'success' => true,
        'message' => 'Pemandu berjaya dipadam!'
    ]);

}
catch (Exception $e) {
    error_log("Error in delete_pemandu.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>







