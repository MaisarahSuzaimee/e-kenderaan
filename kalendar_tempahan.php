<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Add these require statements
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();
require 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: error.html");
    exit();
}

// Get current month and year from URL parameters, default to current month/year if not set
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate and adjust month/year if out of range
if ($month > 12) {
    $month = 1;
    $year++;
} elseif ($month < 1) {
    $month = 12;
    $year--;
}

// Calculate previous and next month/year
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Format month name in Malay
function getMalayMonthName($month)
{
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Mac',
        4 => 'April',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Julai',
        8 => 'Ogos',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Disember'
    ];
    return $months[$month];
}

// Fetch bookings for the selected month
try {
    $firstDayOfMonth = sprintf("%04d-%02d-01", $year, $month);
    $lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));

    $sql = "SELECT t.*, 
            COALESCE(tj.jenis_kenderaan, 'Tidak ditetapkan') as jenis_pilihan,
            COALESCE(p.namapemandu, 'Tiada Pemandu') as nama_pemandu,
            p.notelefon, kj.no_plat, kj.model, kj.pengeluar, tj2.jenis_kenderaan  
            FROM tempahan_kenderaan t 
            LEFT JOIN ttempah_jenis tj ON t.id_pilihanJenis  = tj.id
            LEFT JOIN tpemandu p ON t.id_pemandu = p.id
            left join kenderaan_jabatan kj on t.id_kenderaan = kj.id
            left join ttempah_jenis tj2 on kj.id_jenis = tj2.id
            WHERE t.tarikh_pergi BETWEEN  ? AND ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$firstDayOfMonth, $lastDayOfMonth]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    $bookings = array();
    $_SESSION['error'] = "Ralat sistem telah berlaku. Sila cuba sebentar lagi.";
}

// Group bookings by date
$bookingsByDate = [];
foreach ($bookings as $booking) {
    $date = date('j', strtotime($booking['tarikh_pergi']));
    if (!isset($bookingsByDate[$date])) {
        $bookingsByDate[$date] = [];
    }
    $bookingsByDate[$date][] = $booking;
}

