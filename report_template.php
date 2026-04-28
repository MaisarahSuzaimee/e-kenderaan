<?php
require 'config.php';

$result = $conn->query("SELECT * FROM tempahan_kenderaan");
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

<h2>LAPORAN TEMPAHAN KENDERAAN</h2>

<table>
    <tr>
        <th>Bil</th>
        <th>Pemohon</th>
        <th>Destinasi</th>
        <th>Tarikh</th>
    </tr>

    <?php $bil = 1; ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $bil++ ?></td>
        <td><?= $row['pemohon'] ?></td>
        <td><?= $row['destinasi'] ?></td>
        <td><?= $row['tarikh'] ?></td>
    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>