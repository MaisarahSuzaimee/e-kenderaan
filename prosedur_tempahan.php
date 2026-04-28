<?php
session_start();
require 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// if ($_SESSION['role'] !== 'staff') {
//     header("Location: error.html");
//     exit();
// }

if (!in_array($_SESSION['role'], ['staff', 'penyelaras_bahagian'])) {
    header("Location: error.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="1024">
    <title>Sistem Tempahan Kenderaan | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK2.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

</head>

<body>
    <?=
        include 'dashboard2.php'
        ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header"  style="margin-top: -26px;"  >
                <h1 class="welcome-text">Prosedur Tempahan</h1>
                <div class="left-actions">
                    
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>
            <div class="section-procedure">
                <div class="procedure-card">
                    <h3>Prosedur Tempahan Kenderaan</h3>
                    <ol>
                        <li>Isi borang permohonan tempahan.</li>
                        <li>Semakan oleh admin.</li>
                        <li>Kelulusan / penolakan tempahan.</li>
                        <li>Penjadualan kenderaan & pemandu.</li>
                        <li>Pelaksanaan perjalanan.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</body>

</html>