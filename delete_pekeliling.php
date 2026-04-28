<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    error_log("Received JSON: " . $json); // Log the received JSON
    
    $data = json_decode($json, true);
    
    if (!$data) {
        throw new Exception('Data JSON tidak sah: ' . json_last_error_msg());
    }
    
    if (!isset($data['id'])) {
        throw new Exception('ID tidak diterima dalam data JSON');
    }

    $id = (int)$data['id'];
    error_log("Parsed ID: " . $id); // Log the parsed ID
    
    // Validate ID
    if ($id <= 0) {
        throw new Exception('ID tidak sah: ' . $id);
    }

    // Check if record exists and get file name
    $check_sql = "SELECT id, fail FROM pekeliling_kenderaan WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$id]);
    $pekeliling = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pekeliling) {
        throw new Exception("Rekod dengan ID {$id} tidak dijumpai");
    }

    // Delete the file if it exists
    if (!empty($pekeliling['fail'])) {
        $file_path = 'uploads/pekeliling/' . $pekeliling['fail'];
        if (file_exists($file_path)) {
            if (!unlink($file_path)) {
                error_log("Failed to delete file: " . $file_path);
                // Continue with record deletion even if file deletion fails
            }
        } else {
            error_log("File does not exist: " . $file_path);
        }
    }

    // Delete the record
    $delete_sql = "DELETE FROM pekeliling_kenderaan WHERE id = ?";
    $stmt = $pdo->prepare($delete_sql);
    
    if (!$stmt->execute([$id])) {
        throw new Exception("Gagal memadam rekod: " . implode(", ", $stmt->errorInfo()));
    }

    if ($stmt->rowCount() === 0) {
        throw new Exception("Tiada rekod dipadam untuk ID {$id}");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Rekod berjaya dipadam',
        'debug_info' => [
            'id' => $id,
            'affected_rows' => $stmt->rowCount()
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in delete_pekeliling.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

