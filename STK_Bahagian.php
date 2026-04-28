<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Add these require statements
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
// Database connection
session_start();
require 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'penyelaras_bahagian') {
    header("Location: error.html");
    exit();
}

$showPopup = false;

if (isset($_SESSION['show_popup'])) {
    $showPopup = true;
    unset($_SESSION['show_popup']);
}

// ✅ Safe to use
$bahagian = $_SESSION['bahagian'] ?? '';

// echo ($bahagian);


function stkPemanduNameUpper(?string $name): string
{
    if ($name === null || $name === '') {
        return '';
    }
    $t = trim($name);
    if ($t === '') {
        return '';
    }
    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($t, 'UTF-8');
    }
    return strtoupper($t);
}
// Function to get driver name by ID
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
        return htmlspecialchars(stkPemanduNameUpper($row['namapemandu']));
    }
    return 'Belum ditentukan';
}

/**
 * Trip date range [start, end] as Y-m-d strings for overlap checks.
 */
function stkTripRangeFromBooking(?string $tarikh_pergi, ?string $tarikh_balik): ?array
{
    $tarikh_pergi = $tarikh_pergi !== null ? trim($tarikh_pergi) : '';
    if ($tarikh_pergi === '' || strcasecmp($tarikh_pergi, 'None') === 0) {
        return null;
    }
    $tripStart = substr($tarikh_pergi, 0, 10);

    $tarikh_balik = $tarikh_balik !== null ? trim($tarikh_balik) : '';
    if ($tarikh_balik === '' || strcasecmp($tarikh_balik, 'None') === 0) {
        return [$tripStart, $tripStart];
    }
    $tripEnd = substr($tarikh_balik, 0, 10);
    if ($tripEnd < $tripStart) {
        return [$tripEnd, $tripStart];
    }
    return [$tripStart, $tripEnd];
}

/**
 * True if pemandu_leave overlaps the trip range for this driver.
 */
function stkPemanduLeaveOverlapsTrip(mysqli $conn, int $pemanduId, string $tripStart, string $tripEnd): bool
{
    if ($pemanduId <= 0) {
        return false;
    }
    $sql = "SELECT 1 FROM pemandu_leave WHERE pemandu_id = ? AND start_date <= ? AND end_date >= ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("iss", $pemanduId, $tripEnd, $tripStart);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res && $res->num_rows > 0;
}

function getAdminData($conn)
{
    $stmt = $conn->prepare("SELECT nama, email FROM penggunajkn WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}
$admins = getAdminData($conn);

$swalMessage = null;
$swalType = null;
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

// Initialize search parameters if form is submitted
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'search'
) {
    $searchParams = array(
        'id' => $_POST['id'] ?? '',
        'pemohon' => $_POST['pemohon'] ?? '',
        'tarikh' => $_POST['tarikh'] ?? '',
        'destinasi' => $_POST['destinasi'] ?? '',
        'kelulusan' => $_POST['kelulusan'] ?? '',
        'pemandu' => $_POST['pemandu'] ?? '',
        'kenderaan' => $_POST['kenderaan'] ?? ''
    );

    $result = searchRecords($conn, $searchParams);
} else {
    // Default query with pagination
    $sql = "SELECT 
                tk.*, 
                tp.id as pemandu_id, 
                case 
                    when tp.namapemandu is null then  'Belum Ditugaskan' 
                    else tp.namapemandu 
                end as namapemandu,
                tp.notelefon,
                p.nohp,
                kj.id AS kenderaan_id,
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
where p.bahagian = ?
            ORDER BY 
                CASE 
                    WHEN tk.kelulusan = 'Baru' THEN 0
                    ELSE 1
                END,
                CASE 
                    WHEN tk.kelulusan = 'Baru' THEN tk.tarikh_mohon
                END ASC,
                CASE 
                    WHEN tk.kelulusan <> 'Baru' THEN tk.tarikh_mohon
                END DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $bahagian, $items_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Make sure the searchRecords function includes pagination
function searchRecords($conn, $searchParams)
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
                kj.id AS kenderaan_id,
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

if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    $notifId = (int) $_POST['notification_id'];
    $markReadQuery = "UPDATE notifications SET is_read_admin = 1 WHERE id = ?";
    $stmt = $conn->prepare($markReadQuery);
    $stmt->bind_param("i", $notifId);
    $stmt->execute();

    // Redirect supaya elak resubmit & refresh count
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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

// // Mark notifications as read if requested
// if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
//     $notifId = (int) $_POST['notification_id'];
//     $markReadQuery = "UPDATE notifications SET is_read = 1 WHERE id = ?";
//     $stmt = $conn->prepare($markReadQuery);
//     $stmt->bind_param("i", $notifId);
//     $stmt->execute();
// }

// At the top of the file, add this to track if we're searching
$isSearching = ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    (!empty($_POST['id']) ||
        !empty($_POST['pemohon']) ||
        !empty($_POST['destinasi']) ||
        !empty($_POST['kelulusan']) ||
        !empty($_POST['tarikh'])));

// Check for success message
if (isset($_SESSION['success_message'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('" . addslashes($_SESSION['success_message']) . "');
        });
    </script>";
    unset($_SESSION['success_message']);
}

// Check for error message
if (isset($_SESSION['error_message'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('Error: " . addslashes($_SESSION['error_message']) . "');
        });
    </script>";
    unset($_SESSION['error_message']);
}

// Check for updated record to highlight
if (isset($_SESSION['updated_record'])) {
    $updatedRecord = $_SESSION['updated_record'];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store the updated record in sessionStorage for highlighting
            sessionStorage.setItem('updatedRecord', JSON.stringify(" . json_encode($updatedRecord) . "));
            
            // Find and highlight the row
            const row = document.querySelector('tr[data-id=\"" . $updatedRecord['id'] . "\"]');
            if (row) {
                row.classList.add('updated-row');
                setTimeout(() => {
                    row.classList.remove('updated-row');
                }, 3000);
            }
        });
    </script>";
    unset($_SESSION['updated_record']);
}



