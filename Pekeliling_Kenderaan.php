<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// if ($_SESSION['role'] !== 'staff') {
//     header("Location: error.html");
//     exit();
// }

if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: error.html");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

try {
    // Count total records for pagination
    if (!empty($search)) {
        $count_sql = "SELECT COUNT(*) FROM pekeliling_kenderaan WHERE tajuk LIKE :search OR catatan LIKE :search";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute(['search' => "%$search%"]);
    } else {
        $count_sql = "SELECT COUNT(*) FROM pekeliling_kenderaan";
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute();
    }

    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);

    // Fetch records with search functionality and pagination
    if (!empty($search)) {
        $sql = "SELECT * FROM pekeliling_kenderaan WHERE tajuk LIKE :search OR catatan LIKE :search 
                ORDER BY tarikh_pekeliling DESC LIMIT :offset, :records_per_page";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $sql = "SELECT * FROM pekeliling_kenderaan ORDER BY tarikh_pekeliling DESC LIMIT :offset, :records_per_page";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':records_per_page', $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
    }

    $pekelilings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $pekelilings = [];
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pekeliling Kenderaan | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="CSS/STK2.css" />
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
</head>

<body>
    <?= include 'dashboard2.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Pekeliling Kenderaan</h1>
                <div class="left-actions">
                    <a href="#" onclick="openModal('addPekelilingModal')" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Pekeliling Kenderaan
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" name="search" class="search-input" placeholder="Cari pekeliling..."
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
                            
                            <th class="text-center" style="text-align:center;">Bil</th>
                            <th class="text-left" >Tajuk</th>
                            <th class="text-center">Tarikh Pekeliling</th>
                            <th class="text-center">Fail</th>
                            <th class="text-left">Catatan</th>
                            <th class="text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pekelilings)):
                            $no = 1;
                            foreach ($pekelilings as $pekeliling): ?>
                        <tr>
                            
                            <td class="text-center" style="text-align: center; font-size: 15px;"><?= $no++ ?></td>
                            <td class="text-left" style="font-size: 15px;"><?= htmlspecialchars($pekeliling['tajuk']) ?></td>
                            <td class="text-center" style="font-size: 15px;"><?= date('d/m/Y', strtotime($pekeliling['tarikh_pekeliling'])) ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($pekeliling['fail'])): ?>
                                <a href="uploads/pekeliling/<?= htmlspecialchars($pekeliling['fail']) ?>"
                                    target="_blank" class="file-link">
                                    <i class="fas fa-file-pdf"></i> Lihat Fail
                                </a>
                                <?php endif; ?>
                            </td>
                            <td class="text-left" style="font-size: 15px; word-wrap: break-word; max-width: 300px;"><?= htmlspecialchars($pekeliling['catatan']) ?></td>
                            <td class="action-buttons">
                                <div class="action-cell">
                                    <a href="#"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($pekeliling)) ?>)"
                                        class="action-btn" title="Edit">
                                        <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                    </a>

                                    <a href="#" onclick="deletePekeliling(<?= (int)$pekeliling['id'] ?>)"
                                        class="action-btn" title="Padam">
                                        <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tiada rekod ditemui</td>
                        </tr>
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

            <!-- Add Modal -->
            <div id="addPekelilingModal" class="modal2">
                <div class="modal-content2">
                    <div class="container">
                        <div class="modal-header">
                            <h2 class="form-title">Tambah Pekeliling Baru</h2>
                            <span class="close" onclick="closeModal('addPekelilingModal')">&times;</span>
                        </div>
                        <form id="addPekelilingForm" action="add_pekeliling.php" method="POST"
                            enctype="multipart/form-data">

                            <div class="form-group">
                                <label for="tajuk">
                                    Tajuk:
                                </label>
                                <input type="text" id="tajuk" name="tajuk" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="tarikh_pekeliling">
                                        Tarikh Pekeliling:
                                    </label>
                                    <input type="date" id="tarikh_pekeliling" name="tarikh_pekeliling" required>
                                </div>
                                <div class="form-group">
                                    <label for="fail">
                                        Fail PDF:
                                    </label>
                                    <input type="file" id="fail" name="fail" accept=".pdf" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="catatan">
                                        Catatan:
                                    </label>
                                    <textarea id="catatan" name="catatan" rows="4" class="form-control"
                                        style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"></textarea>
                                </div>
                            </div>

                            <div class="button-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <button type="button" class="btn btn-cancel" onclick="handleCancel()">
                                    <i class="fas fa-times"></i> Batal
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
                        <div class="modal-header">
                            <span class="close" onclick="closeModal('editModal')">&times;</span>
                            <h2 class="form-title">Kemaskini Pekeliling Kenderaan</h2>
                        </div>
                        <form id="editForm" action="update_pekeliling.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" id="edit_id" name="id">

                            <div class="form-group">
                                <label for="edit_tajuk">
                                    Tajuk:
                                </label>
                                <input type="text" id="edit_tajuk" name="tajuk" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_tarikh_pekeliling">
                                        Tarikh Pekeliling:
                                    </label>
                                    <input type="date" id="edit_tarikh_pekeliling" name="tarikh_pekeliling" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_fail">
                                        Fail PDF:
                                    </label>
                                    <input type="file" id="edit_fail" name="fail" accept=".pdf">
                                    <small class="form-text text-muted">Biarkan kosong jika tidak mahu tukar fail sedia
                                        ada.</small>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_catatan">
                                        Catatan:
                                    </label>
                                    <textarea id="edit_catatan" name="catatan" rows="4" class="form-control"
                                        style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"></textarea>
                                </div>
                            </div>
                            <div class="button-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <button type="button" class="btn btn-cancel" onclick="closeModal('editModal')">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
    function openModal(modalId) {
        document.getElementById(modalId).style.display = "block";
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }

    function openEditModal(pekeliling) {
        document.getElementById('edit_id').value = pekeliling.id;
        document.getElementById('edit_tajuk').value = pekeliling.tajuk;
        document.getElementById('edit_tarikh_pekeliling').value = pekeliling.tarikh_pekeliling;
        document.getElementById('edit_catatan').value = pekeliling.catatan || '';
        openModal('editModal');
    }

    function handleCancel() {

        const tajuk = document.getElementById('tajuk').value.trim();
        const tarikh = document.getElementById('tarikh_pekeliling').value;
        const fail = document.getElementById('fail').files.length;
        const catatan = document.getElementById('catatan').value.trim();

        const modal = document.getElementById('addPekelilingModal');
        const form = document.getElementById('addPekelilingForm');

        // Check if everything is empty
        if (tajuk === '' && tarikh === '' && fail === 0 && catatan === '') {
            // Just close modal without SweetAlert
            if (modal) {
                modal.style.display = 'none';
            }
            return;
        }

        // If any field has value → show confirmation
        Swal.fire({
            title: 'Anda pasti?',
            text: "Semua maklumat yang diisi akan dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, batalkan!',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                form.reset();

                if (modal) {
                    modal.style.display = 'none';
                }

                Swal.fire(
                    'Dibatalkan!',
                    'Borang telah dikosongkan.',
                    'success'
                );
            }
        });
    }

    function deletePekeliling(id) {
        console.log("Attempting to delete pekeliling with ID:", id);

        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda tidak akan dapat memulihkan rekod ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, padam!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Convert id to a number to ensure it's treated as a numeric value
                const numericId = parseInt(id, 10);

                fetch('delete_pekeliling.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: numericId
                        })
                    })
                    .then(response => {
                        console.log("Response status:", response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log("Response data:", data);
                        if (data.success) {
                            Swal.fire(
                                'Berjaya!',
                                'Rekod telah dipadam.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Ralat!',
                                data.message || 'Gagal memadam rekod',
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
            }
        });
    }

    // Form submission handlers
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('addPekelilingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('add_pekeliling.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berjaya!',
                            'Pekeliling baru telah ditambah!',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Ralat!',
                            data.message || 'Gagal menambah rekod',
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

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('update_pekeliling.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berjaya!',
                            'Pekeliling telah dikemaskini!',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Ralat!',
                            data.message || 'Gagal mengemaskini rekod',
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
    });
    </script>
</body>

</html>