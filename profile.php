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

if (!in_array($_SESSION['role'], ['staff', 'admin', 'superadmin', 'penyelaras_bahagian'])) {
    header("Location: error.html");
    exit();
}
function getDetails($conn)
{
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT 
                                    p.*, tp.nama_ptj as ptj, tb.bahagian as bahagian, tu.unit as unit, tg.kod_gred as gred, tj.desc_jawatan AS namaJawatan
                                FROM 
                                    penggunajkn p
                                left join 
                                    ptjs tp
                                    on p.idptj = tp.id
                                left join 
                                    tbahagian tb
                                    on p.bahagian = tb.id
                                left join 
                                    tunit tu
                                    on p.unit = tu.id
                                left join
                                    greds tg 
                                    on p.gred = tg.id
                                left JOIN 
                                	jawatans tj
                                	on p.jawatan = tj.id 
                                WHERE
                                    p.id = ?"
        );
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}

$user = getDetails($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024">
    <title>Sistem Tempahan Kenderaan | Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK2.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <?=
        include("dashboard2.php");
    ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Profile Pengguna</h1>
                <div class="left-actions">
                    <?php if ($_SESSION['role'] == 'staff') { ?>
                        <a href="tukar_pass_user.php" class="btn btn-primary">
                            <i class="fas fa-key"></i> Tukar Kata Laluan
                        </a>
                    <?php } else if ($_SESSION['role'] == 'admin') { ?>
                            <a href="tukar_pass_admin.php" class="btn btn-primary">
                                <i class="fas fa-key"></i> Tukar Kata Laluan
                            </a>
                    <?php } ?>

                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>

            <div class="profile-card">

                <div class="profile-group">

                    <div class="profile-row">
                        <span class="profile-label">Nama</span>
                        <span class="profile-value"><?= $user['nama'] ?? '-' ?></span>
                    </div>

                    <div class="profile-row">
                        <span class="profile-label">No. KP</span>
                        <span class="profile-value"><?= $user['nokp'] ?? '-' ?></span>
                    </div>

                    <div class="profile-row">
                        <span class="profile-label">Email</span>
                        <span class="profile-value"><?= $user['email'] ?? '-' ?></span>
                    </div>

                    <div class="profile-row">
                        <span class="profile-label">No. HP</span>
                        <span class="profile-value"><?= $user['nohp'] ?? '-' ?></span>
                    </div>

                    <div class="profile-row">
                        <span class="profile-label">Jawatan</span>
                        <span class="profile-value"><?= $user['namaJawatan'] ?? '-' ?></span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Gred</span>
                        <span class="profile-value"><?= $user['gred'] ?? '-' ?></span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">PTJ</span>
                        <span class="profile-value"><?= $user['ptj'] ?? '-' ?></span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Bahagian</span>
                        <span class="profile-value"><?= $user['bahagian'] ?? '-' ?></span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Unit</span>
                        <span class="profile-value"><?= $user['unit'] ?? '-' ?></span>
                    </div>
                    <div class="profile-row">
                        <span class="profile-label">Peranan</span>
                        <?php if ($user['role'] == 'staff') { ?>
                            <span class="profile-value">Staff</span>
                        <?php } else if ($user['role'] == 'admin') { ?>

                        <?php } else if ($user['role'] == 'penyelaras_bahagian') { ?>
                                    <span class="profile-value">Penyelaras Bahagian</span>

                        <?php } else if ($user['role'] == 'superadmin') { ?>
                                        <span class="profile-value">Superadmin</span>

                        <?php }
                        ?>
                    </div>

                </div>

            </div>

        </div>
    </div>
</body>

</html>