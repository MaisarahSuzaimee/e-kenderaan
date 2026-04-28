<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get POST data
        $id = $_POST['id'];
        $namapemandu = $_POST['namapemandu'];
        $nokp = $_POST['nokp'];
        $idjawatan = $_POST['idjawatan'];
        $idgred = $_POST['idgred'];
        $idptj = $_POST['idptj'];
        $idbahagian = $_POST['idbahagian'];
        $idunit = $_POST['idunit'] ?: null; // Handle optional unit
        $notelefon = $_POST['notelefon'] ?: null; // Handle optional phone
        $status = $_POST['status'];
        $catatan = $_POST['catatan'] ?: null;

        // Check if nokp exists for other records
        $check_sql = "SELECT id FROM tpemandu WHERE nokp = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $nokp, $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No. KP ini telah wujud!'
            ]);
            exit();
        }

        // Update the record
        $sql = "UPDATE tpemandu SET 
                namapemandu = ?,
                nokp = ?,
                idjawatan = ?,
                idgred = ?,
                idptj = ?,
                idbahagian = ?,
                idunit = ?,
                notelefon = ?,
                status = ?,
                catatan = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiiiisssi", 
            $namapemandu,
            $nokp,
            $idjawatan,
            $idgred,
            $idptj,
            $idbahagian,
            $idunit,
            $notelefon,
            $status,
            $catatan,
            $id
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Pemandu berjaya dikemaskini!'
            ]);
        } else {
            throw new Exception($stmt->error);
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Ralat: ' . $e->getMessage()
        ]);
    }
    exit();
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);

