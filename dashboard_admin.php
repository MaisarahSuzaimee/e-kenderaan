<?php
session_start();
require 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: error.html");
    exit();
}



$showPopup = false;

if (isset($_SESSION['show_popup'])) {
    $showPopup = true;
    unset($_SESSION['show_popup']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ 1. MARK NOTIFICATION AS READ
    if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
        $notifId = (int) $_POST['notification_id'];

        $stmt = $conn->prepare("UPDATE notifications SET is_read_admin = 1 WHERE id = ?");
        $stmt->bind_param("i", $notifId);
        $stmt->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // ✅ 2. SAVE PROCEDURE
    if (isset($_POST['procedure'])) {

        $content = $_POST['procedure'];
        $content = $conn->real_escape_string($content);

        $check = "SELECT id FROM prosedur_tempahan LIMIT 1";
        $result = $conn->query($check);

        if ($result && $result->num_rows > 0) {
            $sql = "UPDATE prosedur_tempahan 
                    SET content='$content' 
                    WHERE id = (SELECT id FROM (SELECT id FROM prosedur_tempahan LIMIT 1) as t)";
        } else {
            $sql = "INSERT INTO prosedur_tempahan (content) VALUES ('$content')";
        }

        if ($conn->query($sql)) {
            echo "<script>
                alert('Prosedur berjaya disimpan!');
                window.location.href = window.location.href;
            </script>";
        } else {
            echo "<script>alert('Gagal simpan!');</script>";
        }
    }
}
// Get unread notifications count
$notifCountQuery = "SELECT COUNT(*) as count FROM notifications WHERE is_read_admin = 0";
$notifCountResult = $conn->query($notifCountQuery);
$unreadCount = $notifCountResult->fetch_assoc()['count'];

// Get recent notifications
$notifQuery = "SELECT n.*, t.pemohon, t.destinasi 
               FROM notifications n 
               JOIN tempahan_kenderaan t ON n.booking_id = t.id 
               WHERE n.is_read_admin = 0 
               ORDER BY n.created_at DESC 
               LIMIT 5";
$notifications = $conn->query($notifQuery);

$sql_count = "SELECT COUNT(*) AS total FROM tempahan_kenderaan";
$result_count = $conn->query($sql_count);
$total_rows = $result_count->fetch_assoc()['total'];

$baruCountQuery = "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'BARU'";
$baruCountResult = $conn->query($baruCountQuery);
$baruCount = $baruCountResult->fetch_assoc()['count'];

$lulusCountQuery = "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'LULUS'";
$lulusCountResult = $conn->query($lulusCountQuery);
$lulusCount = $lulusCountResult->fetch_assoc()['count'];

$tidakLulusCountQuery = "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'TIDAK LULUS'";
$tidakLulusCountResult = $conn->query($tidakLulusCountQuery);
$tidakLulusCount = $tidakLulusCountResult->fetch_assoc()['count'];

$pemanduCountQuery = "SELECT COUNT(*) as count FROM tpemandu";
$pemanduCountResult = $conn->query($pemanduCountQuery);
$pemanduCount = $pemanduCountResult->fetch_assoc()['count'];

$pemanduCutiCountQuery = "SELECT COUNT(*) AS count
                            FROM pemandu_leave
                            WHERE CURRENT_DATE() BETWEEN start_date AND end_date;";
$pemanduCutiCountResult = $conn->query($pemanduCutiCountQuery);
$pemanduCutiCount = $pemanduCutiCountResult->fetch_assoc()['count'];

$kenderaanCountQuery = "SELECT COUNT(*) as count FROM kenderaan_jabatan";
$kenderaanCountResult = $conn->query($kenderaanCountQuery);
$kenderaanCount = $kenderaanCountResult->fetch_assoc()['count'];

$kenderaanBertugasCountQuery = "SELECT COUNT(id_kenderaan) AS count
FROM tempahan_kenderaan 
WHERE CURRENT_DATE() BETWEEN tarikh_pergi  AND tarikh_balik";
$kenderaanBertugasCountResult = $conn->query($kenderaanBertugasCountQuery);
$kenderaanBertugasCount = $kenderaanBertugasCountResult->fetch_assoc()['count'];


$procedure = "";

$result = $conn->query("SELECT content FROM prosedur_tempahan LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $procedure = $row['content'];
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
    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close-btn">&times;</span>

            <h3>Prosedur Tempahan Kenderaan</h3>
            <?= $procedure ? $procedure : "<p>Tiada prosedur ditetapkan.</p>" ?>

        </div>
    </div>
    <?=
        include 'dashboard2.php'
        ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?></h1>
                <div class="left-actions">
                    <div class="notification-container">
                        <div class="notification-wrapper" id="notificationWrapper">

                            <div class="notification-bell" onclick="toggleNotifications()">
                                <i class="fas fa-bell"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="notification-badge"><?= $unreadCount ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <h3>Notifikasi Baru</h3>
                                <?php if ($notifications->num_rows > 0): ?>
                                    <?php while ($notif = $notifications->fetch_assoc()): ?>
                                        <div class="notification-item">
                                            <div class="notification-content">
                                                <p><?= htmlspecialchars($notif['message']) ?></p>
                                                <small><?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?></small>
                                            </div>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                                                <button type="submit" name="mark_read" class="mark-read-btn">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="no-notifications">Tiada notifikasi baru</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>

            </div>
            <h3 class="section-title">Statistik Sistem</h3>

            <div class="dashboard-grid">

                <div class="stat-card">
                    <div class="stat-title">Jumlah Tempahan</div>
                    <div class="stat-amount"><?= $total_rows ?></div>
                    <div class="stat-change">
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Tempahan Baru</div>
                    <div class="stat-amount" style="color:blue;"><?= $baruCount ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Tempahan Lulus</div>
                    <div class="stat-amount" style="color:green;"><?= $lulusCount ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Tempahan Tidak Lulus</div>
                    <div class="stat-amount" style="color:red;"><?= $tidakLulusCount ?></div>
                </div>

                <!-- <div class="stat-card">
                    <div class="stat-title">Jumlah Pemandu</div>
                    <div class="stat-amount"><?= $pemanduCount ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Jumlah Pemandu Bercuti (Hari Ini)</div>
                    <div class="stat-amount"><?= $pemanduCutiCount ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Jumlah Kenderaan</div>
                    <div class="stat-amount"><?= $kenderaanCount ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">Jumlah Kenderaan Bertugas (Hari Ini)</div>
                    <div class="stat-amount"><?= $kenderaanBertugasCount ?></div>
                </div> -->

            </div>

            <h3 class="section-title">Prosedur Tempahan</h3>

            <div class="section-procedure">
                <div class="procedure-card">

                    <div class="procedure-header">
                        <h3>Prosedur Tempahan Kenderaan</h3>
                    </div>

                    <div class="procedure-content">
                        <?= $procedure ? $procedure : "<p>Tiada prosedur ditetapkan.</p>" ?>

                        <!-- onclick="document.getElementById('popup').style.display='flex'" -->
                        <div class="button-group">
                            <button type="button" class="btn btn-edit" onclick="openEditModal()">
                                <i class="fas fa-edit"></i>Edit Prosedur
                            </button>
                            <button type="button" class="btn btn-success"
                                onclick="document.getElementById('popup').style.display='flex'">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                        </div>


                    </div>
                </div>

            </div>

            <div id="editModal" class="modal2">
                <div class="modal-content2">
                    <div class="container">
                        <div class="modal-header">
                            <span class="close" onclick="closeEditModal()">&times;</span>
                            <h2 class="form-title">Edit Prosedur Tempahan</h2>
                        </div>

                        <form method="POST" action="" id="editForm">

                            <textarea name="procedure" id="procedureEditor">
    <?= $procedure ?>
</textarea>

                            <div class="button-group" style="margin-top:10px;">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>

                        </form>
                        <!-- <form method="POST" action="" id="editForm" onsubmit="return confirmSubmit()"> -->
                    </div>

                </div>
            </div>

            <script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>

            <script>
                function toggleNotifications() {
                    const dropdown = document.getElementById('notificationDropdown');
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';

                    document.addEventListener('click', function (event) {
                        const wrapper = document.getElementById('notificationWrapper');
                        const dropdown = document.getElementById('notificationDropdown');

                        // Kalau klik BUKAN dalam wrapper
                        if (!wrapper.contains(event.target)) {
                            dropdown.style.display = 'none';
                        }
                    });
                }

                document.addEventListener("DOMContentLoaded", function () {

                    <?php if ($showPopup): ?>
                        document.getElementById("popup").style.display = "flex";
                    <?php endif; ?>

                    const closeBtn = document.querySelector(".close-btn");
                    if (closeBtn) {
                        closeBtn.onclick = function () {
                            document.getElementById("popup").style.display = "none";
                        };
                    }

                });

                function openEditModal() {
                    document.getElementById('editModal').style.display = 'flex';
                }

                function closeEditModal() {
                    document.getElementById('editModal').style.display = 'none';
                }

                let editorInstance;

                ClassicEditor
                    .create(document.querySelector('#procedureEditor'))
                    .then(editor => {
                        editorInstance = editor;
                    })
                    .catch(error => {
                        console.error(error);
                    });

                document.getElementById('editForm').addEventListener('submit', function () {
                    if (editorInstance) {
                        document.querySelector('#procedureEditor').value = editorInstance.getData();
                    }
                });
            </script>

</body>

</html>