if (
    $_SERVER["REQUEST_METHOD"] == "POST"
    && isset($_POST['action'])
    && $_POST['action'] === 'update'
) {
   $id = (int) $_POST['id'];   // 🔥 FIRST THING

$kelulusan = $_POST['kelulusan'];

$pemohonEmail = "";
$pemohon = "";

$getPemohon = $conn->prepare("
    SELECT p.nama, p.email, tk.tarikh_mohon ,tk.bertolak, tk.destinasi, tk.jenis_perjalanan , tk.tarikh_pergi, tk.tarikh_balik, tk.masa_pergi, tk.masa_balik,
tk.bil_penumpang, tk.senarai_penumpang, kj.no_plat, kj.model, kj.pengeluar, tk.kelulusan, tk.tujuan_perjalanan, tk.lain_tujuan  
FROM tempahan_kenderaan tk
    JOIN penggunajkn p ON tk.user_id = p.id
    JOIN kenderaan_jabatan kj ON tk.id_kenderaan = kj.id
    join ttempah_jenis tj on kj.id_jenis = tj.id
    WHERE tk.id = ?
");
$getPemohon->bind_param("i", $id);
$getPemohon->execute();
$resultPemohon = $getPemohon->get_result();

if ($rowPemohon = $resultPemohon->fetch_assoc()) {
    $pemohonEmail = $rowPemohon['email'];
    $pemohon = $rowPemohon['nama'];

    $tarikh_mohon = $rowPemohon['tarikh_mohon'];
    $bertolak = $rowPemohon['bertolak'];
    $destinasi = $rowPemohon['destinasi'];
    $tarikh_pergi = $rowPemohon['tarikh_pergi'];
    $masa_pergi = $rowPemohon['masa_pergi'];
    $tarikh_balik = $rowPemohon['tarikh_balik'];
    $masa_balik = $rowPemohon['masa_balik'];
    $tujuan_perjalanan = $rowPemohon['tujuan_perjalanan'];
    $lain_tujuan = $rowPemohon['lain_tujuan'];
    $bil_penumpang = $rowPemohon['bil_penumpang'];
    $senarai_penumpang = $rowPemohon['senarai_penumpang'];
    $no_plat2 = $rowPemohon['no_plat'];
    $model = $rowPemohon['model'];
    $pengeluar = $rowPemohon['pengeluar'];
}

    // $id = (int) $_POST['id'];
    // $kelulusan = $_POST['kelulusan'];
    $pemandu = null;
    if (isset($_POST['pemandu']) && $_POST['pemandu'] !== '') {
        $pemandu = (int) $_POST['pemandu'];
        if ($pemandu <= 0) {
            $pemandu = null;
        }
    }
    $no_plat = $_POST['no_plat'];

    $bookingRange = null;
    $bkStmt = $conn->prepare("SELECT tarikh_pergi, tarikh_balik FROM tempahan_kenderaan WHERE id = ?");
    if ($bkStmt) {
        $bkStmt->bind_param("i", $id);
        $bkStmt->execute();
        $bkRow = $bkStmt->get_result()->fetch_assoc();
        if ($bkRow) {
            $bookingRange = stkTripRangeFromBooking($bkRow['tarikh_pergi'] ?? null, $bkRow['tarikh_balik'] ?? null);
        }
    }

    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("sisi", $kelulusan, $pemandu, $no_plat, $id);

    $leaveConflict = false;
    if ($pemandu !== null && $pemandu > 0 && $bookingRange !== null) {
        [$ts, $te] = $bookingRange;
        $leaveConflict = stkPemanduLeaveOverlapsTrip($conn, $pemandu, $ts, $te);
    }

    if ($leaveConflict) {
        $_SESSION['error_message'] = 'Pemandu yang dipilih mempunyai cuti pada tempoh perjalanan ini. Sila pilih pemandu lain.';
    } else {
        $sql = "UPDATE tempahan_kenderaan 
                SET kelulusan = ?,
                    id_pemandu = ?,
                    id_kenderaan = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $kelulusan, $pemandu, $no_plat, $id);

        if ($stmt->execute()) {

            // ✅ SEND EMAIL HERE
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                // $mail->Username = 'sistem.kdh@moh.gov.my';
                //$mail->Password = 'gwdv szgw kkkb tkwz';
                $mail->Username = 'msrhszm@gmail.com';
                $mail->Password = 'miar lgbv cftw jhzt';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // $mail->setFrom('sistem.kdh@moh.gov.my', 'Sistem Tempahan Kenderaan');
                $mail->setFrom('msrhszm@gmail.com', 'Sistem Tempahan Kenderaan');
                $mail->addAddress($pemohonEmail);

                $mail->isHTML(true);
                $mail->Subject = 'Status Tempahan Dikemaskini';

                $mail->Body = "
            <!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
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
        background: #f8f9fb;
        border-bottom: 1px solid #eee;
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
        <h2>Status Tempahan Kenderaan</h2>
    </div>

    <div class='subtext>
        Salam Sejahtera, <br><br>
        Status tempahan kenderaan anda adalah seperti berikut:
    </div>

   <div class='content' style='padding:20px;'>

<table width='100%' cellpadding='8' cellspacing='0' style='border-collapse: collapse; font-size:14px;'>

    <tr>
        <td style='font-weight:bold; width:40%;  border:1px solid #eee; background:#f9f9f9;'>Pemohon</td>
        <td style='border:1px solid #eeee;'>" . $pemohon . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Tarikh Memohon</td>
        <td style='border:1px solid #eeee;'>" . $tarikh_mohon . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold;  border:1px solid #eee; background:#f9f9f9;'>Bertolak Dari</td>
        <td style='border:1px solid #eeee;'>" . $bertolak . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Destinasi</td>
        <td style='border:1px solid #eeee;'>" . $destinasi . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Tarikh & Masa Pergi</td>
        <td style='border:1px solid #eeee;'>" . $tarikh_pergi . " " . $masa_pergi . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Tarikh & Masa Balik</td>
        <td style='border:1px solid #eeee;'>
            " . $tarikh_balik . " " . $masa_balik . "
        </td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Tujuan</td>
        <td style='border:1px solid #eeee;'>" . $tujuan_perjalanan . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Ulasan Perjalanan</td>
        <td style='border:1px solid #eeee;'>" . $lain_tujuan . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Bilangan Penumpang</td>
        <td style='border:1px solid #eeee;'>" . $bil_penumpang . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Senarai Penumpang</td>
        <td style='border:1px solid #eeee;'>" . $senarai_penumpang . "</td>
    </tr>

    <tr>
        <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Kenderaan</td>
        <td style='border:1px solid #eeee;'>" . $no_plat2 . " - " . $pengeluar . " " . $model . "</td>
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

                $mail->send();

                $swalMessage = 'Tempahan berjaya dikemaskini & email dihantar!';
                $swalType = 'success';
            } catch (Exception $e) {
                $swalMessage = 'Tempahan berjaya dikemaskini tetapi email gagal dihantar: ' . $mail->ErrorInfo;
                $swalType = 'warning';
            }
        } else {
            $swalMessage = 'Gagal mengemaskini tempahan: ' . $conn->error;
            $swalType = 'error';
        }
    }
}


function getUserData($conn)
{
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT nama, email FROM penggunajkn WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}


