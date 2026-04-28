<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check for ID=0 records and fix if found
    $check_zero = $pdo->query("SELECT COUNT(*) FROM tpemandu WHERE id = 0");
    if ($check_zero->fetchColumn() > 0) {
        // Get max ID
        $max_id = $pdo->query("SELECT MAX(id) FROM tpemandu WHERE id > 0")->fetchColumn();
        $new_id = $max_id + 1;

        // Update the zero ID record
        $update = $pdo->prepare("UPDATE tpemandu SET id = ? WHERE id = 0");
        $update->execute([$new_id]);

        // Reset auto_increment
        $pdo->exec("ALTER TABLE tpemandu AUTO_INCREMENT = " . ($new_id + 1));
    }

    // Get form data
    $namapemandu = isset($_POST['nama_pemandu']) ? trim($_POST['nama_pemandu']) : '';
    $nokp = isset($_POST['no_kp']) ? trim($_POST['no_kp']) : '';
    $idjawatan = isset($_POST['jawatan']) ? (int)$_POST['jawatan'] : 0;
    $idgred = isset($_POST['gred']) ? (int)$_POST['gred'] : 0;
    $idptj = isset($_POST['ptj']) ? (int)$_POST['ptj'] : 0;
    $idbahagian = isset($_POST['bahagian']) ? (int)$_POST['bahagian'] : 0;
    $idunit = isset($_POST['unit']) && !empty($_POST['unit']) ? (int)$_POST['unit'] : null;
    $notelefon = isset($_POST['no_telefon']) ? trim($_POST['no_telefon']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'Aktif';
    $catatan = isset($_POST['catatan']) && !empty(trim($_POST['catatan'])) ? trim($_POST['catatan']) : null;
    // Validate required fields
    if (empty($namapemandu) || empty($nokp) || $idjawatan <= 0 || $idgred <= 0 || $idptj <= 0 || $idbahagian <= 0) {
        throw new Exception('Sila isi semua maklumat yang diperlukan');
    }

    // Check if nokp already exists
    $check_sql = "SELECT COUNT(*) FROM tpemandu WHERE nokp = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$nokp]);

    if ($check_stmt->fetchColumn() > 0) {
        throw new Exception('No. KP ini telah wujud dalam sistem');
    }

    // Force explicit ID assignment to avoid zero ID issue
    $max_id_query = $pdo->query("SELECT COALESCE(MAX(id), 0) FROM tpemandu");
    $max_id = $max_id_query->fetchColumn();
    $next_id = $max_id + 1;

    // Insert with explicit ID
    $sql = "INSERT INTO tpemandu (id, namapemandu, nokp, idjawatan, idgred, idptj, idbahagian, idunit, notelefon, status, catatan) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $next_id,
        $namapemandu,
        $nokp,
        $idjawatan,
        $idgred,
        $idptj,
        $idbahagian,
        $idunit,
        $notelefon,
        $status,
        $catatan
    ]);

    if (!$result) {
        throw new Exception('Gagal menambah rekod: ' . implode(", ", $stmt->errorInfo()));
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pemandu baru telah ditambah!',
        'id' => $next_id
    ]);
} catch (Exception $e) {
    // Rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Error in add_pemandu.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