function getAdminData($conn)
{
    $stmt = $conn->prepare("SELECT nama, email FROM penggunajkn WHERE role = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}
$admins = getAdminData($conn);
if (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'insert'
) {

    $TEST_MODE = false; // <-- set to true to test without sending email

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
        $masaPergi = $_POST['masa_pergi'] ?? '';
        $tarikhBalik = !empty($_POST['tarikh_balik']) ? $_POST['tarikh_balik'] : null;
        $masaBalik = !empty($_POST['masa_balik']) ? $_POST['masa_balik'] : null;
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
            (pemohon, user_id, bertolak, destinasi, negeri, jenis_perjalanan, tarikh_pergi, masa_pergi, tarikh_balik, masa_balik, tujuan_perjalanan, lain_tujuan, bil_penumpang, senarai_penumpang, jenis_kenderaan) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $pemohon,
            $user_id,
            // $tarikhMohon,
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
        // $superadminEmail = "sistem.kdh@moh.gov.my";
        $superadminEmail = "msrhszm@gmail.com";
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalendar Tempahan | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="CSS/STK2.css" />
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/kalendar_tempahan.css">
    <link rel="stylesheet" href="CSS/jadual_pemandu.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />


</head>

<body>
    <?= include 'dashboard2.php' ?>
    <div class="dashboard jadual-pemandu-page">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Kalendar Tempahan</h1>
                <div class="left-actions">
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>

            <div class="calendar-container jadual-card">

                <div class="calendar-legend">

                    <span class="legend-chip" style="color:#6b7280;font-weight:400;">
                        <i class="fas fa-calendar-day" style="opacity:.7;"></i> Klik pada tarikh untuk membuat tempahan baru
                    </span>
                    <span class="legend-chip" style="color:#6b7280;font-weight:400;">
                        <i class="fas fa-mouse-pointer" style="opacity:.7;"></i> Klik pada tempahan untuk melihat butiran
                    </span>

                </div>

                <div class="calendar-navigation" style="margin-top: 2rem;">
                    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-primary">
                        <i class="fas fa-chevron-left"></i> Bulan Sebelum
                    </a>
                    <h3><?= getMalayMonthName($month) . ' ' . $year ?></h3>
                    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-primary">
                        Bulan Seterus <i class="fas fa-chevron-right"></i>
                    </a>
                </div>

                <table class="calendar">
                    <thead>
                        <tr>

                            <th style="width: 14.28%;">Ahad</th>
                            <th style="width: 14.28%;">Isnin</th>
                            <th style="width: 14.28%;">Selasa</th>
                            <th style="width: 14.28%;">Rabu</th>
                            <th style="width: 14.28%;">Khamis</th>
                            <th style="width: 14.28%;">Jumaat</th>
                            <th style="width: 14.28%;">Sabtu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get the first day of the month
                        $firstDay = mktime(0, 0, 0, $month, 1, $year);
                        $daysInMonth = date('t', $firstDay);
                        $startDay = date('w', $firstDay);

                        $currentDay = 1;
                        $today = date('j');
                        $currentMonth = date('n');
                        $currentYear = date('Y');

                        for ($i = 0; $i < 6; $i++) {
                            echo "<tr>";
                            for ($j = 0; $j < 7; $j++) {
                                if (($i == 0 && $j < $startDay) || ($currentDay > $daysInMonth)) {
                                    echo "<td class='empty'></td>";
                                } else if ($currentDay <= $daysInMonth) {
                                    $isToday = ($currentDay == $today && $month == $currentMonth && $year == $currentYear);
                                    $dateClass = $isToday ? 'today' : '';

                                    // Format the date for the URL
                                    $formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);

                                    echo "<td class='$dateClass'>";
                                    // Make the date number clickable
                                    echo '<div class="date-clickable" 
                                    data-date="' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($currentDay, 2, '0', STR_PAD_LEFT) . '"
                                    onclick="openAddTBModal(this)">';
                                    echo "<span class='date-number'>$currentDay</span>";
                                    echo "</div>";

                                    // Display bookings for this date
                                    if (isset($bookingsByDate[$currentDay])) {
                                        foreach ($bookingsByDate[$currentDay] as $booking) {
                                            $status = htmlspecialchars($booking['kelulusan'] ?? 'Pending');
                                            $statusClass = strtolower(str_replace(' ', '', $status));

                                            echo "<div class='booking-event-$statusClass' 
                                                    data-id='" . htmlspecialchars($booking['id']) . "'
                                                    data-tarikh_mohon='" . htmlspecialchars($booking['tarikh_mohon']) . "'
                                                    data-pemohon='" . htmlspecialchars($booking['pemohon']) . "'
                                                    data-destinasi='" . htmlspecialchars($booking['destinasi']) . "'
                                                    data-bertolak='" . htmlspecialchars($booking['bertolak']) . "'
                                                    data-tarikh_pergi='" . htmlspecialchars($booking['tarikh_pergi']) . "'
                                                    data-masa_pergi='" . htmlspecialchars($booking['masa_pergi']) . "'
                                                    data-tarikh_balik='" . htmlspecialchars($booking['tarikh_balik']) . "'
                                                    data-masa_balik='" . htmlspecialchars($booking['masa_balik']) . "'
                                                    data-kenderaan='" . htmlspecialchars($booking['jenis_kenderaan']) . "'
                                                    data-kelulusan='" . htmlspecialchars($booking['kelulusan']) . "'
                                                    data-pemandu='" . htmlspecialchars($booking['nama_pemandu']) . "'
                                                    data-plat='" . htmlspecialchars($booking['no_plat']) . "'
                                                    data-model='" . htmlspecialchars($booking['model']) . "'
                                                    data-pengeluar='" . htmlspecialchars($booking['pengeluar']) . "'
                                                    data-notelefon='" . htmlspecialchars($booking['notelefon']) . "'
                                                    onclick='showViewModal(this)'>
                                                    <span class='time'>" .
                                                date('H:i', strtotime($booking['masa_pergi'])) .
                                                "</span>
                                                      <span class='details'>" . "<strong>" .
                                                htmlspecialchars($booking['no_plat'] ?? $booking['jenis_pilihan']) . " </strong> - " .
                                                htmlspecialchars($booking['nama_pemandu']) .
                                                "</span>
                                                  </div>";
                                        }
                                    }

                                    echo "</td>";
                                    $currentDay++;
                                }
                            }
                            echo "</tr>";
                            if ($currentDay > $daysInMonth)
                                break;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="viewModal" class="modal2">
        <div class="modal-content2">
            <div class="booking-card">
                <div class="booking-header">
                    <h2>Butiran Tempahan Kenderaan </h2>
                    <span class="booking-id">ID: <span id="displayIdText"></span></span>
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
                                <span id="displayBertolakText"></span>
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
                                <!-- <span id="modalNotelefonText"></span> -->
                            </div>
                            <div class="info-value">
                                <i class="fas fa-phone"></i>
                                <!-- <span id="modalPemanduText"></span> -->
                                <span id="displayNoTelefonText"></span>
                            </div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Kenderaan</div>
                            <div class="info-value">
                                <i class="fas fa-ticket"></i>
                                <span id="displayJenisKenderaan"></span>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-car"></i>
                                <span id="modalPengeluarText"></span>

                            </div>
                        </div>
                    </div>




                    <div class="button-group">
                        <!-- <button type="button" class="btn btn-edit" onclick="triggerEditFromView()">
                            <span><i class="fas fa-edit"></i> Kembali</span>
                        </button> -->
                        <button type="button" onclick="closeViewModal()" class="btn btn-back">
                            <span><i class="fas fa-angle-left"></i> Kembali</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="addTBModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addTBModal')">&times;</span>

                    <h2 class="form-title">Borang Tempahan Kenderaan</h2>
                </div>

                <form id="addTBForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="action" value="insert">
                    <!-- First column -->
                    <!-- <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tarikh Memohon*</label>
                            <input type="text" id="tarikh_mohon" name="tarikh_mohon" class="form-control2" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pemohon</label>
                            <input type="text" id="pemohon" name="pemohon" class="form-control2"
                                value="<?= htmlspecialchars($_SESSION['username']) ?>" readonly>
                        </div>
                    </div> -->

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
                            <input type="time" id="masa_pergi" name="masa_pergi" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Tarikh Balik*</label>
                            <input type="date" id="tarikh_balik" name="tarikh_balik" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>


                        <div class="form-group">
                            <label class="form-label">Masa Balik*</label>
                            <input type="time" id="masa_balik" name="masa_balik" class="form-control" required>
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
                            <label class="form-label">Ulasan Perjalanan</label>
                            <input type="text" id="lain_tujuan" name="lain_tujuan" class="form-control">
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
                                        echo '<option value="' . htmlspecialchars($kenderaan['jenis_kenderaan']) . '">' .
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
                            <label class="form-label2">Senarai Penumpang*</label>
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
        function showViewModal(el) {
            const id = el.dataset.id;
            const pemohon = el.dataset.pemohon;
            const destinasi = el.dataset.destinasi;
            const bertolak = el.dataset.bertolak;
            const tarikhMohon = el.dataset.tarikh_mohon;
            const namaPemandu = el.dataset.pemandu;
            const kenderaan = el.dataset.kenderaan;
            const tarikhPergi = el.dataset.tarikh_pergi;
            const masa_pergi = el.dataset.masa_pergi;
            const tarikhBalik = el.dataset.tarikh_balik;
            const masaBalik = el.dataset.masa_balik;
            const kelulusan = el.dataset.kelulusan;
            const noPlat = el.dataset.plat;
            const model = el.dataset.model;
            const pengeluar = el.dataset.pengeluar;
            const noTelefon = el.dataset.notelefon;

            document.getElementById('displayIdText').textContent = id;
            document.getElementById('displayPemohonText').textContent = pemohon;
            document.getElementById('displayDestinasiText').textContent = destinasi;
            document.getElementById('displayBertolakText').textContent = bertolak;
            document.getElementById('displayTarikhMohonText').textContent = tarikhMohon;
            document.getElementById('displayNamaPemandu').textContent = namaPemandu;
            document.getElementById('displayJenisKenderaan').textContent = noPlat;
            document.getElementById('displayTarikhMasaPergi').textContent = (tarikhPergi && masa_pergi) ? tarikhPergi + " " + masa_pergi : "-";
            document.getElementById('displayTarikhMasaBalik').textContent = (tarikhBalik && masaBalik) ? tarikhBalik + " " + masaBalik : "-";
            document.getElementById('displayKelulusan').textContent = kelulusan;
            document.getElementById('modalPengeluarText').textContent = pengeluar + " " + model;
            document.getElementById('displayNoTelefonText').textContent = noTelefon;

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

        function openAddTBModal(element) {

            const selectedDate = element.dataset.date;

            // Get today (YYYY-MM-DD)
            const today = new Date().toISOString().split('T')[0];

            if (selectedDate < today) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak dibenarkan',
                    text: 'Anda tidak boleh membuat tempahan untuk tarikh yang telah lepas.'
                });
                return; // ❌ stop here
            }

            // ✅ allow if today or future
            document.getElementById("tarikh_pergi").value = selectedDate;
            openModal('addTBModal');
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
            const masaBalik = document.getElementById('masa_balik');

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
            const modal = document.getElementById('addTBModal');
            const form = document.getElementById('addTBForm');

            let hasValue = false;

            Array.from(form.elements).forEach(el => {
                if (['pemohon', 'tarikh_mohon', 'tarikh_pergi'].includes(el.name)) return;
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
            <?php if (!empty($swalMessage)): ?>
                Swal.fire({
                    title: '<?php echo $swalType === "success" ? "Berjaya!" : "Gagal!"; ?>',
                    text: '<?php echo addslashes($swalMessage); ?>',
                    icon: '<?php echo $swalType; ?>',
                }).then(() => {
                    <?php if ($swalType === "success"): ?>
                        window.location.href = 'Kalendar_tempahan.php';
                    <?php endif; ?>
                });
            <?php endif; ?>
        });
    </script>

</body>

</html>