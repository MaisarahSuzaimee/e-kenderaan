<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int)($_POST['id'] ?? 0);
    $namaptj = trim($_POST['namaptj'] ?? '');
    // $namaringkas = trim($_POST['namaringkas'] ?? '');

    if ($id <= 0 || $namaptj === '') {
        $response['message'] = 'Data tidak sah!';
        echo json_encode($response);
        exit;
    }

    try {
        // Check duplicate PTJ / short name
        $check_sql = "
            SELECT id FROM ptjs
            WHERE (nama_ptj = ?) 
            AND id != ?
        ";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $namaptj, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $response['message'] = 'PTJ atau nama ringkas ini telah wujud!';
        } else {
            // Update record
            $update_sql = "
                UPDATE ptjs 
                SET nama_ptj = ?
                WHERE id = ?
            ";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $namaptj, $id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Rekod berjaya dikemaskini!';
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
