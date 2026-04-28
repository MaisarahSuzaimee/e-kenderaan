<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();

// if (!isset($_SESSION['logged_in']) || 
//     !in_array($_SESSION['role'], ['staff'])) {

//     header("Location: error.html");
//     exit();
// }

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'staff') {
    header("Location: error.html");
    exit();
}

require 'config.php';


$showPopup = false;

if (isset($_SESSION['show_popup'])) {
    $showPopup = true;
    unset($_SESSION['show_popup']);
}

function getAdminData($conn)
{
    $stmt = $conn->prepare("SELECT nama, email FROM penggunajkn WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}
$admins = getAdminData($conn);
// $adminData = getAdminData($conn);
// $adminEmail = $adminData['email'];
$nama = $_SESSION['username'] ?? "Pengguna Tidak Dikenali";
$userEmail = $_SESSION['email']; // Ambil e-mel pemohon secara automatik
function getDriverName($conn, $driverId)
{
    if (!$driverId)
        return 'Belum ditugaskan';

    $stmt = $conn->prepare("SELECT namapemandu, notelefon FROM tpemandu WHERE id = ?");
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['namapemandu'] . ' / ' . $row['notelefon'];
    }

    return 'Belum ditugaskan';
}

// Function to check if record can be deleted
function canDeleteRecord($record)
{
    // Only allow deletion if status is 'BARU' or 'TIDAK LULUS'
    return in_array($record['kelulusan'], ['BARU', 'TIDAK LULUS']);
}

// Function to delete record
function deleteRecord($conn, $id, $pemohon)
{
    // First check if the record exists and can be deleted
    $check_sql = "SELECT id, kelulusan FROM tempahan_kenderaan WHERE id = ? AND pemohon = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $id, $pemohon);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        error_log("Record not found or not owned by user: ID=$id, User=$pemohon");
        return false;
    }

    $record = $result->fetch_assoc();
    if (!in_array($record['kelulusan'], ['BARU', 'TIDAK LULUS'])) {
        error_log("Cannot delete record with status: " . $record['kelulusan']);
        return false;
    }

    // Now delete the record
    $sql = "DELETE FROM tempahan_kenderaan WHERE id = ? AND pemohon = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id, $pemohon);

    try {
        $stmt->execute();
        $affected = $stmt->affected_rows;
        error_log("Delete result: affected rows = $affected");
        return $affected > 0;
    } catch (Exception $e) {
        error_log("Delete error: " . $e->getMessage());
        return false;
    }
}

// Handle delete requests
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? 0;
    $success = deleteRecord($conn, $id, $_SESSION['username']);

    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit;
}

// Set the number of items per page
$items_per_page = 20;

// Get the current page number from URL, default to 1 if not set
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the OFFSET for SQL query
$offset = ($page - 1) * $items_per_page;

// Get logged-in user's name
$pemohon = $_SESSION['username'];

// Get the total number of records for the current user
$sql_count = "SELECT COUNT(*) AS total FROM tempahan_kenderaan WHERE pemohon = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("s", $pemohon);
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// If the form is submitted, fetch search data
$search_pemohon = isset($_POST['pemohon']) ? $_POST['pemohon'] : '';
$search_tarikh = isset($_POST['tarikh']) ? $_POST['tarikh'] : '';
$search_destinasi = isset($_POST['destinasi']) ? $_POST['destinasi'] : '';
$search_kelulusan = isset($_POST['kelulusan']) ? $_POST['kelulusan'] : '';

function searchRecords($conn, $searchParams, $pemohon)
{
    $query = "SELECT 
                tk.*, 
                tp.id as pemandu_id, 
                case 
                    when tp.namapemandu is null then  'Belum Ditugaskan' 
                    else tp.namapemandu 
                end as namapemandu,
                tp.notelefon,
                p.nohp,
                kj.no_plat, 
                kj.model, 
                kj.pengeluar, 
                kj.id_jenis AS jenis_id, 
                tj.jenis_kenderaan,
                tj2.jenis_kenderaan as pilihanJenis
            FROM tempahan_kenderaan tk 
            LEFT JOIN tpemandu tp ON tk.id_pemandu = tp.id 
            LEFT join penggunajkn p on tk.user_id = p.id
            LEFT join kenderaan_jabatan kj on tk.id_kenderaan = kj.id 
            LEFT join ttempah_jenis tj on kj.id_jenis = tj.id
            left join ttempah_jenis tj2 on tk.id_pilihanJenis = tj2.id
            WHERE pemohon = ? ";

    $values = [$pemohon];
    $types = "s"; // first parameter type

    if (!empty($searchParams['tarikh'])) {
        $query .= " AND (
        tk.tarikh_mohon LIKE ? 
        OR tk.tarikh_pergi LIKE ? 
        OR tk.tarikh_balik LIKE ?
    )";

        $values[] = "%" . $searchParams['tarikh'] . "%";
        $values[] = "%" . $searchParams['tarikh'] . "%";
        $values[] = "%" . $searchParams['tarikh'] . "%";

        $types .= "sss";
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

    $query .= " ORDER BY tk.id DESC ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();

    return $stmt->get_result();
}

