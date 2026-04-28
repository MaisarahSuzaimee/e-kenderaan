<?php
// require 'config.php';

// if (isset($_POST['id'])) {
//     $id = $_POST['id'];

//     $sql = "DELETE FROM tpenempatan WHERE id = ?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("i", $id);

//     if ($stmt->execute()) {
//         header("Location: Penempatan_pemandu.php");
//         exit();
//     } else {
//         echo "Error deleting record: " . $conn->error;
//     }
// }
?>

<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('ID tidak sah');
    }

    $id = (int)$_POST['id'];

    // Delete the record directly from tjenis table
    // Remove the dependency check since tkenderaan table doesn't exist
    $sql = "DELETE FROM tpenempatan WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if (!$stmt->execute([$id])) {
        throw new Exception('Gagal memadam rekod');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Rekod berjaya dipadam'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
