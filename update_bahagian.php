<?php
require 'config.php';

header('Content-Type: application/json');

// Enable error reporting (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = ['success' => false];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Kaedah tidak dibenarkan');
    }

    // Validate required fields
    if (!isset($_POST['id'], $_POST['idptj'], $_POST['bahagian'])) {
        throw new Exception('Sila isi semua maklumat yang diperlukan');
    }

    $id = (int) $_POST['id'];
    $idptj = (int) $_POST['idptj'];
    $bahagian = strtoupper($_POST['bahagian']);
    $unit = trim($_POST['unit'] ?? '');

    if ($id <= 0 || $idptj <= 0 || $bahagian === '') {
        throw new Exception('Data tidak sah');
    }

    // Check if record exists
    $check_exists = $pdo->prepare(
        "SELECT COUNT(*) FROM tbahagian WHERE id = ?"
    );
    $check_exists->execute([$id]);

    if ($check_exists->fetchColumn() == 0) {
        throw new Exception('Rekod tidak dijumpai');
    }

    // Duplicate check
    $check_sql = "
        SELECT COUNT(*) 
        FROM tbahagian 
        WHERE idptj = ? AND bahagian = ? AND id != ?
    ";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$idptj, $bahagian, $id]);

    if ($check_stmt->fetchColumn() > 0) {
        throw new Exception('Bahagian ini telah wujud untuk PTJ ini');
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Update bahagian
    $update_sql = "
        UPDATE tbahagian 
        SET idptj = ?, bahagian = ?
        WHERE id = ?
    ";
    $stmt = $pdo->prepare($update_sql);

    if (!$stmt->execute([$idptj, $bahagian, $id])) {
        throw new Exception('Gagal mengemaskini bahagian');
    }

    // Update / insert unit
    if ($unit !== '') {
        $check_unit = $pdo->prepare(
            "SELECT COUNT(*) FROM tunit WHERE idbahagian = ?"
        );
        $check_unit->execute([$id]);
        $unit_exists = $check_unit->fetchColumn();

        if ($unit_exists) {
            $unit_sql = "
                UPDATE tunit 
                SET unit = ?
                WHERE idbahagian = ?
            ";
            $unit_stmt = $pdo->prepare($unit_sql);
            if (!$unit_stmt->execute([$unit, $id])) {
                throw new Exception('Gagal mengemaskini unit');
            }
        } else {
            $unit_sql = "
                INSERT INTO tunit (idbahagian, unit)
                VALUES (?, ?)
            ";
            $unit_stmt = $pdo->prepare($unit_sql);
            if (!$unit_stmt->execute([$id, $unit])) {
                throw new Exception('Gagal menambah unit');
            }
        }
    }

    $pdo->commit();

    $response['success'] = true;
    $response['message'] = 'Bahagian berjaya dikemaskini';

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
