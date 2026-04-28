<?php
require 'config.php';
header('Content-Type: application/json');

try {
    // Validate request
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('ID tidak sah');
    }

    $id = (int)$_GET['id'];

    // Fetch the record
    $sql = "SELECT * FROM tpemeriksaan_berkala WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Rekod tidak dijumpai');
    }
    
    $record = $result->fetch_assoc();
    
    // Format date for HTML date input (YYYY-MM-DD)
    if (isset($record['tarikh_pemeriksaan'])) {
        $record['tarikh_pemeriksaan'] = date('Y-m-d', strtotime($record['tarikh_pemeriksaan']));
    }
    
    echo json_encode([
        'success' => true,
        'record' => $record
    ]);

} catch (Exception $e) {
    error_log("Error in get_pemeriksaan.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
