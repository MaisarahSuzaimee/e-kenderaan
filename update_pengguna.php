 <?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {

        $id = $_POST['id'];
        $namapengguna = $_POST['nama'];
        $nokp = $_POST['nokp'];
        $email = $_POST['email'];
        $bahagian = $_POST['idptj'] ?: null;
        $unit = $_POST['bahagian'] ?: null;
        $subunit = $_POST['unit'] ?: null;
        $jawatan = $_POST['jawatan'] ?: null;
        $gred = $_POST['gred'] ?: null;
        $nohp = $_POST['nohp'];
        // $password = $_POST['password'];
        $status = $_POST['status'];
        $peranan1 = $_POST['peranan'];
        // $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        if ($peranan1 === 'Admin') {
            $peranan = 'admin';
        } else {
            $peranan = $peranan1;
        }
        // Check duplicate nokp
        $check_sql = "SELECT id FROM penggunajkn WHERE nokp = ? AND id != ?";
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


            $sql = "UPDATE penggunajkn SET 
                    nama = ?,
                    nokp = ?,
                    email = ?,
                    idptj = ?,
                    bahagian = ?,
                    unit = ?,
                    jawatan = ?,
                    gred = ?,
                    nohp = ?,
                    role = ?,
                    status = ?
                    WHERE id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssssi",
                $namapengguna,
                $nokp,
                $email,
                $bahagian,
                $unit,
                $subunit,
                $jawatan,
                $gred,
                $nohp,
                $peranan,
                $status,
                $id
            );
        

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Pengguna berjaya dikemaskini!'
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
?>

