<?php 
// include 'config.php';

// if (isset($_GET['jenis_id'])) {
//     $jenis_id = $_GET['jenis_id'];

//     $sql = "SELECT no_plat, modeltemp FROM tkenderaan WHERE idjenis = '$jenis_id'";
//     $result = $conn->query($sql);

//     echo "<option value=''>Belum ditentukan</option>";

//     while ($row = $result->fetch_assoc()) {
//         echo "<option value='{$row['no_plat']}' data-model='{$row['modeltemp']}'>
//                 {$row['no_plat']}
//               </option>";
//     }
// }

include 'config.php';

if (isset($_GET['jenis_id'])) {
    $jenis_id = $_GET['jenis_id'];

    $sql = "SELECT id, no_plat, pengeluar, model FROM kenderaan_jabatan WHERE id_jenis = '$jenis_id'";
    $result = $conn->query($sql);

    echo "<option value=''>Belum ditentukan</option>";

   while ($row = $result->fetch_assoc()) {
    $model = htmlspecialchars($row['pengeluar'] . ' ' . $row['model']);
    $no_plat = htmlspecialchars($row['no_plat'] . ' - ' . $row['pengeluar'] . ' ' . $row['model']) ;
    $id = htmlspecialchars($row['id']);

    echo "<option value='$id' data-model='$model'>$no_plat</option>";
}
}
?>