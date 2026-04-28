<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    if (!isset($_POST['id']) || empty($_POST['id']) ||
        !isset($_POST['tajuk']) || empty(trim($_POST['tajuk'])) || 
        !isset($_POST['tarikh_pekeliling']) || empty(trim($_POST['tarikh_pekeliling']))) {
        throw new Exception('Sila lengkapkan semua maklumat yang diperlukan');
    }

    $id = (int)$_POST['id'];
    $tajuk = trim($_POST['tajuk']);
    $tarikh_pekeliling = trim($_POST['tarikh_pekeliling']);
    $catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
    
    // Check if record exists
    $check_sql = "SELECT id, fail FROM pekeliling_kenderaan WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$id]);
    $pekeliling = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pekeliling) {
        throw new Exception("Rekod dengan ID {$id} tidak dijumpai");
    }
    
    $current_fail = $pekeliling['fail'];
    $fail = $current_fail; // Default to current file if no new file uploaded
    
    // Handle file upload if a new file is provided
    if (isset($_FILES['fail']) && $_FILES['fail']['error'] === UPLOAD_ERR_OK) {
        // Create directory if it doesn't exist
        $upload_dir = 'uploads/pekeliling/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $fail = time() . '_' . basename($_FILES['fail']['name']);
        $upload_path = $upload_dir . $fail;
        
        // Check file type
        $allowed_types = ['application/pdf'];
        $file_type = $_FILES['fail']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Hanya fail PDF dibenarkan');
        }
        
        if (!move_uploaded_file($_FILES['fail']['tmp_name'], $upload_path)) {
            throw new Exception('Gagal memuat naik fail');
        }
        
        // Delete old file if exists
        if (!empty($current_fail) && file_exists($upload_dir . $current_fail)) {
            unlink($upload_dir . $current_fail);
        }
    }

    // Update record
    $sql = "UPDATE pekeliling_kenderaan 
            SET tajuk = ?, 
                tarikh_pekeliling = ?, 
                fail = ?, 
                catatan = ?,
                updated_at = NOW()
            WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $tajuk,
        $tarikh_pekeliling,
        $fail,
        $catatan,
        $id
    ]);
    
    if (!$result) {
        throw new Exception('Gagal mengemaskini rekod: ' . implode(", ", $stmt->errorInfo()));
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Pekeliling telah dikemaskini!'
    ]);
    
} catch (Exception $e) {
    error_log("Error in update_pekeliling.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

