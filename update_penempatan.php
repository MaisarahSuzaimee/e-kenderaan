<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int)($_POST['id'] ?? 0);
    $idptj = (int)($_POST['idptj'] ?? 0);
    $penempatan = trim($_POST['penempatan'] ?? '');

    if ($id <= 0 || $idptj <= 0 || $penempatan === '') {
        $response['message'] = 'Data tidak sah!';
        echo json_encode($response);
        exit;
    }

    try {
        // Check duplicate
        $check_sql = "SELECT id FROM tpenempatan 
                      WHERE idptj = ? AND penempatan = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("isi", $idptj, $penempatan, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $response['message'] = 'Penempatan ini telah wujud untuk PTJ yang dipilih!';
        } else {
            $update_sql = "UPDATE tpenempatan 
                           SET idptj = ?, penempatan = ? 
                           WHERE id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("isi", $idptj, $penempatan, $id);

            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                $response['message'] = 'Gagal mengemaskini rekod!';
            }
        }

    } catch (Exception $e) {
        $response['message'] = 'Ralat sistem: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Kaedah tidak dibenarkan!';
}

echo json_encode($response);
exit;
