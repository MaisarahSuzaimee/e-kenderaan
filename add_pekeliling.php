<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    if (!isset($_POST['tajuk']) || empty(trim($_POST['tajuk'])) || 
        !isset($_POST['tarikh_pekeliling']) || empty(trim($_POST['tarikh_pekeliling']))) {
        throw new Exception('Sila lengkapkan semua maklumat yang diperlukan');
    }

    $tajuk = trim($_POST['tajuk']);
    $tarikh_pekeliling = trim($_POST['tarikh_pekeliling']);
    $catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
    
    // Handle file upload
    $fail = '';
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
    } else {
        throw new Exception('Sila pilih fail PDF');
    }

    // Insert record
    $sql = "INSERT INTO pekeliling_kenderaan (tajuk, tarikh_pekeliling, fail, catatan, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $tajuk,
        $tarikh_pekeliling,
        $fail,
        $catatan
    ]);
    
    if (!$result) {
        throw new Exception('Gagal menambah rekod: ' . implode(", ", $stmt->errorInfo()));
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Pekeliling baru telah ditambah!'
    ]);
    
} catch (Exception $e) {
    error_log("Error in add_pekeliling.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

