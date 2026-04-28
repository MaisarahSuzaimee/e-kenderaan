<?php
session_start();
require 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'superadmin') {
    header("Location: error.html");
    exit();
}

// Initialize search variable
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Set the number of items per page
$items_per_page = 10;

// Get the current page number from URL, default to 1 if not set
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the offset for SQL query
$offset = ($page - 1) * $items_per_page;

// Get total number of records for pagination
try {
    $count_sql = "SELECT COUNT(*) as total FROM tunit";
    $stmt = $pdo->query($count_sql);
    $total_rows = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_rows / $items_per_page);
} catch (PDOException $e) {
    error_log("Error counting records: " . $e->getMessage());
    $total_rows = 0;
    $total_pages = 1;
}

try {
    // Get total number of records first
    $count_sql = "SELECT COUNT(*) as total FROM tunit t
                  LEFT JOIN tbahagian b ON t.idbahagian = b.id";
    if (!empty($search)) {
        $count_sql .= " WHERE b.bahagian LIKE ? OR t.unit LIKE ?";
    }
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($search)) {
        $search_param = "%$search%";
        $count_stmt->bind_param("ss", $search_param, $search_param);
    }
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
    $total_rows = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $items_per_page);

    // Prepare the base query with JOIN to get PTJ details
    $sql = "SELECT t.id, t.unit, t.idbahagian, b.bahagian as namabahagian 
        FROM tunit t 
        LEFT JOIN tbahagian b ON t.idbahagian = b.id";

    if (!empty($search)) {
        $sql .= " WHERE b.bahagian LIKE ? OR t.unit LIKE ?";
    }

    $sql .= " ORDER BY t.id DESC LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);

    if (!empty($search)) {
        $search_param = "%$search%";
        $stmt->bind_param("ssii", $search_param, $search_param, $items_per_page, $offset);
    } else {
        $stmt->bind_param("ii", $items_per_page, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $records = $result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $error_message = "Database Error: " . $e->getMessage();
    error_log($error_message);
    $records = [];
}

try {
    $stmt = $pdo->prepare("SELECT id, bahagian FROM tbahagian ORDER BY bahagian");
    $stmt->execute();
    $bahagian_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching bahagian list: " . $e->getMessage());
    $bahagian_list = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK2.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <?php include 'dashboard2.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Unit</h1>
                <a href="#" onclick="openAddUModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Unit
                </a>
            </div>
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" class="search-input" name="search" placeholder="Cari unit...">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Cari
                    </button>
                </div>
            </form>

            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Tindakan</th>
                            <th style="width: 30px;">Bil</th>
                            <th>Bahagian</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Tiada rekod ditemui.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $index => $record): ?>
                                <tr>
                                    <td class="action-cell">
                                        <a href="#"
                                            onclick="openEditModal('<?= $record['id'] ?>', '<?= $record['idbahagian'] ?>', '<?= htmlspecialchars($record['unit'], ENT_QUOTES) ?>')"
                                            class="action-btn" title="Edit">
                                            <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                        </a>
                                        <a href="#" onclick="deleteRecord(<?= $record['id'] ?>)" class="action-btn"
                                            title="Padam">
                                            <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                        </a>
                                    </td>
                                    <td><?= $offset + $index + 1 ?></td>
                                    <td><?= htmlspecialchars($record['namabahagian']) ?></td>
                                    <td><?= htmlspecialchars($record['unit']) ?></td>
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


    <!-- Add Modal -->
    <div id="addUModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <span class="close" onclick="closeAddUModal()">&times;</span>
                <h2 class="form-title">Tambah Unit Baru</h2>
                <form id="addUForm" method="POST" onsubmit="return validateAddForm()">
                    <div class="form-group">
                        <label for="bahagian">Bahagian:</label>
                        <select id="bahagian" name="idbahagian" required>
                            <option value="">Pilih Bahagian</option>
                            <?php foreach ($bahagian_list as $bahagian): ?>
                                <option value="<?= $bahagian['id'] ?>">
                                    <?= htmlspecialchars($bahagian['bahagian']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="unit">Unit:</label>
                        <input type="text" id="unit" name="unit" style="text-transform: uppercase" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeAddUModal()">
                            <i class="fas fa-times"></i>
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h2 class="form-title">Kemaskini Unit</h2>
                <form id="editForm" method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_bahagian">Bahagian:</label>
                        <select id="edit_bahagian" name="idbahagian" required>
                            <option value="">Pilih Bahagian</option>
                            <?php foreach ($bahagian_list as $bahagian): ?>
                                <option value="<?= $bahagian['id'] ?>">
                                    <?= htmlspecialchars($bahagian['bahagian']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_unit">Unit:</label>
                        <input type="text" id="edit_unit" name="unit" style='text-transform:uppercase' required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeEditModal()">
                            <i class="fas fa-times"></i>
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddUModal() {
            document.getElementById('addUModal').style.display = 'block';
        }

        function closeAddUModal() {
            document.getElementById('addUModal').style.display = 'none';
        }

        function openEditModal(id, idbahagian, unit) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_bahagian').value = idbahagian;
            document.getElementById('edit_unit').value = unit;
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
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
                    fetch('delete_unit.php', {
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

        document.getElementById('editForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const idbahagian = document
                .getElementById('edit_bahagian')
                .value
                .trim();

            const unit = document
                .getElementById('edit_unit')
                .value
                .trim();

            if (!bahagian || !unit) {
                Swal.fire(
                    'Perhatian!',
                    'Sila isi semua maklumat yang diperlukan.',
                    'warning'
                );
                return;
            }

            const formData = new FormData(this);

            fetch('update_unit.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berjaya!',
                            'Bahagian telah dikemaskini!',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Ralat!',
                            data.message || 'Gagal mengemaskini data.',
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

        document.getElementById('addUForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('add_unit.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berjaya!',
                            'Unit telah ditambah!',
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

        // Add this to handle URL parameters
        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('error')) {
                alert('Ralat semasa menambah unit. Sila cuba lagi.');
            }
            if (urlParams.get('success')) {
                alert('Unit berjaya ditambah!');
            }
        };
    </script>
</body>

</html>