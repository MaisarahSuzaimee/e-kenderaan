<?php
session_start();
require 'config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total records for pagination
$sql_count = "SELECT COUNT(*) AS total FROM tempahan_kenderaan";
$result_count = $conn->query($sql_count);
$total_rows = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Initialize search variables
$search_pemohon = isset($_POST['pemohon']) ? $_POST['pemohon'] : '';
$search_destinasi = isset($_POST['destinasi']) ? $_POST['destinasi'] : '';
$search_kelulusan = isset($_POST['kelulusan']) ? $_POST['kelulusan'] : '';

// Check if search form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchParams = array(
        'id' => isset($_POST['id']) ? $_POST['id'] : '',
        'pemohon' => $search_pemohon,
        'destinasi' => $search_destinasi,
        'kelulusan' => $search_kelulusan,
        'tarikh' => isset($_POST['tarikh']) ? $_POST['tarikh'] : '',
        'pemandu' => isset($_POST['pemandu']) ? $_POST['pemandu'] : '',
        'kenderaan' => isset($_POST['kenderaan']) ? $_POST['kenderaan'] : ''
    );

    $result = searchRecords($conn, $searchParams);
} else {
    // Default query with pagination
    $sql = "SELECT tk.*, tp.id as pemandu_id, tp.namapemandu 
            FROM tempahan_kenderaan tk 
            LEFT JOIN tpemandu tp ON tk.id_pemandu = tp.id 
            ORDER BY tk.id DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $items_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Make sure the searchRecords function includes pagination
function searchRecords($conn, $searchParams)
{
    $query = "SELECT tk.*, tp.id as pemandu_id, tp.namapemandu 
              FROM tempahan_kenderaan tk 
              LEFT JOIN tpemandu tp ON tk.id_pemandu = tp.id 
              WHERE 1=1";
    $values = array();
    $types = "";

    if (!empty($searchParams['id'])) {
        $query .= " AND tk.id = ?";
        $values[] = $searchParams['id'];
        $types .= "i";
    }

    if (!empty($searchParams['pemohon'])) {
        $query .= " AND tk.pemohon LIKE ?";
        $values[] = "%" . $searchParams['pemohon'] . "%";
        $types .= "s";
    }

    if (!empty($searchParams['tarikh'])) {
        $query .= " AND tk.tarikh = ?";
        $values[] = $searchParams['tarikh'];
        $types .= "s";
    }

    if (!empty($searchParams['destinasi'])) {
        $query .= " AND tk.destinasi LIKE ?";
        $values[] = "%" . $searchParams['destinasi'] . "%";
        $types .= "s";
    }

    if (!empty($searchParams['kelulusan'])) {
        $query .= " AND tk.kelulusan = ?";
        $values[] = $searchParams['kelulusan'];
        $types .= "s";
    }

    if (!empty($searchParams['pemandu'])) {
        $query .= " AND tp.namapemandu LIKE ?";
        $values[] = "%" . $searchParams['pemandu'] . "%";
        $types .= "s";
    }

    if (!empty($searchParams['kenderaan'])) {
        $query .= " AND tk.kenderaan LIKE ?";
        $values[] = "%" . $searchParams['kenderaan'] . "%";
        $types .= "s";
    }

    $query .= " ORDER BY tk.id DESC";

    $stmt = $conn->prepare($query);
    if (!empty($values)) {
        $stmt->bind_param($types, ...$values);
    }
    $stmt->execute();

    return $stmt->get_result();
}

// Get unread notifications count
$notifCountQuery = "SELECT COUNT(*) as count FROM notifications WHERE is_read_superadmin = 0";
$notifCountResult = $conn->query($notifCountQuery);
$unreadCount = $notifCountResult->fetch_assoc()['count'];

// Get recent notifications
$notifQuery = "SELECT n.*, t.pemohon, t.destinasi 
               FROM notifications n 
               JOIN tempahan_kenderaan t ON n.booking_id = t.id 
               WHERE n.is_read_superadmin = 0 
               ORDER BY n.created_at DESC 
               LIMIT 5";
$notifications = $conn->query($notifQuery);

// Mark notifications as read if requested
// if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
//     $notifId = (int) $_POST['notification_id'];
//     $markReadQuery = "UPDATE notifications SET is_read_superadmin = 1 WHERE id = ?";
//     $stmt = $conn->prepare($markReadQuery);
//     $stmt->bind_param("i", $notifId);
//     $stmt->execute();

//     // Redirect supaya elak resubmit & refresh count
//     header("Location: " . $_SERVER['PHP_SELF']);
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ 1. MARK NOTIFICATION AS READ
    if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
        $notifId = (int) $_POST['notification_id'];

        $stmt = $conn->prepare("UPDATE notifications SET is_read_superadmin = 1 WHERE id = ?");
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
// Get driver name function
function getDriverName($conn, $id)
{
    if (!$id)
        return 'Belum ditentukan';

    $sql = "SELECT namapemandu FROM tpemandu WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return htmlspecialchars($row['namapemandu']);
    }
    return 'Belum ditentukan';
}


