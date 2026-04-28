<?php
require_once 'config.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = ['success' => false, 'data' => []];

try {
    switch ($action) {
        case 'getPTJ':
            $stmt = $pdo->query("SELECT id, namaptj FROM tptj ORDER BY namaptj");
            $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['success'] = true;
            break;
            
        case 'getBahagian':
            if (isset($_GET['idptj']) && !empty($_GET['idptj'])) {
                $idptj = (int)$_GET['idptj'];
                $stmt = $pdo->prepare("SELECT id, bahagian FROM tbahagian WHERE idptj = ? ORDER BY bahagian");
                $stmt->execute([$idptj]);
                $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
            }
            break;
            
        case 'getUnit':
            if (isset($_GET['idbahagian']) && !empty($_GET['idbahagian'])) {
                $idbahagian = (int)$_GET['idbahagian'];
                $stmt = $pdo->prepare("SELECT id, unit FROM tunit WHERE idbahagian = ? ORDER BY unit");
                $stmt->execute([$idbahagian]);
                $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
            }
            break;
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>