$userData = getUserData($conn);
$nama = $userData['nama'] ?? "Pengguna Tidak Dikenali";
$userEmail = $userData['email'] ?? ""; // Ambil e-mel pemohon secara automatik


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
        // $tarikhMohon = $_POST['tarikh_mohon'] ?? '';
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
    <td style='font-weight:bold; border:1px solid #eee; background:#f9f9f9;'>Ulasan Perjalanan</td>
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
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                // $mail->Username = 'sistem.kdh@moh.gov.my';
                //$mail->Password = 'gwdv szgw kkkb tkwz';
                $mail->Username = 'msrhszm@gmail.com';
                $mail->Password = 'miar lgbv cftw jhzt';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // $mail->setFrom('sistem.kdh@moh.gov.my', 'Sistem Tempahan Kenderaan');
                $mail->setFrom('msrhszm@gmail.com', 'Sistem Tempahan Kenderaan');
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
                    $swalMessage = "Tempahan berjaya dihantar dan notifikasi telah dihantar!";
                    $swalType = "success";
                } else {
                    throw new Exception("Email failed to send");
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $swalMessage = "Tempahan tidak berjaya. Ralat: " . $e->getMessage();
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

$baruCountQuery = "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'BARU'";
$baruCountResult = $conn->query($baruCountQuery);
$baruCount = $baruCountResult->fetch_assoc()['count'];

$lulusCountQuery = "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'LULUS'";
$lulusCountResult = $conn->query($lulusCountQuery);
$lulusCount = $lulusCountResult->fetch_assoc()['count'];

$tidakLulusCountQuery = "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'TIDAK LULUS'";
$tidakLulusCountResult = $conn->query($tidakLulusCountQuery);
$tidakLulusCount = $tidakLulusCountResult->fetch_assoc()['count'];

$pemandu_leave_rows = [];
$leaveRes = $conn->query("SELECT pemandu_id, start_date, end_date FROM pemandu_leave");
if ($leaveRes) {
    while ($row = $leaveRes->fetch_assoc()) {
        $pemandu_leave_rows[] = [
            'pemandu_id' => (int) $row['pemandu_id'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
        ];
    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Tempahan Kenderaan | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK2.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        window.PEMANDU_LEAVE =
            <?= json_encode($pemandu_leave_rows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>

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

        #editModal .select2-container {
            width: 100% !important;
        }

        #editModal .select2-container--default .select2-selection--single {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        #editModal .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }

        #editModal .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        #editModal .modal-content2 {
            position: relative;
        }

        #editModal .select2-container--open,
        #editModal .select2-dropdown {
            z-index: 10050 !important;
        }

        .form-container {
            width: 100% !important;
        }

        .form-container--default {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 4px;
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
    <?= 
    include 'dashboard2.php'
    ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Senarai Tempahan Kenderaan</h1>
                <div class="left-actions">
                    <a href="javascript:void(0)" onclick="openModal('addModal')" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tempahan Kenderaan Baru
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="" class="search-container" onsubmit="return validateSearch()">
            <input type="hidden" name="action" value="search">
            <div class="search-grid">
                <div class="search-field">
                    <label>ID</label>
                    <input type="text" name="id" value="<?= htmlspecialchars($_POST['id'] ?? '') ?>"
                        placeholder="Cari ID">
                </div>
                <div class="search-field">
                    <label>Pemohon</label>
                    <input type="text" name="pemohon" value="<?= htmlspecialchars($search_pemohon) ?>"
                        placeholder="Cari Pemohon">
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
                        <option value="TIDAK LULUS" <?= $search_kelulusan == 'TIDAK LULUS' ? 'selected' : '' ?>>Tidak
                            Lulus
                        </option>
                        <option value="KIV" <?= $search_kelulusan == 'KIV' ? 'selected' : '' ?>>KIV</option>
                        <option value="BATAL" <?= $search_kelulusan == 'BATAL' ? 'selected' : '' ?>>Batal</option>
                    </select>
                </div>
                <div class="search-field">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Cari
                    </button>
                </div>
            </div>
        </form>

        <!-- <form action="" method="GET" class="search-field" style="display:flex; justify-content:fle">
                <div class="search-grid2">
                    <input type="text" name="search" class="search-input"
                        placeholder="Cari Tempahan Kenderaan..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form> -->

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Results Table -->
        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>

                        <th style="text-align: center;">Bil</th>
                        <th>Butiran Pemohon</th>
                        <th>Butiran Perjalanan</th>
                        <th>Pemandu / Kenderaan </th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>

                    <?php $count = $offset + 1;
                    if ($result->num_rows > 0): ?>
                        <?php while ($record = $result->fetch_assoc()):

                            $isNew = (isset($record['created_at']) && strtotime($record['created_at']) >= strtotime('-3 days')) ? 'new-data' : '';
                            $statusClass = match (isset($record['kelulusan']) ? $record['kelulusan'] : 'BARU') {
                                'LULUS' => 'status-approved ',
                                'TIDAK LULUS' => 'status-rejected',
                                'KIV' => 'status-pending status-white-text',
                                'BARU' => 'status-new',
                                'BATAL' => 'status-batal',
                                default => ''
                            };
                        ?>
                            <tr data-id="<?= $record['id'] ?>">

                                <td style="text-align: center; font-size:15px;"><?= $count++ ?></td>
                                <td class="nama" style="font-size: 15px;">
                                    <strong><?= htmlspecialchars($record['pemohon']) ?></strong><br>
                                    <?= date('d/m/Y H:i', strtotime($record['tarikh_mohon'])) ?><br>
                                    <?= htmlspecialchars($record['nohp']) ?>
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

                                <?php if (!empty($record['no_plat'])) { ?>
                                    <td class="nama" style="font-size: 15px;">
                                        <strong><?= htmlspecialchars(stkPemanduNameUpper($record['namapemandu'])) ?></strong><br>
                                        <?= htmlspecialchars($record['no_plat']) ?><br>
                                        <?= htmlspecialchars($record['pengeluar']) ?> <?= htmlspecialchars($record['model']) ?>
                                    </td>
                                <?php } else { ?>
                                    <td class="nama" style="font-size: 15px;">
                                        <?= htmlspecialchars($record['pilihanJenis']) ?><br>

                                    </td>
                                <?php } ?>

                                <td>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($record['kelulusan']) ?>
                                    </span>
                                </td>

                                <td class="action-side">

                                    <a href="javascript:void(0)" class="action-btn2"
                                        data-id="<?= htmlspecialchars($record['id']) ?>"
                                        data-pemohon="<?= htmlspecialchars($record['pemohon']) ?>"
                                        data-tarikh_mohon="<?= htmlspecialchars($record['tarikh_mohon']) ?>"
                                        data-destinasi="<?= htmlspecialchars($record['destinasi']) ?>"
                                        data-tarikh_pergi="<?= htmlspecialchars($record['tarikh_pergi']) ?>"
                                        data-masa_pergi="<?= htmlspecialchars($record['masa_pergi']) ?>"
                                        data-tarikh_balik="<?= htmlspecialchars($record['tarikh_balik']) ?>"
                                        data-masa_balik="<?= htmlspecialchars($record['masa_balik']) ?>"
                                        data-status="<?= htmlspecialchars($statusClass) ?>"
                                        data-kelulusan="<?= htmlspecialchars($record['kelulusan'] ?? 'BARU') ?>"
                                        data-pemandu="<?= htmlspecialchars(stkPemanduNameUpper($record['namapemandu'])) ?>"
                                        data-kenderaan="<?= htmlspecialchars($record['no_plat']) ?>"
                                        data-pengeluar="<?= htmlspecialchars($record['pengeluar']) ?>"
                                        data-model="<?= htmlspecialchars($record['model']) ?>"
                                        data-bertolak="<?= htmlspecialchars($record['bertolak']) ?>"
                                        data-notelefon="<?= htmlspecialchars($record['notelefon']) ?>"
                                        data-pilihan-jenis="<?= htmlspecialchars($record['pilihanJenis']) ?>"
                                        title=" Lihat" onclick=" showViewModal(this)">

                                        <i class="fas fa-eye" style="color: #3498DB;"></i>
                                    </a>

                                    <a href="javascript:void(0)" id="editBtn-<?= $record['id'] ?>" class="action-btn2"
                                        data-id="<?= htmlspecialchars($record['id']) ?>"
                                        data-pemohon="<?= htmlspecialchars($record['pemohon']) ?>"
                                        data-tarikh_mohon="<?= htmlspecialchars($record['tarikh_mohon']) ?>"
                                        data-bertolak="<?= htmlspecialchars($record['bertolak']) ?>"
                                        data-destinasi="<?= htmlspecialchars($record['destinasi']) ?>"
                                        data-jenis_perjalanan="<?= htmlspecialchars($record['jenis_perjalanan']) ?>"
                                        data-tarikh_pergi="<?= htmlspecialchars($record['tarikh_pergi']) ?>"
                                        data-masa_pergi="<?= htmlspecialchars($record['masa_pergi']) ?>"
                                        data-tarikh_balik="<?= htmlspecialchars($record['tarikh_balik']) ?>"
                                        data-masa_balik="<?= htmlspecialchars($record['masa_balik']) ?>"
                                        data-tujuan_perjalanan="<?= htmlspecialchars($record['tujuan_perjalanan']) ?>"
                                        data-lain_tujuan="<?= htmlspecialchars($record['lain_tujuan']) ?>"
                                        data-bil_penumpang="<?= htmlspecialchars($record['bil_penumpang']) ?>"
                                        data-senarai_penumpang="<?= htmlspecialchars($record['senarai_penumpang']) ?>"
                                        data-negeri="<?= htmlspecialchars($record['negeri']) ?>"
                                        data-kelulusan="<?= htmlspecialchars($record['kelulusan']) ?>"
                                        data-pemandu="<?= htmlspecialchars($record['id_pemandu'] ?? '') ?>"
                                        data-jenis="<?= htmlspecialchars($record['jenis_id'] ?? '') ?>"
                                        data-kenderaan="<?= htmlspecialchars($record['kenderaan_id']) ?>"
                                        data-pilihan-jenis="<?= htmlspecialchars($record['pilihanJenis']) ?>"

                                        title="Edit"
                                        onclick="showEditModal(this)">

                                        <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                    </a>

                                    <a href="#" onclick="return deleteBooking(<?= $record['id'] ?>)" class="action-btn2"
                                        title="Padam">
                                        <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                    </a>

                                    <!-- <a href="#" onclick="showEmailModal(
                                           '<?= $record['id'] ?>', 
                                           '<?= htmlspecialchars($record['pemohon']) ?>', 
                                           '<?= htmlspecialchars($record['kelulusan']) ?>', 
                                           '<?= htmlspecialchars($record['destinasi']) ?>', 
                                           '<?= htmlspecialchars($record['tarikh_mohon']) ?>', 
                                           '<?= htmlspecialchars(getDriverName($conn, $record['id_pemandu'])) ?>', 
                                           '<?= htmlspecialchars($record['jenis_kenderaan'] ?? '') ?>'
                                       )" class="action-btn2 email-btn" title="Hantar Email">
                                        <i class="fas fa-envelope" style="color: #787878;"></i>
                                    </a> -->

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">Tiada rekod dijumpai.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <div class="pagination">
                <?php
                // Only show pagination if NOT searching AND total pages is greater than 1
                if (!$isSearching && $total_pages > 1):
                ?>
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?>" class="page-link page-nav"
                            title="Muka Pertama">
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
                <?php endif; ?>
            </div>
        </div>


    </div>
    </div>

    <div id="viewModal" class="modal2">
        <input type="hidden" id="modalRecordId">
        <div class="modal-content2">
            <div class="booking-card">
                <div class="booking-header">
                    <h2>Butiran Tempahan Kenderaan </h2>
                    <!-- <span class="booking-id">ID: <span id="displayIdText"></span></span> -->
                </div>
                <div class="booking-content">
                    <div class="info-grid">
                        <div class="info-group">
                            <div class="info-label">Pemohon</div>
                            <div class="info-value">
                                <i class="fas fa-user"></i>
                                <span id="displayPemohonText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Tarikh Memohon</div>
                            <div class="info-value">
                                <i class="fas fa-calendar-alt"></i>
                                <span id="displayTarikhMohonText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Lokasi Bertolak</div>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="displayBertolak"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Destinasi</div>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="displayDestinasiText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Tarikh & Masa Pergi</div>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="displayTarikhMasaPergi"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Tarikh & Masa Balik</div>
                            <div class="info-value">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="displayTarikhMasaBalik"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Status Kelulusan</div>
                            <div class="info-value">

                                <span class="status-badge " id="statusBadge">
                                    <i class="fas fa-circle-info"></i>
                                    <span id="displayKelulusan"></span>
                                </span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Pemandu</div>
                            <div class="info-value">
                                <i class="fas fa-id-card"></i>
                                <span id="displayNamaPemandu"></span>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-phone"></i>
                                <span id="displayTelefon"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Kenderaan</div>
                            <div class="info-value">
                                <i class="fas fa-ticket"></i>
                                <span id="displayPlat"></span>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-car"></i>
                                <span id="displayModel"></span>
                            </div>
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="button" onclick="closeViewModal()" class="btn btn-back">
                            <span><i class="fas fa-angle-left"></i> Kembali</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="emailModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <span class="close" onclick="closeEmailModal()">&times;</span>
                <h2 class="form-title">Hantar Email</h2>
                <form id="emailForm">
                    <input type="hidden" id="bookingId" name="bookingId">
                    <input type="hidden" id="pemohon" name="pemohon">
                    <input type="hidden" id="status" name="status">
                    <div class="form-group">
                        <label for="emailSubject">Subjek:</label>
                        <input type="text" id="emailSubject" name="subject" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="emailMessage">Mesej:</label>
                        <textarea id="emailMessage" name="message" rows="10" class="form-control"
                            style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"
                            readonly></textarea>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane"></i>Hantar
                            Email</button>
                        <button type="button" class="btn btn-cancel" onclick="closeEmailModal()"><i
                                class="fas fa-times"></i> Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal2">
        <input type="hidden" id="modalRecordId">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2 class="form-title">Butiran Tempahan Kenderaan</h2>
                </div>
                <form method="POST" action="" id="editForm" onsubmit="return confirmSubmit(event)">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" id="editdisplayId" name="id" readonly>
                    <div class="form-row">

                        <div class="form-group">
                            <label for="pemohon">Pemohon:</label>
                            <input type="text" id="editdisplayPemohon" name="pemohon" readonly>
                        </div>
                        <div class="form-group">
                            <label for="tarikh_mohon">Tarikh Momohon:</label>
                            <input type="text" id="editdisplayTarikhMohon" name="tarikh_mohon" readonly>
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label for="bertolak">Bertolak:</label>
                            <input type="text" id="editdisplayBertolak" name="bertolak" readonly>
                        </div>
                        <div class="form-group">
                            <label for="destinasi">Destinasi:</label>
                            <input type="text" id="editdisplayDestinasi" name="destinasi" readonly>
                        </div>
                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label for="negeri">Negeri:</label>
                            <input type="text" id="editdisplayNegeri" name="negeri" readonly>
                        </div>
                        <div class="form-group">
                            <label for="jenis_perjalanan">Jenis Perjalanan:</label>
                            <input type="text" id="editdisplayJenisPerjalanan" name="jenis_perjalanan" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tarikh_masa_pergi">Tarikh & Masa Pergi:</label>
                            <input type="text" id="editdisplayTarikhMasaPergi" name="tarikh_masa_pergi" readonly>
                        </div>
                        <div class="form-group">
                            <label for="tarikh_masa_balik">Tarikh & Masa Balik:</label>
                            <input type="text" id="editdisplayTarikhMasaBalik" name="tarikh_masa_balik" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tujuan_perjalanan">Tujuan Perjalanan:</label>
                            <input type="text" id="editdisplayTujuanPerjalanan" name="tujuan_perjalanan" readonly>
                        </div>
                        <div class="form-group">
                            <label for="lain_tujuan">Ulasan Perjalanan</label>
                            <input type="text" id="editdisplayLainTujuan" name="lain_tujuan" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bil_penumpang">Bilangan Penumpang:</label>
                            <input type="text" id="editdisplayBilPenumpang" name="bil_penumpang" readonly>
                        </div>
                        <div class="form-group">
                            <label for="jenisPilihan">Jenis Kenderaan</label>
                            <input type="text" id="editdisplayJenisPilihan" name="jenisPilihan" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="penumpang">Senarai Penumpang / No Telefon:</label>
                        <input type="text" id="editdisplaySenaraiPenumpang" name="penumpang" readonly>
                    </div>
                    <h3
                        style="margin-top: 25px; margin-bottom: 10px; padding-left: 15px; color: #2C3E50; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 8px;">
                        Kelulusan Penyelaras Kenderaan
                    </h3>

                    <div class="form-grid" style="display: flex; gap: 15px; margin-top: 10px; padding: 0 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label" for="kelulusan">Status Kelulusan</label>
                            <select name="kelulusan" id="editdisplayKelulusan" class="form-control" required>
                                <option value="BARU">BARU</option>
                                <option value="LULUS">LULUS</option>
                                <option value="TIDAK LULUS">TIDAK LULUS</option>
                                <option value="KIV">KIV</option>
                                <option value="BATAL">BATAL</option>
                            </select>
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label class="form-label" for="pemandu">Pemandu</label>
                            <select name="pemandu" id="editdisplayPemandu" class="form-control"
                                data-placeholder="Cari atau pilih pemandu">
                                <option value="">Belum ditentukan</option>
                                <?php
                                $drivers_sql = "SELECT id, namapemandu FROM tpemandu WHERE UPPER(TRIM(status)) = 'AKTIF' ORDER BY namapemandu ASC";
                                $drivers_result = $conn->query($drivers_sql);
                                while ($driver = $drivers_result->fetch_assoc()) {
                                    $selected = ($driver['id'] == $record['id_pemandu']) ? 'selected' : '';
                                    $optId = (int) $driver['id'];
                                    $optName = htmlspecialchars(stkPemanduNameUpper($driver['namapemandu']), ENT_QUOTES, 'UTF-8');
                                    echo "<option value=\"{$optId}\" {$selected}>{$optName}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- <div class="form-group">
                            <label for="bil_penumpang">Bilangan Penumpang:</label>
                            <input type="text" id="editdisplayJenis" name="bil_penumpang" readonly>
                        </div> -->


                    </div>

                    <div class="form-grid" style="display: flex; gap: 15px; margin-top: 10px; padding: 0 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label class="form-label" for="jenisKenderaan">Jenis Kenderaan</label>
                            <select name="jenis_id" id="editdisplayJenis" class="form-control">
                                <option value="">Belum ditentukan</option>

                                <?php
                                $jenis_sql = "SELECT id, jenis_kenderaan FROM ttempah_jenis ORDER BY jenis_kenderaan";
                                $jenis_result = $conn->query($jenis_sql);

                                while ($jenis = $jenis_result->fetch_assoc()) {

                                    $selected = ($jenis["id"] == $record['jenis_id']) ? 'selected' : '';
                                    $id = htmlspecialchars($jenis['id']);
                                    $name = htmlspecialchars($jenis['jenis_kenderaan']);

                                    echo "<option value='$id' $selected>$name</option>";
                                }
                                ?>

                            </select>
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label class="form-label" for="noPlat">No Plat</label>
                            <select name="no_plat" id="noPlat" class="form-control">
                                <option value="">Pilih jenis dahulu</option>
                            </select>
                        </div>

                        <!-- <div class="form-group" style="flex: 1;">
                            <label class="form-label" for="model">Model</label>
                            <input type="text" id="model" name="model" readonly >
                        </div> -->


                    </div>
                    <!-- <input type="hidden" name="id" value="<?= $id ?>"> -->
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>

                        <button type="button" onclick="closeEditModal()" class="btn btn-cancel">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
                <!-- <form method="POST" action="" id="editForm" onsubmit="return confirmSubmit()"> -->
            </div>

        </div>
    </div>

    <div id="addModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addModal')">&times;</span>

                    <h2 class="form-title">Borang Tempahan Kenderaan</h2>
                </div>

                <form id="addForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="insert">
                    <!-- First column -->


                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Bertolak*</label>
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

                    <!-- Empty space beside Jenis Perjalanan -->
                    <!-- <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-control" style="border:none;"></div>
                    </div> -->

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tarikh Pergi*</label>
                            <input type="date" id="tarikh_pergi" name="tarikh_pergi" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Masa Pergi*</label>
                            <input type="time" id="Masa_Pergi" name="Masa_Pergi" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tarikh Balik*</label>
                            <input type="date" id="tarikh_balik" name="tarikh_balik" class="form-control" min="<?= date('Y-m-d') ?>" required>
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
        let selectedKenderaan = null;
        let selectedNoPlat = '';


        $('#editdisplayJenis').on('change', function() {
            var jenis_id = $(this).val();

            fetch('get_plat.php?jenis_id=' + jenis_id)
                .then(response => response.text())
                .then(data => {
                    $('#noPlat').html(data);

                    // ✅ Set selected value AFTER options loaded
                    if (selectedNoPlat) {
                        $('#noPlat').val(selectedNoPlat).trigger('change');
                    }
                });
        });
        $('#noPlat').on('change', function() {
            var selected = $(this).find(':selected');
            var model = selected.data('model');

            $('#model').val(model || '');

        });


        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';

            document.addEventListener('click', function(event) {
                const wrapper = document.getElementById('notificationWrapper');
                const dropdown = document.getElementById('notificationDropdown');

                // Kalau klik BUKAN dalam wrapper
                if (!wrapper.contains(event.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }

        function validateSearch() {
            const id = document.querySelector('input[name="id"]').value.trim();
            const pemohon = document.querySelector('input[name="pemohon"]').value.trim();
            const destinasi = document.querySelector('input[name="destinasi"]').value.trim();
            const kelulusan = document.querySelector('select[name="kelulusan"]').value.trim();

            // If all fields are empty
            if (!id && !pemohon && !destinasi && !kelulusan) {
                window.location.href = 'STK.php';
                return false;
            }

            return true;
        }

        const modal = document.getElementById('viewModal');

        function showViewModal(el) {
            const id = el.dataset.id;
            const pemohon = el.dataset.pemohon;
            const destinasi = el.dataset.destinasi;
            const tarikhMohon = el.dataset.tarikh_mohon;
            const namaPemandu = el.dataset.pemandu;
            const kenderaan = el.dataset.kenderaan;
            const tarikhPergi = el.dataset.tarikh_pergi;
            const masa_pergi = el.dataset.masa_pergi;
            const tarikhBalik = el.dataset.tarikh_balik;
            const masaBalik = el.dataset.masa_balik;
            const kelulusan = el.dataset.kelulusan;
            const status = el.dataset.status;
            const pengeluar = el.dataset.pengeluar;
            const model = el.dataset.model;
            const bertolak = el.dataset.bertolak;
            const notelefon = el.dataset.notelefon;
            const pilihanJenis = el.dataset.pilihanJenis;

            console.log(notelefon);

            document.getElementById('displayPemohonText').textContent = pemohon;
            document.getElementById('displayDestinasiText').textContent = destinasi;
            document.getElementById('displayTarikhMohonText').textContent = tarikhMohon;
            document.getElementById('displayNamaPemandu').textContent = namaPemandu;
            document.getElementById('displayPlat').textContent = (kenderaan) ? kenderaan : pilihanJenis;
            document.getElementById('displayTarikhMasaPergi').textContent = (tarikhPergi && masa_pergi) ? tarikhPergi + " " + masa_pergi : "";
            document.getElementById('displayTarikhMasaBalik').textContent = (tarikhBalik && masaBalik) ? tarikhBalik + " " + masaBalik : "";
            document.getElementById('displayKelulusan').textContent = kelulusan;
            document.getElementById('displayModel').textContent = (pengeluar && model) ? pengeluar + " " + model : ""
            document.getElementById('displayBertolak').textContent = bertolak;
            document.getElementById('displayTelefon').textContent = notelefon;


            document.getElementById('viewModal').style.display = 'flex';

            let badge = document.getElementById("statusBadge");

            badge.classList.remove("status-approved", "status-rejected", "status-pending");

            if (kelulusan === "LULUS") {
                badge.classList.add("status-approved");
            } else if (kelulusan === "TIDAK LULUS") {
                badge.classList.add("status-rejected");
            } else if (kelulusan === "KIV") {
                badge.classList.add("status-pending");
            } else if (kelulusan === "BARU") {
                badge.classList.add("status-new");
            }
        }

        function closeViewModal() {
            const modal = document.getElementById('viewModal');
            modal.style.display = 'none';
        }

        function deleteBooking(id) {
            Swal.fire({
                title: 'Adakah anda pasti?',
                text: `Anda akan memadam tempahan ini.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Padam!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sila tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send delete request
                    fetch(`delete_stk.php?id=${encodeURIComponent(id)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Berjaya!',
                                    text: data.message || 'Rekod berjaya dipadam',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Ralat!',
                                    text: data.message || 'Gagal memadam rekod',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Ralat Sistem!',
                                text: 'Sila cuba lagi atau hubungi admin',
                                icon: 'error'
                            });
                        });
                }
            });
        }

        function showEmailModal(id, pemohon, status, destinasi, tarikhMohon, namaPemandu, jenisKenderaan) {
            const modal = document.getElementById('emailModal');
            const subjectInput = document.getElementById('emailSubject');
            const messageInput = document.getElementById('emailMessage');

            // Set hidden inputs
            document.getElementById('bookingId').value = id;
            document.getElementById('pemohon').value = pemohon;
            document.getElementById('status').value = status;

            // Set email subject
            subjectInput.value = `Status Tempahan Kenderaan - ${status.toUpperCase()}`;

            // Build email message
            let message = `Kepada ${pemohon},\n\n`;
            message += `Status tempahan kenderaan anda adalah seperti berikut:\n\n`;
            message += `Nama Pemohon: ${pemohon}\n`;
            message += `Tarikh Mohon: ${tarikhMohon}\n`;
            message += `Destinasi: ${destinasi}\n`;
            message += `Status: ${status}\n`;

            if (status === 'LULUS') {
                message += `Pemandu: ${namaPemandu || 'Belum ditentukan'}\n`;
                message += `Kenderaan: ${jenisKenderaan || 'Belum ditentukan'}\n\n`;
                message += `Tempahan kenderaan anda telah diluluskan.`;
            } else if (status === 'TIDAK LULUS') {
                message += `\nHarap maklum, tempahan kenderaan anda tidak dapat diluluskan pada kali ini.`;
            } else if (status === 'KIV') {
                message += `\nTempahan anda sedang dalam proses pertimbangan.`;
            } else if (status === 'BATAL') {
                message += `\nTempahan anda telah dibatalkan.`;
            }

            message += `\n\nTerima kasih.`;
            messageInput.value = message;

            modal.style.display = "block";
        }

        function closeEmailModal() {
            document.getElementById('emailModal').style.display = "none";
        }

        const modalEdit = document.getElementById('editModal');

        window.PEMANDU_LEAVE = window.PEMANDU_LEAVE || [];

        function stkNormalizeDateStr(s) {
            if (!s || typeof s !== 'string') return null;
            const t = s.trim();
            if (!t || t.toLowerCase() === 'none') return null;
            const ymd = t.substring(0, 10);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(ymd)) return null;
            return ymd;
        }

        function stkTripRangeFromDataset(tarikhPergi, tarikhBalik) {
            const tripStart = stkNormalizeDateStr(tarikhPergi || '');
            if (!tripStart) return null;
            const endRaw = stkNormalizeDateStr(tarikhBalik || '');
            const tripEnd = endRaw || tripStart;
            if (tripEnd < tripStart) {
                return {
                    tripStart: tripEnd,
                    tripEnd: tripStart
                };
            }
            return {
                tripStart: tripStart,
                tripEnd: tripEnd
            };
        }

        function stkLeaveOverlapsTrip(pemanduId, tripStart, tripEnd) {
            const id = parseInt(pemanduId, 10);
            if (!id || !tripStart || !tripEnd) return false;
            const rows = window.PEMANDU_LEAVE || [];
            for (let i = 0; i < rows.length; i++) {
                const r = rows[i];
                if (r.pemandu_id !== id) continue;
                const ls = stkNormalizeDateStr(String(r.start_date || ''));
                const le = stkNormalizeDateStr(String(r.end_date || ''));
                if (!ls || !le) continue;
                if (ls <= tripEnd && le >= tripStart) return true;
            }
            return false;
        }

        function stkApplyPemanduLeaveDisabled(pemanduSelect, tarikhPergi, tarikhBalik, currentAssignedId) {
            const range = stkTripRangeFromDataset(tarikhPergi, tarikhBalik);
            const currentStr = currentAssignedId !== undefined && currentAssignedId !== null ? String(currentAssignedId) :
                '';

            for (let i = 0; i < pemanduSelect.options.length; i++) {
                const opt = pemanduSelect.options[i];
                opt.disabled = false;
                opt.removeAttribute('title');
                if (!opt.value) continue;
                if (!range) continue;
                const onLeave = stkLeaveOverlapsTrip(opt.value, range.tripStart, range.tripEnd);
                if (onLeave && opt.value !== currentStr) {
                    opt.disabled = true;
                    opt.title = 'Pemandu sedang bercuti pada tempoh perjalanan ini';
                }
            }
        }

        function showEditModal(el) {
            const id = el.dataset.id;
            const pemohon = el.dataset.pemohon;
            const tarikhMohon = el.dataset.tarikh_mohon;
            const bertolak = el.dataset.bertolak;
            const destinasi = el.dataset.destinasi;
            const jenisPerjalanan = el.dataset.jenis_perjalanan;
            const tarikhPergi = el.dataset.tarikh_pergi;
            const masaPergi = el.dataset.masa_pergi;
            const tarikhBalik = el.dataset.tarikh_balik;
            const masaBalik = el.dataset.masa_balik;
            const tujuanPerjalanan = el.dataset.tujuan_perjalanan;
            const lainTujuan = el.dataset.lain_tujuan;
            const bilPenumpang = el.dataset.bil_penumpang;
            const senaraiPenumpang = el.dataset.senarai_penumpang;
            const negeri = el.dataset.negeri;
            const kelulusan = el.dataset.kelulusan;
            const pemandu = el.dataset.pemandu;
            const kenderaan = el.dataset.kenderaan;
            const jenis = el.dataset.jenis;
            const pilihanJenis = el.dataset.pilihanJenis;

            document.getElementById('editdisplayId').value = id;
            document.getElementById('editdisplayPemohon').value = pemohon;
            document.getElementById('editdisplayTarikhMohon').value = tarikhMohon;
            document.getElementById('editdisplayBertolak').value = bertolak ? bertolak : "-";
            document.getElementById('editdisplayDestinasi').value = destinasi;
            document.getElementById('editdisplayJenisPerjalanan').value = jenisPerjalanan;
            document.getElementById('editdisplayTarikhMasaPergi').value = (tarikhPergi && masaPergi) ? tarikhPergi + " " +
                masaPergi : "-";
            document.getElementById('editdisplayTarikhMasaBalik').value = (tarikhBalik && masaBalik) ? tarikhBalik + " " +
                masaBalik : "-";
            document.getElementById('editdisplayTujuanPerjalanan').value = tujuanPerjalanan;
            document.getElementById('editdisplayLainTujuan').value = lainTujuan;
            document.getElementById('editdisplayBilPenumpang').value = bilPenumpang;
            document.getElementById('editdisplaySenaraiPenumpang').value = senaraiPenumpang;
            document.getElementById('editdisplayNegeri').value = negeri;
            document.getElementById('editdisplayJenisPilihan').value = pilihanJenis;
            // document.getElementById('editdisplayJenis').value = jenis;
            const kelulusanSelect = document.getElementById('editdisplayKelulusan');
            const pemanduSelect = document.getElementById('editdisplayPemandu');
            selectedNoPlat = kenderaan || '';
            const jenisSelect = document.getElementById('editdisplayJenis');

            // if (pemanduSelect) pemanduSelect.value = pemandu;

            if (pemanduSelect) {
                stkApplyPemanduLeaveDisabled(pemanduSelect, tarikhPergi, tarikhBalik, pemandu);
                if (window.jQuery && $(pemanduSelect).data('select2')) {
                    $(pemanduSelect).val(pemandu || '').trigger('change');
                } else {
                    pemanduSelect.value = pemandu || '';
                }

                const range = stkTripRangeFromDataset(tarikhPergi, tarikhBalik);
                if (range && pemandu && stkLeaveOverlapsTrip(pemandu, range.tripStart, range.tripEnd)) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Perhatian',
                        text: 'Pemandu semasa mempunyai rekod cuti yang bertindih dengan tempoh perjalanan ini. Sila tukar kepada pemandu lain jika perlu.',
                    });
                }
            }

            // if (kenderaanSelect) {
            //     if (window.jQuery && $(kenderaanSelect).data('select2')) {
            //         $(kenderaanSelect).val(kenderaan || '').trigger('change');
            //     } else {
            //         kenderaanSelect.value = kenderaan || '';
            //     }
            // }
            if (jenisSelect) {
                if (window.jQuery && $(jenisSelect).data('select2')) {
                    $(jenisSelect).val(jenis || '').trigger('change');
                } else {
                    jenisSelect.value = jenis || '';
                    $('#editdisplayJenis').trigger('change'); // 🔥 IMPORTANT
                }
            }

            if (kelulusanSelect) {
                if (window.jQuery && $(kelulusanSelect).data('select2')) {
                    $(kelulusanSelect).val(kelulusan || '').trigger('change');
                } else {
                    kelulusanSelect.value = kelulusan || '';
                }
            }

            console.log(kenderaan);
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            const pemanduSelect = document.getElementById('editdisplayPemandu');
            if (window.jQuery && pemanduSelect && $(pemanduSelect).data('select2')) {
                $(pemanduSelect).select2('close');
            }

            const jenisSelect = document.getElementById('editDisplayJenis');
            if (window.jQuery && jenisSelect && $(jenisSelect).data('select2')) {
                $(jenisSelect).select2('close');
            }

            modal.style.display = 'none';
        }

        function confirmSubmit(event) {
            event.preventDefault(); // stop the form from submitting immediately

            Swal.fire({
                title: 'Sahkan',
                text: 'Adakah anda pasti untuk menyimpan perubahan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirms, submit the form
                    event.target.submit();
                }
                // If cancelled, do nothing (modal closes automatically)
            });

            return false; // prevent default form submission
        }

        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        document.addEventListener("DOMContentLoaded", function() {
            const now = new Date();

            const date = now.toISOString().split('T')[0];
            const time = now.toLocaleTimeString('en-GB');

            document.getElementById("tarikh_mohon").value = date + " " + time;
        });

        document.addEventListener('DOMContentLoaded', function() {
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
            radioButtons.forEach(function(radio) {
                radio.addEventListener('change', toggleReturnFields);
            });

            // Initialize on page load
            if (document.querySelector('input[name="jenis_perjalanan"]:checked')) {
                toggleReturnFields();
            }
        });
        document.getElementById('tarikh_balik').addEventListener('change', function() {
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

            let hasValue = false;

            Array.from(form.elements).forEach(el => {
                if (['pemohon', 'tarikh_mohon'].includes(el.name)) return;
                if (['button', 'submit', 'hidden'].includes(el.type)) return;

                if (el.type === 'radio' || el.type === 'checkbox') {
                    if (el.checked) hasValue = true;
                } else if (el.value && el.value.trim() !== '') {
                    hasValue = true;
                }
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

                    form.reset(); // 🔥 much cleaner

                    // restore readonly fields if needed
                    document.getElementById('pemohon').value = "<?= htmlspecialchars($_SESSION['username']) ?>";
                    document.getElementById('tarikh_mohon').value = document.getElementById('tarikh_mohon').value;

                    modal.style.display = 'none';

                    Swal.fire(
                        'Dibatalkan!',
                        'Borang telah dikosongkan.',
                        'success'
                    );
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {

            if (window.jQuery && $.fn.select2) {
                const $pemandu = $('#editdisplayPemandu');
                if ($pemandu.length) {
                    $pemandu.select2({
                        width: '100%',
                        dropdownParent: $('#editForm'),
                        placeholder: 'Cari atau pilih pemandu',
                        allowClear: false,
                        language: {
                            noResults: function() {
                                return 'Tiada pemandu dijumpai';
                            },
                            searching: function() {
                                return 'Mencari…';
                            },
                        },
                    });
                    $pemandu.on('select2:open', function() {
                        requestAnimationFrame(function() {
                            $(window).trigger('resize');
                        });
                    });
                }
            }

            if (window.jQuery && $.fn.select2) {
                const $jenis = $('#editdisplayJenis');
                if ($jenis.length) {
                    $jenis.select2({
                        width: '100%',
                        dropdownParent: $('#editForm'),
                        placeholder: 'Cari atau pilih kenderaan',
                        allowClear: false,
                        language: {
                            noResults: function() {
                                return 'Tiada kenderaan dijumpai';
                            },
                            searching: function() {
                                return 'Mencari…';
                            },
                        },
                    });
                    $jenis.on('select2:open', function() {
                        requestAnimationFrame(function() {
                            $(window).trigger('resize');
                        });
                    });
                }
            }

            if (window.jQuery && $.fn.select2) {
                const $kelulusan = $('#editdisplayKelulusan');
                if ($kelulusan.length) {
                    $kelulusan.select2({
                        width: '100%',
                        dropdownParent: $('#editForm'),
                        placeholder: 'Cari atau pilih kelulusan',
                        allowClear: false,
                        language: {
                            noResults: function() {
                                return 'Tiada kelulusan dijumpai';
                            },
                            searching: function() {
                                return 'Mencari…';
                            },
                        },
                    });
                    $kelulusan.on('select2:open', function() {
                        requestAnimationFrame(function() {
                            $(window).trigger('resize');
                        });
                    });
                }
            }

            if (window.jQuery && $.fn.select2) {
                const $noPlat = $('#noPlat');
                if ($noPlat.length) {
                    $noPlat.select2({
                        width: '100%',
                        dropdownParent: $('#editForm'),
                        placeholder: 'Pilih Jenis dahulu',
                        allowClear: false,
                        language: {
                            noResults: function() {
                                return 'Tiada kenderaan dijumpai';
                            },
                            searching: function() {
                                return 'Mencari…';
                            },
                        },
                    });
                    $noPlat.on('select2:open', function() {
                        requestAnimationFrame(function() {
                            $(window).trigger('resize');
                        });
                    });
                }
            }

            // if (window.jQuery && $.fn.select2) {
            //     const $model = $('#model');
            //     if ($model.length) {
            //         $model.select2({
            //             width: '100%',
            //             dropdownParent: $('#editForm'),
            //             placeholder: 'Pilih Jenis dahulu',
            //             allowClear: false,
            //             language: {
            //                 noResults: function () {
            //                     return 'Tiada pemandu dijumpai';
            //                 },
            //                 searching: function () {
            //                     return 'Mencari…';
            //                 },
            //             },
            //         });
            //         $model.on('select2:open', function () {
            //             requestAnimationFrame(function () {
            //                 $(window).trigger('resize');
            //             });
            //         });
            //     }
            // }

            <?php if (!empty($swalMessage)): ?>
                Swal.fire({
                    title: '<?php echo $swalType === "success" ? "Berjaya!" : "Gagal!"; ?>',
                    text: '<?php echo addslashes($swalMessage); ?>',
                    icon: '<?php echo $swalType; ?>',
                }).then(() => {
                    <?php if ($swalType === "success"): ?>
                        window.location.href = 'STK.php';
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