// Default query or search based on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchParams = array(
        'tarikh' => $search_tarikh,
        'destinasi' => $search_destinasi,
        'kelulusan' => $search_kelulusan
    );
    $result = searchRecords($conn, $searchParams, $pemohon);
} else {
    // $sql = "SELECT * FROM tempahan_kenderaan WHERE pemohon = ? ORDER BY id DESC LIMIT ? OFFSET ?";
    $sql = "SELECT 
                tk.*, 
                tp.id as pemandu_id, 
                case 
                    when tp.namapemandu is null then  'Belum Ditugaskan' 
                    else tp.namapemandu 
                end as namapemandu,
                tp.notelefon,
                p.nohp,
                kj.no_plat, 
                kj.model, 
                kj.pengeluar, 
                kj.id_jenis AS jenis_id, 
                tj.jenis_kenderaan,
                tj2.jenis_kenderaan as pilihanJenis
            FROM tempahan_kenderaan tk 
            LEFT JOIN tpemandu tp ON tk.id_pemandu = tp.id 
            LEFT join penggunajkn p on tk.user_id = p.id
            LEFT join kenderaan_jabatan kj on tk.id_kenderaan = kj.id 
            LEFT join ttempah_jenis tj on kj.id_jenis = tj.id
            left join ttempah_jenis tj2 on tk.id_pilihanJenis = tj2.id
            WHERE pemohon = ? ORDER BY id DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $pemohon, $items_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

if (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'insert'
) {

    $TEST_MODE = true; // <-- set to true to test without sending email


    try {
        // Make sure we have a valid user_id from the session
        if (!isset($_SESSION['user_id'])) {
            throw new Exception("User ID not found in session. Please log in again.");
        }

        $user_id = $_SESSION['user_id']; // Get user_id from session
        $pemohon = $_SESSION['username'] ?? 'Tidak diketahui';
        // $tarikhMohon = (new DateTime())->format('Y-m-d H:i:s');
        $bertolak = $_POST['bertolak'] ?? '';
        $destinasi = $_POST['destinasi'] ?? '';
        $negeri = $_POST['negeri'] ?? '';
        $jenisPerjalanan = $_POST['jenis_perjalanan'] ?? '';
        $tarikhPergi = $_POST['tarikh_pergi'] ?? '';
        $masaPergi = $_POST['Masa_Pergi'] ?? '';
        $tarikhBalik = !empty($_POST['tarikh_balik']) ? $_POST['tarikh_balik'] : null;
        $masaBalik = !empty($_POST['Masa_Balik']) ? $_POST['Masa_Balik'] : null;
        $tujuanPerjalanan = $_POST['tujuan_perjalanan'] ?? '';
        $lainTujuan = $_POST['lain_tujuan'] ?? '';
        $bilPenumpang = $_POST['bil_penumpang'] ?? '';
        $senaraiPenumpang = $_POST['senarai_penumpang'] ?? '';
        $jenisKenderaan = $_POST['jenis_kenderaan'] ?? '';

        // Start transaction
        $pdo->beginTransaction();

        // Insert booking into tempahan_kenderaan table
        $stmt = $pdo->prepare("
            INSERT INTO tempahan_kenderaan 
            (pemohon, user_id, bertolak, destinasi, negeri, jenis_perjalanan, tarikh_pergi, masa_pergi, tarikh_balik, masa_balik, tujuan_perjalanan, lain_tujuan, bil_penumpang, senarai_penumpang, id_pilihanJenis) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $pemohon,
            $user_id,

            $bertolak,
            $destinasi,
            $negeri,
            $jenisPerjalanan,
            $tarikhPergi,
            $masaPergi,
            $tarikhBalik,
            $masaBalik,
            $tujuanPerjalanan,
            $lainTujuan,
            $bilPenumpang,
            $senaraiPenumpang,
            $jenisKenderaan
        ]);

        if (!$success) {
            throw new Exception("Failed to insert booking: " . implode(" | ", $stmt->errorInfo()));
        }

        $bookingId = $pdo->lastInsertId();

        // Insert notification
        $notificationMessage = "Tempahan baru dari $pemohon untuk perjalanan ke $destinasi pada $tarikhPergi";
        $notifStmt = $pdo->prepare("INSERT INTO notifications (booking_id, message) VALUES (?, ?)");
        $notifStmt->execute([$bookingId, $notificationMessage]);

        $tpergi = date('d/m/Y', strtotime($tarikhPergi));
        $tbalik = date('d/m/Y', strtotime($tarikhBalik));

        $mpergi = date('H:i', strtotime($masaPergi));
        $mbalik = date('H:i', strtotime($masaBalik));

        // Prepare email content
        $superadminEmail = "sistem.kdh@moh.gov.my";
        $subject = "Tempahan Kenderaan Baru oleh $pemohon";
        $message = "
            <!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<title>Tempahan Kenderaan Baru</title>

<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #eef2f7;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    .wrapper {
        width: 100%;
        padding: 20px 10px;
    }

    .container {
        max-width: 650px;
        margin: auto;
        background: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .header {
        background: linear-gradient(135deg, #2C3E50, #34495E);
        color: #fff;
        padding: 20px;
        text-align: center;
    }

    .header h2 {
        margin: 0;
        font-size: 20px;
        letter-spacing: 0.5px;
    }

    .subtext {
        padding: 15px 20px;
        font-size: 14px;
        color: #555;
        background: #ffffff;
        border-bottom: 1px solid #ffffff;
    }

    .content {
        padding: 10px 20px 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .label {
        font-weight: 600;
        color: #2C3E50;
        width: 45%;
    }

    .value {
        width: 55%;
        text-align: right;
        color: #555;
    }

    .highlight {
        background: #f4f8ff;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .footer {
        text-align: center;
        font-size: 12px;
        color: #888;
        padding: 15px;
        background: #fafafa;
    }

    @media (max-width: 600px) {
        .info-row {
            flex-direction: column;
            text-align: left;
        }

        .value {
            text-align: left;
            margin-top: 3px;
        }
    }
</style>
</head>

<body>

<div class='wrapper'>
    <div class='container'>

    <div class='header'>
        <h2>Maklumat Tempahan Kenderaan</h2>
    </div>

    <div class='subtext'>
        Salam Sejahtera, <br><br>
        Satu tempahan kenderaan telah diterima.
    </div>

   <div class='content' style='padding:20px;'>

<table width='100%' cellpadding='8' cellspacing='0' style='border-collapse: collapse; font-size:14px;'>

<tr>
    <td style='font-weight:bold; width:40%; border:1px solid #eee; background:#f9f9f9;'>Pemohon</td>
    <td style='border:1px solid #eee;'>" . $pemohon . "</td>
</tr>



<tr>
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Bertolak Dari</td>
    <td style='border:1px solid #eee;'>" . $bertolak . "</td>
</tr>

<tr>
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Destinasi</td>
    <td style='border:1px solid #eee;'>" . $destinasi . "</td>
</tr>

<tr>
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Negeri</td>
    <td style='border:1px solid #eee;'>" . $negeri . "</td>
</tr>

<tr>
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Jenis Perjalanan</td>
    <td style='border:1px solid #eee;'>" . $jenisPerjalanan . "</td>
</tr>

<tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Tarikh & Masa Pergi</td>
        <td style='border:1px solid #eeee;'>" . ($tarikhPergi ?: '-') . ", " . ($masaPergi ?: '-') . "</td>
</tr>

";

        if ($jenisPerjalanan == '2 hala') {
            $message .= "
    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Tarikh & Masa Balik</td>
        <td style='border:1px solid #eee;'>" . ($tarikhBalik ?: '-') . ", " . ($masaBalik ?: '-') . "</td>
    </tr>
    ";
        }

        $message .= "
<tr>
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Tujuan</td>
    <td style='border:1px solid #eee;'>" . $tujuanPerjalanan . "</td>
</tr>

<tr>
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Lain-lain Tujuan</td>
    <td style='border:1px solid #eee;'>" . ($lainTujuan ?: 'Tiada') . "</td>
</tr>

<tr>
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Bilangan Penumpang</td>
    <td style='border:1px solid #eee;'>" . $bilPenumpang . "</td>
</tr>

<tr>
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Senarai Penumpang</td>
    <td style='border:1px solid #eee;'>" . $senaraiPenumpang . "</td>
</tr>

</table>
</div>

    <div class='footer'>
        Ini adalah email automatik dari <b>Sistem Tempahan Kenderaan JKN Kedah</b>.<br>
        Sila jangan balas email ini.
    </div>

</div>
</div>

</body>
</html>
        ";

        if ($TEST_MODE) {
            // In test mode, skip sending email
            file_put_contents('email_log.html', $message); // optional: log email content
            $pdo->commit();
            $swalMessage = "Tempahan berjaya disimpan! (TEST MODE, email not sent)";
            $swalType = "success";
        } else {
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            // $mail->SMTPDebug = 2;

            try {
                // Konfigurasi SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sistem.kdh@moh.gov.my';
                $mail->Password = 'gwdv szgw kkkb tkwz';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // ✅ ADD THIS
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
                // Tetapan e-mel
                $mail->setFrom('msrhszm@gmail.com', 'Sistem Tempahan Kenderaan');
                // $mail->addAddress($userEmail); 
                foreach ($admins as $admin) {
                    if (!empty($admin['email'])) {
                        $mail->addAddress($admin['email']);
                    }
                }

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $message;

                if ($mail->send()) {
                    $pdo->commit();
                    $swalMessage = "Tempahan berjaya dihantar!";
                    $swalType = "success";
                } else {
                    throw new Exception("Email failed to send");
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $swalMessage = "Tempahan tidak berjaya. Sila cuba lagi.";
                $swalType = "error";
            }
        }
    } catch (Exception $e) {
        if (isset($pdo))
            $pdo->rollBack();
        $swalMessage = "Ralat: " . $e->getMessage();
        $swalType = "error";
    }

    // Show SweetAlert after processing
    echo "<script>
        Swal.fire({
            title: '$swalType',
            text: '$swalMessage',
            icon: '$swalType'
        });
    </script>";
}


$procedure = "";

$resultProcedure = $conn->query("SELECT content FROM prosedur_tempahan LIMIT 1");
if ($row = $resultProcedure->fetch_assoc()) {
    $procedure = $row['content'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024">
    <title>Sistem Tempahan Kenderaan | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK2.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->

    <style>
        <style>.action-side {
            display: flex;
            /* arrange children in a row */
            justify-content: center;
            /* center horizontally */
            align-items: center;
            /* center vertically */
            gap: 8px;
            /* space between buttons */
        }

        /* Optional: style the buttons for uniform size and hover effect */
        .action-side .action-btn2 {
            display: inline-flex;
            /* make icons inline-flex for proper alignment */
            align-items: center;
            justify-content: center;
            width: 36px;
            /* fixed width */
            height: 36px;
            /* fixed height */
            border-radius: 6px;
            /* rounded corners */
            background-color: rgb(255, 255, 255);
            /* light background */
            text-decoration: none;
            transition: background-color 0.2s, transform 0.2s;
        }

        .action-side .action-btn2:hover {
            background-color: #ffffff;
            transform: scale(1.1);
        }

        /* Optional: style disabled buttons */
        .action-side .action-btn2.disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }
    </style>
    </style>

</head>

<body>
    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close-btn">&times;</span>

            <h3>Prosedur Tempahan Kenderaan</h3>
            <?= $procedure ? $procedure : "<p>Tiada prosedur ditetapkan.</p>" ?>

        </div>
    </div>
    <?php
    include 'dashboard2.php' 
    ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header">
                <h1 class="welcome-text">Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?></h1>
                <div class="left-actions">
                    <a href="#" onclick="openModal('addModal')" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tempahan Kenderaan Baru
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>

            <form method="POST" action="" class="search-container">
                <div class="search-grid">
                    <div class="search-field">
                        <label>Tarikh</label>
                        <input type="date" name="tarikh" value="<?= htmlspecialchars($search_tarikh) ?>">
                    </div>
                    <div class="search-field">
                        <label>Destinasi</label>
                        <input type="text" name="destinasi" value="<?= htmlspecialchars($search_destinasi) ?>"
                            placeholder="Cari Destinasi">
                    </div>
                    <div class="search-field">
                        <label>Status</label>
                        <select name="kelulusan">
                            <option value="">Semua Status</option>
                            <option value="BARU" <?= $search_kelulusan == 'BARU' ? 'selected' : '' ?>>Baru</option>
                            <option value="LULUS" <?= $search_kelulusan == 'LULUS' ? 'selected' : '' ?>>Lulus</option>
                            <option value="TIDAK LULUS" <?= $search_kelulusan == 'TIDAK LULUS' ? 'selected' : '' ?>>
                                Tidak
                                Lulus</option>
                            <option value="BATAL" <?= $search_kelulusan == 'BATAL' ? 'selected' : '' ?>>Batal</option>
                            <option value="KIV" <?= $search_kelulusan == 'KIV' ? 'selected' : '' ?>>KIV</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Cari
                        </button>
                    </div>

                    <div class="search-field">
                        <label>&nbsp;</label>
                        <button type="button" onclick="window.location.href='user_page.php'" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </form>

            <!-- List tempahahn -->
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="text-align:center;">Bil</th>
                            <th>Tarikh Mohon</th>
                            <th>Butiran Perjalanan</th>
                            <th>Pemandu</th>
                            <th>Kenderaan</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $offset + 1;
                        if ($result->num_rows > 0): ?>
                            <?php while ($record = $result->fetch_assoc()):

                                $statusClass = match (isset($record['kelulusan']) ? $record['kelulusan'] : 'BARU') {
                                    'LULUS' => 'status-approved',
                                    'TIDAK LULUS' => 'status-rejected',
                                    'KIV' => 'status-pending',
                                    'BARU' => 'status-new',
                                    'BATAL' => 'status-batal',
                                    default => ''
                                };

                                $canDelete = canDeleteRecord($record);
                                ?>
                                <tr>

                                    <td style="text-align:center;">
                                        <?= $no++ ?>
                                    </td>

                                    <td class="nama" style="font-size: 15px;">
                                        <?= date('d/m/Y H:i', strtotime($record['tarikh_mohon'])) ?>
                                    </td>

                                    <td class="nama" style="font-size: 15px;">
                                        <strong><?= htmlspecialchars($record['bertolak']) ?> -
                                            <?= htmlspecialchars($record['destinasi']) ?></strong><br>
                                        Tarikh Pergi: <?= date('d/m/Y', strtotime($record['tarikh_pergi'])) ?>
                                        <?= date('H:i', strtotime($record['masa_pergi'])) ?><br>
                                        <?php if (htmlspecialchars($record['jenis_perjalanan']) == '2 hala') { ?>
                                            Tarikh Balik: <?= date('d/m/Y H:i', strtotime($record['tarikh_balik'])) ?>
                                        <?php } ?>
                                    </td>

                                    <td class="nama" style="font-size: 15px;">
                                        <?php if (htmlspecialchars($record['namapemandu']) == 'Belum Ditugaskan') { ?>
                                            <?= htmlspecialchars($record['namapemandu']) ?>
                                        <?php } else { ?>
                                            <strong><?= htmlspecialchars($record['namapemandu']) ?></strong>
                                            <br><?= htmlspecialchars($record['notelefon']) ?>
                                        <?php } ?>
                                    </td>

                                    <?php if (htmlspecialchars($record['no_plat']) == NULL) { ?>
                                        <td class="nama" style="font-size: 15px;">
                                            <?= htmlspecialchars($record['pilihanJenis']) ?>
                                        </td>
                                    <?php } else { ?>
                                        <td class="nama" style="font-size: 15px;">
                                            <strong><?= htmlspecialchars($record['no_plat']) ?></strong>
                                            <br><?= htmlspecialchars($record['pengeluar']) ?>
                                            <?= htmlspecialchars($record['model']) ?>
                                        </td>
                                    <?php } ?>

                                    <td class="nama" style="font-size: 15px;">
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($record['kelulusan'] ?? 'BARU') ?>
                                        </span>
                                    </td>

                                    <td class="action-side">

                                        <a href="javascript:void(0)" class="action-btn2" data-id="<?= $record['id'] ?>"
                                            data-pemohon="<?= htmlspecialchars($record['pemohon'], ENT_QUOTES) ?>"
                                            data-tarikh="<?= htmlspecialchars($record['tarikh_mohon'], ENT_QUOTES) ?>"
                                            data-destinasi="<?= htmlspecialchars($record['destinasi'], ENT_QUOTES) ?>"
                                            data-bertolak="<?= htmlspecialchars($record['bertolak']) ?>"
                                            data-status="<?= htmlspecialchars($record['kelulusan'] ?? 'BARU') ?>"
                                            data-tarikh_pergi="<?= htmlspecialchars($record['tarikh_pergi']) ?>"
                                            data-masa_pergi="<?= htmlspecialchars($record['masa_pergi']) ?>"
                                            data-tarikh_balik="<?= htmlspecialchars($record['tarikh_balik']) ?>"
                                            data-masa_balik="<?= htmlspecialchars($record['masa_balik']) ?>"
                                            data-pemandu="<?= htmlspecialchars($record['namapemandu']) ?>"
                                            data-notelefon="<?= htmlspecialchars(($record['notelefon'])) ?>"
                                            data-kenderaan="<?= htmlspecialchars($record['pilihanJenis']) ?>"
                                            data-plat="<?= htmlspecialchars($record['no_plat']) ?>"
                                            data-model="<?= htmlspecialchars($record['model']) ?>"
                                            data-pengeluar="<?= htmlspecialchars($record['pengeluar']) ?>" title="Lihat"
                                            onclick=" openEditModal(this)">
                                            <i class="fas fa-eye" style="color: #3498DB;"></i>
                                        </a>

                                        <?php if ($canDelete): ?>
                                            <a href="#" onclick="deleteRecord(<?= $record['id'] ?>)" class="action-btn2 delete"
                                                title="Padam">
                                                <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                            </a>

                                        <?php else: ?>
                                            <a href="#" class="action-btn2 disabled" title="Tidak boleh dipadam"
                                                onmouseover="showTooltip(this, 'Hanya tempahan BARU atau TIDAK LULUS boleh dipadam')"
                                                onmouseout="hideTooltip(this)">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>

                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">Tiada rekod dijumpai.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?>"
                            class="page-link page-nav" title="Muka Pertama">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?page=<?= $page - 1 ?><?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?>"
                            class="page-link page-nav" title="Sebelum">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    <a href="?page=<?= $page ?><?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?>"
                        class="page-link active">
                        <?= $page ?>
                    </a>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?>"
                            class="page-link page-nav" title="Seterusnya">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?= $total_pages ?><?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?>"
                            class="page-link page-nav" title="Muka Terakhir">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <!-- View Modal -->
    <div id="editModal" class="modal2">
        <input type="hidden" id="modalRecordId">
        <div class="modal-content2">
            <div class="booking-card">
                <div class="booking-header">
                    <h2>Butiran Tempahan Kenderaan</h2>
                    <span class="booking-id">ID: <span id="modalRecordIdText"></span></span>
                </div>
                <div class="booking-content">
                    <div class="info-grid">
                        <div class="info-group">
                            <div class="info-label">Pemohon</div>
                            <div class="info-value">
                                <i class="fas fa-user"></i>
                                <span id="modalPemohonText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Tarikh Memohon</div>
                            <div class="info-value">
                                <i class="fas fa-calendar-alt"></i>
                                <span id="modalTarikhText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Lokasi Bertolak</div>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="modalBertolakText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Destinasi</div>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="modalDestinasiText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Tarikh & Masa Pergi</div>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="modalTarikhMasaPergiText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Tarikh & Masa Balik</div>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="modalTarikhMasaBalikText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Status Kelulusan</div>
                            <div class="info-value">

                                <span class="status-badge" id="statusBadge">
                                    <i class="fas fa-circle-info"></i>
                                    <span id="modalStatusText"></span>
                                </span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Pemandu</div>
                            <div class="info-value">
                                <i class="fas fa-id-card"></i>
                                <span id="modalPemanduText"></span>
                                <!-- <span id="modalNotelefonText"></span> -->
                            </div>
                            <div class="info-value">
                                <i class="fas fa-phone"></i>
                                <!-- <span id="modalPemanduText"></span> -->
                                <span id="modalNotelefonText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Kenderaan</div>
                            <div class="info-value">
                                <i class="fas fa-ticket"></i>
                                <span id="modalPlatText"></span>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-car"></i>
                                <span id="modalPengeluarText"></span>

                            </div>
                            <!-- <div class="info-value">
                                <i class="fas fa-ticket"></i>
                                <span id="modalKenderaanText"></span>
                            </div> -->

                        </div>
                    </div>
                    <div class="button-group">
                        <a onclick="closeEditModal()" class="btn btn-primary">
                            <span><i class="fas fa-arrow-left"></i> Kembali</span>

                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addModal')">&times;</span>

                    <h2 class="form-title">Borang Tempahan Kenderaan</h2>
                </div>

                <form id="addForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="insert">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Lokasi Bertolak*</label>
                            <textarea id="bertolak" name="bertolak" class="form-control" rows="3"
                                style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
                                required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Destinasi*</label>
                            <textarea id="destinasi" name="destinasi" class="form-control" rows="3"
                                style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
                                required></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jenis Perjalanan*</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="jenis_perjalanan" value="2 hala" required>
                                2 hala
                            </label>
                            <label>
                                <input type="radio" name="jenis_perjalanan" value="1 hala" required>
                                1 hala
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tarikh Pergi*</label>
                            <input type="date" id="tarikh_pergi" name="tarikh_pergi" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Masa Pergi*</label>
                            <input type="time" id="Masa_Pergi" name="Masa_Pergi" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tarikh Balik*</label>
                            <input type="date" id="tarikh_balik" name="tarikh_balik" class="form-control" required>
                        </div>


                        <div class="form-group">
                            <label class="form-label">Masa Balik*</label>
                            <input type="time" id="Masa_Balik" name="Masa_Balik" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tujuan Perjalanan*</label>
                            <select id="tujuan_perjalanan" name="tujuan_perjalanan" class="form-control" required>
                                <option value="">Pilih Tujuan</option>
                                <?php
                                // Query to fetch travel purposes from ttempah_tujuan table
                                $tujuanQuery = "SELECT * FROM ttempah_tujuan ORDER BY tujuan_perjalanan ASC";
                                $tujuanResult = $conn->query($tujuanQuery);

                                if ($tujuanResult && $tujuanResult->num_rows > 0) {
                                    while ($tujuan = $tujuanResult->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($tujuan['tujuan_perjalanan']) . '">' .
                                            htmlspecialchars($tujuan['tujuan_perjalanan']) . '</option>';
                                    }
                                } else {
                                    // Fallback to hardcoded options if query fails
                                    $defaultTujuan = ["Mesyuarat", "Lawatan Kerja", "Bank", "Kursus/Seminar", "Lain-lain"];
                                    foreach ($defaultTujuan as $tujuan) {
                                        echo '<option>' . $tujuan . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ulasan Perjalanan*</label>
                            <input type="text" id="lain_tujuan" name="lain_tujuan" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Negeri*</label>
                            <select id="negeri" name="negeri" class="form-control" required>
                                <option value="">Pilih Negeri</option>
                                <?php
                                // Query to fetch states from tnegeri table
                                $negeriQuery = "SELECT * FROM tnegeri ORDER BY negeri ASC";
                                $negeriResult = $conn->query($negeriQuery);

                                if ($negeriResult && $negeriResult->num_rows > 0) {
                                    while ($negeri = $negeriResult->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($negeri['negeri']) . '">' .
                                            htmlspecialchars($negeri['negeri']) . '</option>';
                                    }
                                } else {
                                    // Fallback to hardcoded options if query fails
                                    $defaultNegeri = [
                                        "Johor",
                                        "Kedah",
                                        "Kelantan",
                                        "Kuala Lumpur",
                                        "Labuan",
                                        "Melaka",
                                        "Negeri Sembilan",
                                        "Pahang",
                                        "Perak",
                                        "Perlis",
                                        "Pulau Pinang",
                                        "Putrajaya",
                                        "Sabah",
                                        "Sarawak",
                                        "Selangor",
                                        "Terengganu"
                                    ];
                                    foreach ($defaultNegeri as $negeri) {
                                        echo '<option>' . $negeri . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jenis Kenderaan*</label>
                            <select id="jenis_kenderaan" name="jenis_kenderaan" class="form-control" required>
                                <option value="">Pilih Kenderaan</option>
                                <?php
                                // Query to fetch vehicle types from ttempah_jenis table
                                $kenderaanQuery = "SELECT * FROM ttempah_jenis ORDER BY jenis_kenderaan ASC";
                                $kenderaanResult = $conn->query($kenderaanQuery);

                                if ($kenderaanResult && $kenderaanResult->num_rows > 0) {
                                    while ($kenderaan = $kenderaanResult->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($kenderaan['id']) . '">' .
                                            htmlspecialchars($kenderaan['jenis_kenderaan']) . '</option>';
                                    }
                                } else {
                                    // Fallback to hardcoded options if query fails
                                    $defaultKenderaan = ["Sedan", "MPV 6 Seater", "Lori", "Hino", "SUV"];
                                    foreach ($defaultKenderaan as $kenderaan) {
                                        echo '<option>' . $kenderaan . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Bilangan Penumpang*</label>
                            <input type="number" id="bil_penumpang" name="bil_penumpang" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label2">Senarai Penumpang / No Telefon*</label>
                            <textarea id="senarai_penumpang" name="senarai_penumpang" class="form-control" rows="2"
                                style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
                                required></textarea>
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                        <button type="button" class="btn btn-cancel" onclick="handleCancel()"><i
                                class="fas fa-times"></i> Batal</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script>
        const today = new Date().toISOString().split('T')[0];
        document.getElementById("tarikh_pergi").setAttribute("min", today);
        const modal = document.getElementById('editModal');

        function validateTime() {
            const tarikhPergi = document.getElementById("tarikh_pergi").value;
            const tarikhBalik = document.getElementById("tarikh_balik").value;
            const masaPergi = document.getElementById("Masa_Pergi").value;
            const masaBalik = document.getElementById("Masa_Balik");

            // Reset min first
            masaBalik.removeAttribute("min");

            // If same date → enforce time rule
            if (tarikhPergi && tarikhBalik && tarikhPergi === tarikhBalik) {
                masaBalik.setAttribute("min", masaPergi);
            }
        }

        // Run when user changes anything
        document.getElementById("tarikh_pergi").addEventListener("change", validateTime);
        document.getElementById("tarikh_balik").addEventListener("change", validateTime);
        document.getElementById("Masa_Pergi").addEventListener("change", validateTime);

        function openEditModal(el) {
            const id = el.dataset.id;
            const pemohon = el.dataset.pemohon;
            const tarikh_mohon = el.dataset.tarikh;
            const bertolak = el.dataset.bertolak;
            const destinasi = el.dataset.destinasi;
            const status = el.dataset.status;
            const tarikh_pergi = el.dataset.tarikh_pergi;
            const masa_pergi = el.dataset.masa_pergi;
            const tarikh_balik = el.dataset.tarikh_balik;
            const masa_balik = el.dataset.masa_balik;
            const pemandu = el.dataset.pemandu;
            const notelefon = el.dataset.notelefon;
            const kenderaan = el.dataset.kenderaan || '-';
            const pengeluar = el.dataset.pengeluar;
            const model = el.dataset.model;
            const plat = el.dataset.plat;

            document.getElementById('modalRecordId').value = id;
            document.getElementById('modalRecordIdText').textContent = id;
            document.getElementById('modalPemohonText').textContent = pemohon;
            document.getElementById('modalTarikhText').textContent = tarikh_mohon;
            document.getElementById('modalBertolakText').textContent = bertolak;
            document.getElementById('modalDestinasiText').textContent = destinasi;
            document.getElementById('modalStatusText').textContent = status;
            document.getElementById('modalTarikhMasaBalikText').textContent = (tarikh_balik && masa_balik) ? tarikh_balik +
                " " + masa_balik : "-";
            document.getElementById('modalTarikhMasaPergiText').textContent = (tarikh_pergi && masa_pergi) ? tarikh_pergi +
                " " + masa_pergi : "-";
            document.getElementById('modalPemanduText').textContent = pemandu;
            // document.getElementById('modalKenderaanText').textContent = kenderaan;
            document.getElementById('modalNotelefonText').textContent = notelefon;
            document.getElementById('modalPengeluarText').textContent = (pengeluar && model) ? pengeluar + " " + model : "-";
            document.getElementById('modalPlatText').textContent = (plat) ? plat : kenderaan;

            document.getElementById('editModal').style.display = 'flex';

            let badge = document.getElementById("statusBadge");

            // Remove old classes first
            badge.classList.remove("status-approved", "status-rejected", "status-pending");

            if (status === "LULUS") {
                badge.classList.add("status-approved");
            } else if (status === "TIDAK LULUS") {
                badge.classList.add("status-rejected");
            } else if (status === "KIV") {
                badge.classList.add("status-pending");
            } else if (status === "BARU") {
                badge.classList.add("status-new");
            } else if (status === "BATAL") {
                badge.classList.add("status-batal")
            }
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.style.display = 'none';
        }

        function deleteRecord(id) {
            Swal.fire({
                title: 'Adakah anda pasti?',
                text: 'Rekod ini akan dipadam secara kekal!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, padam!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + encodeURIComponent(id)
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Berjaya!', 'Rekod telah dipadam.', 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Ralat!', data.message, 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Ralat!', 'Masalah sambungan ke server.', 'error');
                        });
                }
            });
        };

        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        document.addEventListener("DOMContentLoaded", function () {
            const now = new Date();

            const date = now.toISOString().split('T')[0];
            const time = now.toLocaleTimeString('en-GB');

            document.getElementById("tarikh_mohon").value = date + " " + time;
        });

        document.addEventListener('DOMContentLoaded', function () {
            const radioButtons = document.querySelectorAll('input[name="jenis_perjalanan"]');
            const tarikhBalik = document.getElementById('tarikh_balik');
            const masaBalik = document.getElementById('Masa_Balik');

            function toggleReturnFields() {
                const selectedValue = document.querySelector('input[name="jenis_perjalanan"]:checked').value;

                if (selectedValue === '1 hala') {
                    tarikhBalik.disabled = true;
                    masaBalik.disabled = true;
                    tarikhBalik.required = false;
                    masaBalik.required = false;

                    // Clear values
                    tarikhBalik.value = '';
                    masaBalik.value = '';
                } else {
                    tarikhBalik.disabled = false;
                    masaBalik.disabled = false;
                    tarikhBalik.required = true;
                    masaBalik.required = true;
                }
            }

            // Add event listeners to radio buttons
            radioButtons.forEach(function (radio) {
                radio.addEventListener('change', toggleReturnFields);
            });

            // Initialize on page load
            if (document.querySelector('input[name="jenis_perjalanan"]:checked')) {
                toggleReturnFields();
            }
        });
        document.getElementById('tarikh_balik').addEventListener('change', function () {
            const tarikhPergiDate = new Date(document.getElementById('tarikh_pergi').value);
            const tarikhBalikDate = new Date(this.value);

            if (tarikhBalikDate < tarikhPergiDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat',
                    text: 'Tarikh balik tidak boleh lebih awal dari tarikh pergi!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    document.getElementById('tarikh_balik').value = '';
                });
            }
        });

        function handleCancel() {
            const modal = document.getElementById('addModal');
            const form = document.getElementById('addForm');

            const hasValue = Array.from(form.elements).some(el => {
                // if (el.name === 'pemohon' || el.name === 'tarikh_mohon') return false;
                if (el.type === "radio") return el.checked;
                if (el.type === "button" || el.type === "submit") return false;
                return el.value.trim() !== '';
            });

            if (!hasValue) {
                modal.style.display = 'none';
                return;
            }

            Swal.fire({
                title: 'Anda pasti?',
                text: "Semua maklumat yang diisi akan dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, batalkan!',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    Array.from(form.elements).forEach(el => {
                        if (el.name === 'pemohon' || el.name === 'tarikh_mohon') return;
                        if (el.type === 'radio' || el.type === 'checkbox') {
                            el.checked = false;
                        } else if (el.type !== 'button' && el.type !== 'submit') {
                            el.value = '';
                        }
                    });

                    modal.style.display = 'none';

                    Swal.fire(
                        'Dibatalkan!',
                        'Borang telah dikosongkan.',
                        'success'
                    );
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($swalMessage)): ?>
                Swal.fire({
                    title: '<?php echo $swalType === "success" ? "Berjaya!" : "Gagal!"; ?>',
                    text: '<?php echo addslashes($swalMessage); ?>',
                    icon: '<?php echo $swalType; ?>',
                }).then(() => {
                    <?php if ($swalType === "success"): ?>
                        window.location.href = 'user_page.php';
                    <?php endif; ?>
                });
            <?php endif; ?>
        });

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
    </script>
</body>

</html>