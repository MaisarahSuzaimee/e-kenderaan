<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a superadmin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit;
}

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
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
function searchRecords($conn, $searchParams) {
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
        $query .= " AND tk.tarikh_mohon like ?";
        $values[] = "%" . $searchParams['tarikh'] . "%";
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
        $query .= " AND tk.jenis_kenderaan LIKE ?";
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

// Helper function to get driver name
function getDriverName($conn, $driverId) {
    if (empty($driverId)) return '';
    
    $sql = "SELECT namapemandu FROM tpemandu WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['namapemandu'];
    }
    
    return '';
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

// Get system statistics
$statsQuery = [
    "total_bookings" => "SELECT COUNT(*) as count FROM tempahan_kenderaan",
    "pending_bookings" => "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'BARU' OR kelulusan = 'KIV'",
    "approved_bookings" => "SELECT COUNT(*) as count FROM tempahan_kenderaan WHERE kelulusan = 'LULUS'",
    "total_drivers" => "SELECT COUNT(*) as count FROM tpemandu WHERE status = 'Aktif'",
    "total_vehicles" => "SELECT COUNT(*) as count FROM tkenderaan",
    "total_users" => "SELECT COUNT(*) as count FROM penggunajkn"
];

$stats = [];
foreach ($statsQuery as $key => $query) {
    $result_stats = $conn->query($query);
    $stats[$key] = $result_stats->fetch_assoc()['count'];
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
    <title>Sistem Tempahan Kenderaan | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

</head>

<body>
    <?php include 'dashboard.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Senarai Tempahan Kenderaan</h1>
                <!-- <a href="#" onclick="openAddModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Pemandu
                </a> -->
            </div>
             <form method="POST" action="" class="search-container" onsubmit="return validateSearch()">
                <div class="search-grid">
                    <div class="search-field">
                        <label>ID</label>
                        <input type="text" name="id" value="<?= htmlspecialchars($_POST['id'] ?? '') ?>" placeholder="Cari ID">
                    </div>
                    <div class="search-field">
                        <label>Pemohon</label>
                        <input type="text" name="pemohon" value="<?= htmlspecialchars($search_pemohon) ?>" placeholder="Cari Pemohon">
                    </div>
                    <div class="search-field">
                        <label>Destinasi</label>
                        <input type="text" name="destinasi" value="<?= htmlspecialchars($search_destinasi) ?>" placeholder="Cari Destinasi">
                    </div>
                    <div class="search-field">
                        <label>Status</label>
                        <select name="kelulusan">
                            <option value="">Semua Status</option>
                            <option value="BARU" <?= $search_kelulusan == 'BARU' ? 'selected' : '' ?>>Baru</option>
                            <option value="LULUS" <?= $search_kelulusan == 'LULUS' ? 'selected' : '' ?>>Lulus</option>
                            <option value="TIDAKLULUS" <?= $search_kelulusan == 'TIDAKLULUS' ? 'selected' : '' ?>>Tidak Lulus</option>
                            <option value="KIV" <?= $search_kelulusan == 'KIV' ? 'selected' : '' ?>>KIV</option>
                            <option value="BATAL" <?= $search_kelulusan == 'BATAL' ? 'selected' : '' ?>>Batal</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label>Tarikh</label>
                        <input type="date" name="tarikh" value="<?= htmlspecialchars($_POST['tarikh'] ?? '') ?>">
                    </div>
                    <div class="search-field">
                        <label>Pemandu</label>
                        <input type="text" name="pemandu" value="<?= htmlspecialchars($_POST['pemandu'] ?? '') ?>" placeholder="Cari Pemandu">
                    </div>
                    <div class="search-field">
                        <label>Kenderaan</label>
                        <input type="text" name="kenderaan" value="<?= htmlspecialchars($_POST['kenderaan'] ?? '') ?>" placeholder="Cari Kenderaan">
                    </div>
                    <div class="search-field">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <!-- <a href="STK_superadmin.php" class="reset-btn">
                            <i class="fas fa-redo"></i> Reset
                        </a> -->
                    </div>
                </div>
            </form>

            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>ID</th>
                            <th>Pemohon</th>
                            <th>Tarikh Mohon</th>
                            <th>Destinasi</th>
                            <th>Status</th>
                            <th>Nama Pemandu</th>
                            <th>Kenderaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = $offset + 1;
                         if ($result->num_rows > 0): ?>
                            <?php while ($record = $result->fetch_assoc()): 
                                $isNew = (isset($record['created_at']) && strtotime($record['created_at']) >= strtotime('-2 days')) ? 'new-data' : '';
                                $statusClass = match (isset($record['kelulusan']) ? $record['kelulusan'] : 'BARU') {
                                    'LULUS' => 'status-approved ',
                                    'TIDAK LULUS' => 'status-rejected',
                                    'KIV' => 'status-pending status-white-text',
                                    'BARU' => 'status-new',
                                    'BATAL' => 'status-batal',
                                    default => ''
                                };
                            ?>
                                <tr data-id="<?= $record['id'] ?>" class="<?= $isNew ?>">
                                    <td><?= $count++ ?></td>
                                    <td><?= $record['id'] ?></td>
                                    <td><?= htmlspecialchars($record['pemohon']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($record['tarikh_mohon'])) ?></td>
                                    <td><?= htmlspecialchars($record['destinasi']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($record['kelulusan']) ?>
                                        </span>
                                    </td>
                                    <td class="pemandu-cell">
                                        <?php 
                                        $driverName = !empty($record['namapemandu']) ? 
                                            htmlspecialchars($record['namapemandu']) : 'Belum ditentukan';
                                        echo $driverName;
                                        ?>
                                    </td>
                                    <td class="kenderaan-cell">
                                        <?php 
                                        $vehicleName = !empty($record['kenderaan']) ? htmlspecialchars($record['kenderaan']) : 
                                                      (!empty($record['jenis_kenderaan']) ? htmlspecialchars($record['jenis_kenderaan']) : 'Belum ditugaskan');
                                        echo $vehicleName;
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="no-records">Tiada rekod ditemui</td>
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>