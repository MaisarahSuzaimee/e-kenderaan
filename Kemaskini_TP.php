<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'superadmin') {
    header("Location: error.html");
    exit();
}


// Initialize variables
$records = [];
$items_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Initialize search variables
    $searchCondition = '';
    $params = [];

    // Handle Search
    if (!empty($search)) {
        $searchCondition = "WHERE tujuan_perjalanan LIKE :search"; // Changed to correct column name
        $params[':search'] = "%{$search}%";
    }

    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM ttempah_tujuan " . $searchCondition;
    $stmt = $pdo->prepare($countQuery);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $total_rows = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_rows / $items_per_page);

    // Fetch records with pagination
    $query = "SELECT * FROM ttempah_tujuan " . $searchCondition . " 
              ORDER BY id DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    if (!empty($params)) {
        $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
    error_log($error_message);
    $records = [];
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
</head>

<body>
    <?= include 'dashboard2.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Tujuan Perjalanan</h1>
                
                <div class="left-actions">
                    <a href="#" onclick="openTPModal('addTPModal')" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Tujuan Perjalanan
                </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" name="search" class="search-input" placeholder="Cari tujuan perjalanan..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Tindakan</th>
                            <th style="width: 30px;">Bil</th>
                            <th>Tujuan Perjalanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="3">Tiada rekod ditemui.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($records as $index => $record): ?>
                        <tr>
                            <td class="action-buttons">
                                <div class="action-cell">
                                    <a href="#"
                                        onclick="openEditModal(<?php echo $record['id']; ?>, '<?php echo htmlspecialchars($record['tujuan_perjalanan']); ?>')"
                                        class="action-btn" title="Edit">
                                        <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                    </a>
                                    <a href="#" onclick="deleteRecord(<?php echo $record['id']; ?>)" class="action-btn">
                                        <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                    </a>
                                </div>
                            </td>
                            <td><?= $offset + $index + 1 ?></td>
                            <td><?= htmlspecialchars($record['tujuan_perjalanan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>


            </div>
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link page-nav" title="Muka Pertama">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?= $page - 1 ?><?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link page-nav" title="Sebelum">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <?php endif; ?>

                    <a href="?page=<?= $page ?><?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link active">
                        <?= $page ?>
                    </a>

                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link page-nav" title="Seterusnya">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?= $total_pages ?><?= !empty($_POST) ? '&' . http_build_query($_POST) : '' ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link page-nav" title="Muka Terakhir">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tambah tujuan perjalanan -->
    <div id="addTPModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeTPModal()">&times;</span>
                    <h2 class="form-title">Tambah Tujuan Perjalanan</h2>
                </div>
                <form id="addTPForm" method="POST">
                    <div class="form-group">
                        <label for="new_tujuan_perjalanan">Tujuan Perjalanan:</label>
                        <input type="text" id="new_tujuan_perjalanan" name="tujuan_perjalanan" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeTPModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Tujuan Perjalanan -->
    <div id="editModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2 class="form-title">Edit Tujuan Perjalanan</h2>
                </div>
                <form id="editForm" method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="tujuan_perjalanan">Tujuan Perjalanan:</label>
                        <input type="text" id="tujuan_perjalanan" name="tujuan_perjalanan" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openTPModal() {
        document.getElementById('addTPModal').style.display = 'block';
        document.getElementById('new_tujuan_perjalanan').value = '';
    }

    function closeTPModal() {
        document.getElementById('addTPModal').style.display = 'none';
    }

    document.getElementById('addTPForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('add_tujuan_perjalanan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berjaya!',
                        'Tujuan perjalanan telah ditambah!',
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
            });
    });

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
                fetch('delete_tujuan_perjalanan.php', {
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

    function openEditModal(id, tujuanPerjalanan) {
        document.getElementById('edit_id').value = id;
        document.getElementById('tujuan_perjalanan').value = tujuanPerjalanan;
        document.getElementById('editModal').style.display = 'block';

        console.log(tujuanPerjalanan)
    }

    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('update_tujuan_perjalanan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berjaya!',
                        'Tujuan perjalanan telah dikemaskini!',
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
            });
    });
    </script>
</body>

</html>