<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $response = [
        'success' => true,
        'jawatan' => [],
        'gred' => [],
        'ptj' => [],
        'bahagian' => [],
        'unit' => []
    ];

    // Fetch Jawatan
    $stmt = $conn->query("SELECT id, desc_jawatan FROM jawatans ORDER BY desc_jawatan");
    $response['jawatan'] = $stmt->fetch_all(MYSQLI_ASSOC);

    // Fetch Gred
    $stmt = $conn->query("SELECT id, kod_gred FROM greds ORDER BY kod_gred");
    $response['gred'] = $stmt->fetch_all(MYSQLI_ASSOC);

    // Fetch PTJ
    $stmt = $conn->query("SELECT id, nama_ptj FROM ptjs ORDER BY nama_ptj");
    $response['ptj'] = $stmt->fetch_all(MYSQLI_ASSOC);

    // Fetch Bahagian
    $stmt = $conn->query("SELECT id, bahagian FROM tbahagian ORDER BY bahagian");
    $response['bahagian'] = $stmt->fetch_all(MYSQLI_ASSOC);

    // Fetch Unit
    $stmt = $conn->query("SELECT id, unit FROM tunit ORDER BY unit");
    $response['unit'] = $stmt->fetch_all(MYSQLI_ASSOC);

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>