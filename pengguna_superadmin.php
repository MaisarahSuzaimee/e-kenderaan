<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);
session_start();
require 'config.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

  function generatePassword($length = 10)
  {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%'), 0, $length);
  }
  if (isset($_POST['registerBtn'])) {
    $nokp = trim($_POST['nokp']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $idptj = isset($_POST['idptj']) ? (int) $_POST['idptj'] : null;
    $bahagian = isset($_POST['bahagian']) ? (int) $_POST['bahagian'] : null;
    $unit = isset($_POST['unit']) ? (int) $_POST['unit'] : null;
    $jawatan = isset($_POST['jawatan']) ? trim($_POST['jawatan']) : '';
    $gred = isset($_POST['gred']) ? (int) $_POST['gred'] : null;
    $nohp = trim($_POST['nohp']);
    $peranan = trim($_POST['peranan']);
    $status = trim($_POST['status']);
    // $password = trim($_POST['password']);
    $plainPassword = generatePassword(10); // 🔥 generate random
    $password = password_hash($plainPassword, PASSWORD_DEFAULT);


    // Debug: Log the registration attempt
    error_log("Registering new user: $nama with nokp: $nokp");

    // Validate required fields
    if (empty($nokp) || empty($nama) || empty($email) || empty($password)) {
      $message = "Error: Sila isi semua ruangan yang wajib.";
      $messageColor = "red";
    } else {
      // Check if nokp already exists
      $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM penggunajkn WHERE nokp = ?");
      $checkStmt->execute([$nokp]);
      $userExists = ($checkStmt->fetchColumn() > 0);

      if ($userExists) {
        $message = "No KP ini telah didaftarkan. Sila gunakan No KP yang lain.";
        $messageColor = "red";
      } else {
        // Insert user into database
        try {
          // Store password as plain text to match login.php behavior
          $stmt = $pdo->prepare("INSERT INTO penggunajkn (nokp, nama, email, idptj, bahagian, unit, jawatan, gred, nohp, password, role, status, must_change_password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

          $result = $stmt->execute([$nokp, $nama, $email, $idptj, $bahagian, $unit, $jawatan, $gred, $nohp, $password, $peranan, $status]);

          if ($result) {


            $mail = new PHPMailer(true);

            try {
              // Server settings
              $mail->isSMTP();
              $mail->Host = 'smtp.gmail.com';
              $mail->SMTPAuth = true;
              $mail->Username = 'msrhszm@gmail.com'; // 🔥 your email
              $mail->Password = 'miar lgbv cftw jhzt';    // 🔥 NOT normal password
              $mail->SMTPSecure = 'tls';
              $mail->Port = 587;

              // Sender & receiver
              $mail->setFrom('msrhszm@gmail.com', 'Sistem Tempahan Kenderaan');
              $mail->addAddress($email, $nama);

              // Content
              $mail->isHTML(true);
              $mail->Subject = 'Pendaftaran Akaun Baru Sistem Tempahan Kenderaan';

              $mail->Body = "
                                <!doctype html>
                        <html>
                          <head>
                            <meta charset='UTF-8' />
                            <meta name='viewport' content='width=device-width, initial-scale=1.0' />
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
                                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                              }

                              .header {
                                background: linear-gradient(135deg, #2c3e50, #34495e);
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
                                color: #2c3e50;
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
                                  <h2>Sistem Tempahan Kenderaan</h2>
                                </div>

                                <div class='subtext'>
                                  Salam Sejahtera, <br /><br />
                                  Akaun anda telah berjaya didaftarkan. Sila log masuk menggunaka no kad
                                  pengenalan dan kata laluan berikut:
                                </div>

                                <div class='content' style='padding: 20px'>
                                  <table
                                    width='100%'
                                    cellpadding='8'
                                    cellspacing='0'
                                    style='border-collapse: collapse; font-size: 14px'
                                  >
                                    <tr>
                                      <td
                                        style='
                                          font-weight: bold;
                                          width: 40%;
                                          border: 1px solid #eee;
                                          background: #f9f9f9;
                                        '
                                      >
                                        No Kad Pengenalan
                                      </td>
                                      <td style='border: 1px solid #eeee'>$nokp</td>
                                    </tr>

                                    <tr>
                                      <td
                                        style='
                                          font-weight: bold;
                                          border: 1px solid #eee;
                                          background: #f9f9f9;
                                        '
                                      >
                                        Kata Laluan
                                      </td>
                                      <td style='border: 1px solid #eeee'>$plainPassword</td>
                                    </tr>
                                  </table>
                                </div>
                                <div class='footer'>
                                  Ini adalah email automatik dari
                                  <b>Sistem Tempahan Kenderaan JKN Kedah</b>.<br />
                                  Sila jangan balas email ini.
                                </div>
                              </div>
                            </div>
                          </body>
                        </html>

                            ";

              $mail->send();
              $message = "Pengguna baharu berjaya didaftarkan!";
              $messageColor = "green";

            } catch (Exception $e) {
              error_log("Email failed: " . $mail->ErrorInfo);
            }

            // Clear form fields after successful submission
            $nokp = $nama = $email = $idptj = $bahagian = $unit = $jawatan = $gred = $nohp = $password = $peranan = $status = "";
          } else {
            $message = "Gagal mendaftar pengguna. Sila cuba lagi.";
            $messageColor = "red";
          }
        } catch (PDOException $e) {
          error_log("Database error: " . $e->getMessage());
          $message = "Error: " . $e->getMessage();
          $messageColor = "red";
        }
      }
    }
  }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total records for pagination
$sql_count = "SELECT COUNT(*) AS total FROM penggunajkn";
$result_count = $conn->query($sql_count);
$total_rows = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

//initialize search variables
$search_pengguna = isset($_POST['nama']) ? $_POST['nama'] : '';
$search_bahagian = isset($_POST['namaBahagian']) ? $_POST['namaBahagian'] : '';
$search_status = isset($_POST['status']) ? $_POST['status'] : '';
$search_role = isset($_POST['role']) ? $_POST['role'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $searchParams = array(
    'id' => $_POST['id'] ?? '',
    'nama' => $_POST['nama'] ?? '',
    'email' => $_POST['email'] ?? '',
    'namaPtj' => $_POST['namaPtj'] ?? '',
    'namaBahagian' => $_POST['namaBahagian'] ?? '',
    'namaUnit' => $_POST['namaUnit'] ?? '',
    'role' => $_POST['role'] ?? '',
    'status' => $_POST['status'] ?? ''
  );

  $result = searchRecords($conn, $searchParams);
} else {
  $sql = "SELECT 
	p.*, 
	tp.nama_ptj AS namaPtj, 
	tb.bahagian AS namaBahagian, 
	tu.unit AS namaUnit,
	tj.desc_jawatan AS namaJawatan,
	tg.kod_gred  AS namaGred
FROM 
	penggunajkn p 
LEFT JOIN 
	ptjs tp 
ON 	
	p.idptj = tp.id
LEFT JOIN 
	tbahagian tb 
ON 
	p.bahagian = tb.id
LEFT JOIN 
	tunit tu 
ON 
	p.unit = tu.id
LEFT JOIN
	greds tg 
ON
	p.gred = tg.id
left join
	jawatans tj 
ON 
	p.jawatan = tj.id 
  ORDER BY id DESC LIMIT ? OFFSET ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $items_per_page, $offset);
  $stmt->execute();
  $result = $stmt->get_result();
}

function searchRecords($conn, $searchParams)
{
  $query = "SELECT 
	p.*, 
	tp.nama_ptj AS namaPtj, 
	tb.bahagian AS namaBahagian, 
	tu.unit AS namaUnit,
	tj.desc_jawatan AS namaJawatan,
	tg.kod_gred  AS namaGred
FROM 
	penggunajkn p 
LEFT JOIN 
	ptjs tp 
ON 	
	p.idptj = tp.id
LEFT JOIN 
	tbahagian tb 
ON 
	p.bahagian = tb.id
LEFT JOIN 
	tunit tu 
ON 
	p.unit = tu.id
LEFT JOIN
	greds tg 
ON
	p.gred = tg.id
left join
	jawatans tj 
ON 
	p.jawatan = tj.id 
            WHERE 1=1";

  $values = array();
  $types = "";

  if (!empty($searchParams['id'])) {
    $query .= " AND p.id = ?";
    $values[] = $searchParams['id'];
    $types .= "i";
  }

  if (!empty($searchParams['nama'])) {
    $query .= " AND p.nama LIKE ?";
    $values[] = "%" . $searchParams['nama'] . "%";
    $types .= "s";
  }

  if (!empty($searchParams['namaBahagian'])) {
    $query .= " AND tb.bahagian LIKE ?";
    $values[] = "%" . $searchParams['namaBahagian'] . "%";
    $types .= "s";
  }

  if (!empty($searchParams['status'])) {
    $query .= " AND p.status = ?";
    $values[] = $searchParams['status'];
    $types .= "s";
  }

  if (!empty($searchParams['role'])) {
    $query .= " AND p.role = ?";
    $values[] = $searchParams['role'];
    $types .= "s";
  }

  $stmt = $conn->prepare($query);

  if (!empty($values)) {
    $stmt->bind_param($types, ...$values);
  }

  $stmt->execute();
  return $stmt->get_result();
}
// At the top of the file, add this to track if we're searching
$isSearching = ($_SERVER['REQUEST_METHOD'] === 'POST' &&
  (!empty($_POST['id']) ||
    !empty($_POST['nama']) ||
    !empty($_POST['namaBahagian']) ||
    !empty($_POST['status']) ||
    !empty($_POST['role'])));

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


?>

<!Doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistem Tempahan Kenderaan | JKN Kedah</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link rel="stylesheet" href="CSS/STK2.css" />
  <link rel="stylesheet" href="CSS/layout.css" />
  <link rel="stylesheet" href="CSS/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
  <style>

  </style>
</head>

<body>
  <?= include 'dashboard2.php' ?>
  <div class="dashboard">
    <div class="main-content">
      <div class="welcome-header" style="margin-top: -26px;">
        <h1 class="welcome-text">Senarai Pengguna</h1>

        <div class="left-actions">
          <a href="#" onclick="openModal('addPenggunaModal'); return false;" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Tambah Pengguna
          </a>
          <a href="#" onclick="handleLogout()" class="btn btn-logout">
            <i class="fas fa-sign-out-alt"></i> Log Keluar
          </a>
        </div>
      </div>

      <form method="POST" action="" class="search-container" onsubmit="return validateSearch()">
        <div class="search-grid">
          <div class="search-field">
            <label>Pengguna</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($search_pengguna) ?>"
              placeholder="Cari Pengguna">
          </div>
          <div class="search-field">
            <label>Bahagian</label>
            <input type="text" name="namaBahagian" value="<?= htmlspecialchars($search_bahagian) ?>"
              placeholder="Cari Bahagian">
          </div>
          <div class="search-field">
            <label>Role</label>
            <select name="role">
              <option value="">Semua Role</option>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
              <option value="penyelaras_bahagian">Penyelaras Bahagian</option>
            </select>
          </div>
          <div class="search-field">
            <label>Status</label>
            <select name="status">
              <option value="">Semua Status</option>
              <option value="AKTIF">Aktif</option>
              <option value="TIDAK AKTIF">Tidak Aktif</option>
            </select>
          </div>
          <div class="search-field">
            <label>&nbsp;</label>
            <button type="submit" name="searchBtn" class="btn btn-primary">
              <i class="fas fa-search"></i>
              Cari
            </button>
          </div>
        </div>
      </form>
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
      <div class="table-container">
        <table class="custom-table">
          <thead>
            <tr>
              <th style="text-align:center;">Bil</th>
              <th>Nama</th>
              <th>Ptj / Bahagian / Unit</th>
              <th>Jawatan / Gred</th>
              <th>Peranan</th>
              <th>Status</th>
              <th>Tindakan</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php $bil = $offset + 1; ?>
              <?php while ($record = $result->fetch_assoc()):

                ?>

                <tr>

                  <td style="text-align:center; font-size:15px;"><?= $bil++ ?></td>
                  <td class="nama" style="font-size: 15px;">
                    <strong><?= htmlspecialchars($record['nama']) ?></strong><br>
                    <?= htmlspecialchars($record['nokp']) ?><br>
                    <?= htmlspecialchars($record['email']) ?>
                  </td>
                  <td class="ptj" style="font-size: 15px;">
                    <strong><?= htmlspecialchars($record['namaPtj']) ?></strong><br>
                    <?= htmlspecialchars($record['namaBahagian']) ?><br>
                    <?= htmlspecialchars($record['namaUnit']) ?>
                  </td>
                  <td class="jawatan" style="font-size: 15px;">
                    <strong><?= htmlspecialchars($record['namaJawatan']) ?></strong><br>
                    <?= htmlspecialchars($record['namaGred']) ?>
                  </td>

                  <?php
                  $roleMap = [
                    'staff' => 'Staff',
                    'admin' => 'Admin',
                    'penyelaras_bahagian' => 'Penyelaras Bahagian'
                  ];
                  ?>
                  <td class="role">
                    <?= htmlspecialchars($roleMap[$record['role']] ?? $record['role']) ?>
                  </td>
                  <td>
                    <label class="switch">
                      <input type="checkbox" class="status-toggle" data-id="<?= $record['id'] ?>"
                        <?= ($record['status'] == 'AKTIF') ? 'checked' : '' ?>>
                      <span class="slider round"></span>
                    </label>
                  </td>
                  <td class="action-buttons">
                    <div class="action-cell" style="justify-content: center;">
                      <a href="javascript:void(0)" class="action-btn" data-id="<?= htmlspecialchars($record['id']) ?>"
                        data-nama="<?= htmlspecialchars($record['nama']) ?>"
                        data-nokp="<?= htmlspecialchars($record['nokp']) ?>"
                        data-email="<?= htmlspecialchars($record['email']) ?>"
                        data-namaPtj="<?= htmlspecialchars($record['idptj']) ?>"
                        data-bahagian="<?= htmlspecialchars($record['bahagian']) ?>"
                        data-unit="<?= htmlspecialchars($record['unit']) ?>"
                        data-gred="<?= htmlspecialchars($record['gred']) ?>"
                        data-nohp="<?= htmlspecialchars($record['nohp']) ?>"
                        data-password="<?= htmlspecialchars($record['password']) ?>"
                        data-role="<?= htmlspecialchars($record['role']) ?>"
                        data-jawatan="<?= htmlspecialchars($record['jawatan']) ?>"
                        data-status="<?= htmlspecialchars($record['status']) ?>" title="Lihat"
                        onclick="showViewModal(this)">
                        <i class="fas fa-edit" style="color: #eb7d00;"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align:center;">Tiada rekod dijumpai</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

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

  <!-- TAMBAH PENGGUNA MODAL-->

  <div id="addPenggunaModal" class="modal2">
    <div class="modal-content2">
      <div class="container">
        <div class="modal-header">
          <span class="close" onclick="handleCancel()">&times;</span>
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
              <label for="idptj">Ptj</label>
              <select id="idptj" name="idptj" onchange="loadBahagian()">
                <option value="">-- Sila Pilih --</option>
                <?php
                $ptjQuery = $pdo->query("SELECT id, nama_ptj FROM ptjs ORDER BY nama_ptj");
                while ($ptj = $ptjQuery->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value=\"{$ptj['id']}\">{$ptj['nama_ptj']}</option>";
                }
                ?>
              </select>
            </div>
          </div>
          <div class="form-row">

            <div class="form-group">
              <label for="bahagian">Bahagian</label>
              <select id="bahagian" name="bahagian" onchange="loadUnit()">
                <option value="">-- Sila Pilih --</option>
              </select>
            </div>
            <div class="form-group">
              <label for="unit">Unit</label>
              <select id="unit" name="unit">
                <option value="">-- Sila Pilih --</option>
              </select>
            </div>
          </div>
          <div class="form-row">

            <div class="form-group">
              <label for="jawatan">Jawatan</label>
              <select id="jawatan" name="jawatan">
                <option value="">-- Sila Pilih --</option>
                <?php
                // Fetch Gred from database
                $jawatanQuery = $pdo->query("SELECT id, desc_jawatan FROM jawatans ORDER BY desc_jawatan");
                while ($jawatanItem = $jawatanQuery->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value=\"{$jawatanItem['id']}\">{$jawatanItem['desc_jawatan']}</option>";
                }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label for="gred">Gred</label>
              <select id="gred" name="gred">
                <option value="">-- Sila Pilih --</option>
                <?php
                // Fetch Gred from database
                $gredQuery = $pdo->query("SELECT id, kod_gred FROM greds ORDER BY kod_gred");
                while ($gredItem = $gredQuery->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value=\"{$gredItem['id']}\">{$gredItem['kod_gred']}</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="peranan">Peranan <span>*</span></label>
              <select id="peranan" name="peranan" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="penyelaras_bahagian">Penyelaras Bahagian</option>
              </select>
            </div>

            <div class="form-group">
              <label for="status">Status <span>*</span></label>
              <select id="status" name="status" required>
                <option value="AKTIF">Aktif</option>
                <option value="Tidak Aktif">Tidak Aktif</option>
              </select>
            </div>
          </div>
          <!-- <div class="form-group">
            <label for="password">Kata Laluan <span>*</span></label>
            <div class="password-wrapper">
              <input type="password" id="password" name="password">
              <button type="button" class="toggle-password" onclick="togglePassword2()">
                <i class="fas fa-eye-slash"></i>
              </button>
            </div>
          </div> -->

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

  <!-- END OF TAMBAH PENGGUNA MODAL -->

  <div id="viewModal" class="modal2">
    <input type="hidden" id="modalRecordId">
    <div class="modal-content2">
      <div class="container">
        <div class="modal-header">
          <span class="close" onclick="closeViewModal()">&times;</span>
          <h2 class="form-title">Butiran Pengguna </h2>
        </div>
        <form id="editForm" method="POST">
          <input type="hidden" id="displayId" name="id">
          <div class="form-group">
            <label for="nama">Nama <span>*</span></label>
            <input type="text" id="displayNama" name="nama" required placeholder="Masukkan nama penuh"
              style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="nokp">No KP <span>*</span></label>
              <input type="text" id="displayNokp" name="nokp" required placeholder="Contoh: 901230012345">
            </div>

            <div class="form-group">
              <label for="email">Email <span>*</span></label>
              <input type="email" id="displayEmail" name="email" required placeholder="Contoh: nama@moh.gov.my">
            </div>

          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="nohp">No HP <span>*</span></label>
              <input type="tel" id="displayNohp" name="nohp" required placeholder="Contoh: 0123456789">
            </div>
            <div class="form-group">
              <label for="idptj">PTJ</label>
              <select id="displayBahagian" name="idptj" onchange="loadBahagian2(this.value)">
                <option value="">-- Sila Pilih --</option>
                <?php
                $ptjQuery = $pdo->query("SELECT id, nama_ptj FROM ptjs ORDER BY nama_ptj");
                while ($ptj = $ptjQuery->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value=\"{$ptj['id']}\">{$ptj['nama_ptj']}</option>";
                }
                ?>
              </select>
            </div>

          </div>

          <div class="form-row">

            <div class="form-group">
              <label for="bahagian">Bahagian</label>
              <select id="displayUnit" name="bahagian" onchange="loadUnit2(this.value)">
                <option value="">-- Sila Pilih --</option>
              </select>
            </div>
            <div class="form-group">
              <label for="unit">Unit</label>
              <select id="displaySubUnit" name="unit">
                <option value="">-- Sila Pilih --</option>
              </select>
            </div>
          </div>
          <div class="form-row">

            <div class="form-group">
              <label for="jawatan">Jawatan <span>*</span></label>
              <select id="displayJawatan" name="jawatan">
                <option value="">-- Sila Pilih --</option>
                <?php
                $jawatanQuery = $pdo->query("SELECT id, desc_jawatan FROM jawatans ORDER BY desc_jawatan");
                while ($jawatan = $jawatanQuery->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value=\"{$jawatan['id']}\">{$jawatan['desc_jawatan']}</option>";
                }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label for="gred">Gred</label>
              <select id="displayGred" name="gred">
                <option value="">-- Sila Pilih --</option>
                <?php
                // Fetch Gred from database
                $gredQuery = $pdo->query("SELECT id, kod_gred FROM greds ORDER BY kod_gred");
                while ($gredItem = $gredQuery->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value=\"{$gredItem['id']}\">{$gredItem['kod_gred']}</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="peranan">Peranan <span>*</span></label>
              <select id="displayPeranan" name="peranan" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="penyelaras_bahagian">Penyelaras Bahagian</option>
              </select>
            </div>

            <div class="form-group">
              <label for="status">Status <span>*</span></label>
              <select id="displayStatus" name="status" required>
                <option value="AKTIF">Aktif</option>
                <option value="Tidak Aktif">Tidak Aktif</option>
              </select>
            </div>
          </div>

          <!-- <div class="form-group">
            <label for="password">
              Kata Laluan
              <small class="form-hint">(Biarkan kosong jika anda tidak mahu tukar kata laluan)</small>
            </label>

            <div class="password-wrapper">
              <input type="password" id="kataLaluan" name="password">

              <button type="button" class="toggle-password" onclick="togglePassword()">
                <i class="fas fa-eye-slash"></i>
              </button>
            </div>
          </div> -->

          <div class="button-group">
            <button type="submit" name="registerBtn" class="btn btn-success">
              <i class="fas fa-save"></i>
              Kemaskini
            </button>
            <button type="button" class="btn btn-cancel" onclick="closeModal('viewModal')">
              <i class="fas fa-times"></i> Batal
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>



  <?php if (!empty($message)): ?>
    <script>
      Swal.fire({
        icon: '<?php echo ($messageColor === "green") ? "success" : "error"; ?>',
        title: '<?php echo addslashes($message); ?>',
        confirmButtonText: 'OK',
        timer: 2500
      });
    </script>
  <?php endif; ?>


  <div id="toast" class="toast"><i class="fas fa-circle-check"></i></div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById("kataLaluan");
      const icon = document.querySelector(".toggle-password i");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      }
    }

    function showToast(message, type = "success") {
      const toast = document.getElementById("toast");

      toast.className = "toast " + type;
      toast.innerText = message;
      toast.classList.add("show");

      setTimeout(() => {
        toast.classList.remove("show");
      }, 2500);
    }

    function togglePassword2() {
      const passwordInput = document.getElementById("password");
      const icon = document.querySelector(".toggle-password i");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      }
    }

    function showToast(message, type = "success") {
      const toast = document.getElementById("toast");

      toast.className = "toast " + type;
      toast.innerText = message;
      toast.classList.add("show");

      setTimeout(() => {
        toast.classList.remove("show");
      }, 2500);
    }

    document.querySelectorAll('.status-toggle').forEach(toggle => {
      toggle.addEventListener('change', function () {

        let checkbox = this;
        let userId = checkbox.dataset.id;
        let status = checkbox.checked ? 'AKTIF' : 'Tidak Aktif';

        fetch('update_status_pengguna.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'id=' + userId + '&status=' + status
        })
          .then(response => response.text())
          .then(data => {
            if (data.trim() === "Berjaya") {
              showToast("Status berjaya dikemaskini!", "success", 1500);

              setTimeout(() => {
                location.reload();
              }, 1500);

            } else {
              checkbox.checked = !checkbox.checked;
              showToast("Kemaskini gagal!", "error");
            }
          })
          .catch(error => {
            checkbox.checked = !checkbox.checked;
            showToast("Ralat sistem!", "error");
          });
      });
    });

    // Function to open modal
    function openModal(modalId) {
      document.getElementById(modalId).style.display = "block";
    }

    // Function to close modal
    function closeModal(modalId) {
      document.getElementById(modalId).style.display = "none";
    }

    function loadBahagian() {
      const idptj = document.getElementById('idptj').value;
      const bahagianSelect = document.getElementById('bahagian');
      const unitSelect = document.getElementById('unit');

      // Clear existing options
      bahagianSelect.innerHTML = '<option value="">-- Sila Pilih --</option>';
      unitSelect.innerHTML = '<option value="">-- Sila Pilih --</option>';

      if (idptj) {
        // Fetch bahagian options based on selected PTJ
        fetch(`get_departments.php?action=getBahagian&idptj=${idptj}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              data.data.forEach(bahagian => {
                const option = document.createElement('option');
                option.value = bahagian.id;
                option.textContent = bahagian.bahagian;
                bahagianSelect.appendChild(option);
              });
            }
          })
          .catch(error => console.error('Error loading Bahagian:', error));
      }
    }

    function loadUnit() {
      const bahagianId = document.getElementById('bahagian').value;
      const unitSelect = document.getElementById('unit');

      // Clear existing options
      unitSelect.innerHTML = '<option value="">-- Sila Pilih --</option>';

      if (bahagianId) {
        // Fetch unit options based on selected Bahagian
        fetch(`get_departments.php?action=getUnit&idbahagian=${bahagianId}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              data.data.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = unit.unit;
                unitSelect.appendChild(option);
              });
            }
          })
          .catch(error => console.error('Error loading Unit:', error));
      }
    }

    function loadBahagian2(ptjId, selectedBahagianId = null, selectedUnitId = null) {
      const bahagianSelect = document.getElementById('displayUnit');
      const unitSelect = document.getElementById('displaySubUnit');

      // Clear previous options
      bahagianSelect.innerHTML = '<option value="">-- Sila Pilih --</option>';
      unitSelect.innerHTML = '<option value="">-- Sila Pilih --</option>';

      if (ptjId) {
        fetch(`get_departments.php?action=getBahagian&idptj=${ptjId}`)
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              data.data.forEach(bahagian => {
                const option = document.createElement('option');
                option.value = bahagian.id;
                option.textContent = bahagian.bahagian;
                bahagianSelect.appendChild(option);
              });
              // Set selected Bahagian after options are loaded
              if (selectedBahagianId) {
                bahagianSelect.value = selectedBahagianId;
                // Load units after bahagian is set
                loadUnit2(selectedBahagianId, selectedUnitId);
              }
            }
          });
      }
    }

    function loadUnit2(bahagianId, selectedUnitId = null) {
      const unitSelect = document.getElementById('displaySubUnit');
      unitSelect.innerHTML = '<option value="">-- Sila Pilih --</option>';

      if (bahagianId) {
        fetch(`get_departments.php?action=getUnit&idbahagian=${bahagianId}`)
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              data.data.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = unit.unit;
                unitSelect.appendChild(option);
              });
              // Set selected unit
              if (selectedUnitId) {
                unitSelect.value = selectedUnitId;
              }
            }
          });
      }
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
          window.location.href = 'pengguna_superadmin.php'
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
            window.location.href = 'pengguna_superadmin.php'

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

    function showViewModal(el) {
      const id = el.dataset.id;
      const nama = el.dataset.nama;
      const nokp = el.dataset.nokp;
      const email = el.dataset.email;
      const ptjId = el.dataset.namaptj;
      const bahagianId = el.dataset.bahagian;
      const unitId = el.dataset.unit;
      const gred = el.dataset.gred;
      const nohp = el.dataset.nohp;
      // const password = el.dataset.password;
      const jawatan = el.dataset.jawatan;
      const peranan = el.dataset.role;
      const status = el.dataset.status;

      console.log(bahagianId)

      // const statusMap = {
      //   "AKTIF": "Aktif",
      //   "TIDAK AKTIF": "Tidak Aktif"
      // };



      document.getElementById('displayId').value = id;
      document.getElementById('displayNama').value = nama;
      document.getElementById('displayNokp').value = nokp;
      document.getElementById('displayEmail').value = email;
      document.getElementById('displayGred').value = gred;
      document.getElementById('displayNohp').value = nohp;
      // document.getElementById('displayPassword').value = password;
      document.getElementById('displayJawatan').value = jawatan;
      document.getElementById('displayPeranan').value = peranan;
      document.getElementById("displayStatus").value = status;

      // Set PTJ and load Bahagian options first
      const ptjSelect = document.getElementById('displayBahagian');
      ptjSelect.value = ptjId;

      loadBahagian2(ptjId, bahagianId, unitId);

      document.getElementById('viewModal').style.display = 'flex';
    }

    function closeViewModal() {
      const modal = document.getElementById('viewModal');
      modal.style.display = 'none';
    }

    document.getElementById('editForm').addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);

      // Show loading state
      const submitButton = this.querySelector('button[type="submit"]');
      const originalText = submitButton.innerHTML;
      submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
      submitButton.disabled = true;

      fetch('update_pengguna.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire(
              'Berjaya!',
              data.message,
              'success'
            ).then(() => {
              location.reload();
            });
          } else {
            Swal.fire(
              'Ralat!',
              data.message,
              'error'
            );
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire(
            'Ralat!',
            'Terdapat masalah semasa memproses permintaan anda.',
            'error'
          );
        })
        .finally(() => {
          // Restore button state
          submitButton.innerHTML = originalText;
          submitButton.disabled = false;
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
      const params = new URLSearchParams(window.location.search);

      if (params.get("open") === "admin") {
        openModal('addPenggunaModal');

        document.getElementById("peranan").value = "admin";

      } else if (params.get("open") === "user") {
        openModal('addPenggunaModal');

        document.getElementById("peranan").value = "staff";
      }
    });

  </script>
</body>

</html>