$procedure = "";

$result = $conn->query("SELECT content FROM prosedur_tempahan LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $procedure = $row['content'];
}

// Get system statistics
$statsQuery = [
    "total_bookings" => "SELECT COUNT(*) as count FROM tempahan_kenderaan",
    "pending_bookings" => "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'BARU' OR kelulusan = 'KIV'",
    "approved_bookings" => "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'LULUS'",
    "rejected_bookings" => "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'TIDAK LULUS'",
    "total_drivers" => "SELECT COUNT(*) as count FROM tpemandu WHERE status = 'Aktif'",
    "total_vehicles" => "SELECT COUNT(*) as count FROM tkenderaan",
    "total_users" => "SELECT COUNT(*) as count FROM penggunajkn"
];

$stats = [];
foreach ($statsQuery as $key => $query) {
    $result = $conn->query($query);
    $stats[$key] = $result->fetch_assoc()['count'];
}

// At the top of the file, add this to track if we're searching
$isSearching = ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    (!empty($_POST['id']) ||
        !empty($_POST['pemohon']) ||
        !empty($_POST['destinasi']) ||
        !empty($_POST['kelulusan']) ||
        !empty($_POST['tarikh'])));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closeModal('popup')">&times;</span>

            <h3>Prosedur Tempahan Kenderaan</h3>
            <?= $procedure ? $procedure : "<p>Tiada prosedur ditetapkan.</p>" ?>

        </div>
    </div>
    <?php include 'dashboard2.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?> (Superadmin)
                </h1>
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


                </div>

            </div>
            <h3 class="section-title">Statistik Sistem</h3>

            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-calendar-check"></i>
                        Jumlah Tempahan
                    </div>
                    <div class="stat-amount"><?= $stats['total_bookings'] ?></div>
                    <div class="stat-change">
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-clock"></i>
                        Tempahan Menunggu
                    </div>
                    <div class="stat-amount"><?= $stats['pending_bookings'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-check-circle"></i>
                        Tempahan Diluluskan
                    </div>
                    <div class="stat-amount"><?= $stats['approved_bookings'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-times-circle"></i>
                        Tempahan Tidak Lulus
                    </div>
                    <div class="stat-amount"><?= $stats['rejected_bookings'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-car"></i>
                        Jumlah Kenderaan
                    </div>
                    <div class="stat-amount"><?= $stats['total_vehicles'] ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-user-tie"></i>
                        Jumlah Pemandu
                    </div>
                    <div class="stat-amount"><?= $stats['total_drivers'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">
                        <i class="fas fa-users"></i>
                        Jumlah Pengguna
                    </div>
                    <div class="stat-amount"><?= $stats['total_users'] ?></div>
                </div>
            </div>
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
                        <button type="button" class="btn btn-edit" onclick="openModal('editModal')">
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

        <h3 class="section-title">Tindakan Pantas</h3>
        <div class="dashboard-grid">

            <a href="pengguna_superadmin.php" class="stat-card action-btn" style="text-decoration: none; color: black;">
                <i class="fas fa-users"></i>
                <span>Senarai Pengguna</span>
            </a>
            <a href="pengguna_superadmin.php?open=admin" return false;" class="stat-card action-btn"
                style="text-decoration: none; color: black;">
                <i class="fas fa-user-plus"></i>
                <span>Daftar Admin</span>
            </a>
            <a href="pengguna_superadmin.php?open=user" class="stat-card action-btn"
                style="text-decoration: none; color: black;">
                <i class="fas fa-user-edit"></i>
                <span>Daftar Pengguna</span>
            </a>
        </div>
    </div>
    </div>

    <div id="addPenggunaModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addPenggunaModal')">&times;</span>
                    <h2 class="form-title">Daftar Pengguna Baharu</h2>
                </div>

                <p class="form-subtitle">Sila Gunakan No KP sebagai Login ID</p>

                <form id="addPenggunaForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="nama">Nama <span>*</span></label>
                        <input type="text" id="nama" name="nama" required placeholder="Masukkan nama penuh"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nokp">No KP <span>*</span></label>
                            <input type="text" id="nokp" name="nokp" required placeholder="Contoh: 901230012345">
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span>*</span></label>
                            <input type="email" id="email" name="email" required placeholder="Contoh: nama@moh.gov.my">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nohp">No HP <span>*</span></label>
                            <input type="tel" id="nohp" name="nohp" required placeholder="Contoh: 0123456789">
                        </div>
                        <div class="form-group">
                            <label for="jawatan">Jawatan</label>
                            <input type="text" id="jawatan" name="jawatan" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="idptj">Ptj</label>
                            <select id="idptj" name="idptj" onchange="loadBahagian()">
                                <option value="">-- Sila Pilih --</option>
                                <?php
                                $ptjQuery = $pdo->query("SELECT id, namaptj FROM tptj ORDER BY namaptj");
                                while ($ptj = $ptjQuery->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value=\"{$ptj['id']}\">{$ptj['namaptj']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bahagian">Bahagian</label>
                            <select id="bahagian" name="bahagian" onchange="loadUnit()">
                                <option value="">-- Sila Pilih --</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="unit">Unit</label>
                            <select id="unit" name="unit">
                                <option value="">-- Sila Pilih --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gred">Gred</label>
                            <select id="gred" name="gred">
                                <option value="">-- Sila Pilih --</option>
                                <?php
                                // Fetch Gred from database
                                $gredQuery = $pdo->query("SELECT id, gred FROM tgred ORDER BY gred");
                                while ($gredItem = $gredQuery->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value=\"{$gredItem['id']}\">{$gredItem['gred']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="peranan">Peranan <span>*</span></label>
                            <input type="text" id="peranan" name="peranan" value="Staff" readonly>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span>*</span></label>
                            <select id="status" name="status" required>
                                <option value="AKTIF">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Kata Laluan <span>*</span></label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password">
                            <button type="button" class="toggle-password" onclick="togglePassword2()">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="registerBtn" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Daftar
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="handleCancel()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="addAdminModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addAdminModal')">&times;</span>
                    <h2 class="form-title">Daftar Admin Baharu</h2>
                </div>

                <p class="form-subtitle">Sila Gunakan No KP sebagai Login ID</p>

                <form id="addPenggunaForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="nama">Nama <span>*</span></label>
                        <input type="text" id="nama" name="nama" required placeholder="Masukkan nama penuh"
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nokp">No KP <span>*</span></label>
                            <input type="text" id="nokp" name="nokp" required placeholder="Contoh: 901230012345">
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span>*</span></label>
                            <input type="email" id="email" name="email" required placeholder="Contoh: nama@moh.gov.my">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nohp">No HP <span>*</span></label>
                            <input type="tel" id="nohp" name="nohp" required placeholder="Contoh: 0123456789">
                        </div>
                        <div class="form-group">
                            <label for="jawatan">Jawatan</label>
                            <input type="text" id="jawatan" name="jawatan" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="idptj">Ptj</label>
                            <select id="idptj" name="idptj" onchange="loadBahagian()">
                                <option value="">-- Sila Pilih --</option>
                                <?php
                                $ptjQuery = $pdo->query("SELECT id, namaptj FROM tptj ORDER BY namaptj");
                                while ($ptj = $ptjQuery->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value=\"{$ptj['id']}\">{$ptj['namaptj']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bahagian">Bahagian</label>
                            <select id="bahagian" name="bahagian" onchange="loadUnit()">
                                <option value="">-- Sila Pilih --</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="unit">Unit</label>
                            <select id="unit" name="unit">
                                <option value="">-- Sila Pilih --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gred">Gred</label>
                            <select id="gred" name="gred">
                                <option value="">-- Sila Pilih --</option>
                                <?php
                                // Fetch Gred from database
                                $gredQuery = $pdo->query("SELECT id, gred FROM tgred ORDER BY gred");
                                while ($gredItem = $gredQuery->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value=\"{$gredItem['id']}\">{$gredItem['gred']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="peranan">Peranan <span>*</span></label>
                            <input type="text" id="peranan" name="peranan" value="Admin" readonly>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span>*</span></label>
                            <select id="status" name="status" required>
                                <option value="AKTIF">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Kata Laluan <span>*</span></label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password">
                            <button type="button" class="toggle-password" onclick="togglePassword2()">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="registerBtn" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Daftar
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="handleCancel2()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal2">
                <div class="modal-content2">
                    <div class="container">
                        <div class="modal-header">
                            <span class="close" onclick="closeModal('editModal')">&times;</span>
                            <h2 class="form-title">Edit Prosedur Tempahan</h2>
                        </div>

                        <form method="POST" action="" id="editForm">

                            <textarea name="procedure" id="procedureEditor">
    <?= $procedure ?>
</textarea>

                            <div class="button-group" style="margin-top:10px;">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                 <button type="button" class="btn btn-cancel" onclick="closeModal('editModal')">Batal</button>
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

        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        // Function to close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        function handleCancel() {
            const nama = document.getElementById('nama').value.trim();
            const nokp = document.getElementById('nokp').value.trim();
            const email = document.getElementById('email').value.trim();
            const bahagian = document.getElementById('idptj').value.trim();
            const unit = document.getElementById('bahagian').value.trim();
            const sub_unit = document.getElementById('unit').value.trim();
            const gred = document.getElementById('gred').value.trim();
            const jawatan = document.getElementById('jawatan').value.trim();
            const nohp = document.getElementById('nohp').value.trim();
            const password = document.getElementById('password').value.trim();

            const modal = document.getElementById('addPenggunaModal');
            const form = document.getElementById('addPenggunaForm');

            if (nama === '' && nokp === '' && email === '' && bahagian === '' && unit === '' && sub_unit === '' &&
                gred === '' && jawatan === '' && nohp === '' && password === '') {
                if (modal) {
                    modal.style.display = 'none';
                }
                return;
            }

            let hasValue = false;


            Swal.fire({
                title: 'Anda pasti?',
                text: "Semua maklumat yang diisi akan dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, batalkan!',
                cancelButtonText: 'Tidak',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Clear all form inputs
                    const form = document.getElementById('addPenggunaForm');
                    form.reset();

                    const modal = document.getElementById('addPenggunaModal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    // Show success message, THEN redirect
                    Swal.fire({
                        title: 'Dibatalkan!',
                        text: 'Borang telah dikosongkan.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        // timer: 4000
                    }).then(() => {
                        window.location.href = 'pengguna_superadmin.php';
                    });
                }
            });
        }

        function handleCancel2() {
            const nama = document.getElementById('nama').value.trim();
            const nokp = document.getElementById('nokp').value.trim();
            const email = document.getElementById('email').value.trim();
            const bahagian = document.getElementById('idptj').value.trim();
            const unit = document.getElementById('bahagian').value.trim();
            const sub_unit = document.getElementById('unit').value.trim();
            const gred = document.getElementById('gred').value.trim();
            const jawatan = document.getElementById('jawatan').value.trim();
            const nohp = document.getElementById('nohp').value.trim();
            const password = document.getElementById('password').value.trim();

            const modal = document.getElementById('addAdminModal');
            const form = document.getElementById('addAdminForm');

            if (nama === '' && nokp === '' && email === '' && bahagian === '' && unit === '' && sub_unit === '' &&
                gred === '' && jawatan === '' && nohp === '' && password === '') {
                if (modal) {
                    modal.style.display = 'none';
                }
                return;
            }

            let hasValue = false;


            Swal.fire({
                title: 'Anda pasti?',
                text: "Semua maklumat yang diisi akan dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, batalkan!',
                cancelButtonText: 'Tidak',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Clear all form inputs
                    const form = document.getElementById('addAdminForm');
                    form.reset();

                    const modal = document.getElementById('addAdminModal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    // Show success message, THEN redirect
                    Swal.fire({
                        title: 'Dibatalkan!',
                        text: 'Borang telah dikosongkan.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        // timer: 4000
                    }).then(() => {
                        window.location.href = 'pengguna_superadmin.php';
                    });
                }
            });
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