<?php
require 'config.php';

if (isset($_POST['id']) && isset($_POST['status'])) {

    $id = $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tpemandu SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "Berjaya";
    } else {
        echo "Gagal";
    }
}
?>