<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        $response['message'] = 'ID tidak sah!';
        echo json_encode($response);
        exit;
    }

    try {
        // Check if PTJ is used elsewhere
        $check_sql = "
            SELECT 
                (SELECT COUNT(*) FROM tpenempatan WHERE idptj = ?) +
                (SELECT COUNT(*) FROM tbahagian WHERE idptj = ?) 
            AS total_usage
        ";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $id, $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['total_usage'] > 0) {
            $response['message'] =
                'PTJ ini tidak boleh dipadam kerana sedang digunakan dalam sistem!';
        } else {
            $delete_sql = "DELETE FROM ptjs WHERE id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Rekod berjaya dipadam!';
            } else {
                $response['message'] = 'Gagal memadam rekod!';
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



?>
