<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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



// Debug connection
try {
    $test_query = $pdo->query("SELECT 1");
    error_log("Database connection successful");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$records = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Set the number of items per page to exactly 10
$items_per_page = 10;

// Get the current page number from URL, default to 1 if not set
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the OFFSET for SQL query
$offset = ($page - 1) * $items_per_page;

try {
    // Get total number of records
    $count_sql = "SELECT COUNT(*) as total FROM ttempah_jenis";
    if (!empty($search)) {
        $count_sql .= " WHERE jenis_kenderaan LIKE :search";
    }
    $count_stmt = $pdo->prepare($count_sql);
    if (!empty($search)) {
        $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $items_per_page);

    // Prepare the base query
    $sql = "SELECT * FROM ttempah_jenis";
    if (!empty($search)) {
        $sql .= " WHERE jenis_kenderaan LIKE :search";
    }
    $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

    // Prepare and execute the query
    $stmt = $pdo->prepare($sql);
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add debug output
    if (empty($records)) {
        error_log("No records found in tjenis table");
    }
} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
    error_log($error_message);
    echo "<div style='color: red; padding: 10px;'>Error connecting to database. Please check error logs.</div>";
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
                <h1 class="welcome-text">Jenis Kenderaan</h1>
                <div class="left-actions">
                    <a href="#" onclick="openAddJKModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Jenis Kenderaan
                </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
                
            </div>

            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" class="search-input" name="search" placeholder="Cari jenis kenderaan...">
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
                            <th>Jenis Kenderaan</th>
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
                                    <td class="action-cell">
                                        <a href="#"
                                            onclick="openEditModal('<?= htmlspecialchars($record['id']) ?>', '<?= htmlspecialchars($record['jenis_kenderaan']) ?>')"
                                            class="action-btn" title="Edit">
                                            <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                        </a>
                                        <a href="#" onclick="deleteRecord('<?= htmlspecialchars($record['id']) ?>')"
                                            class="action-btn" title="Delete">
                                            <i class="fas fa-trash" style="color: #e74c3c;"></i>
                                        </a>
                                    </td>
                                    <td><?= $offset + $index + 1 ?></td>
                                    <td><?= htmlspecialchars($record['jenis_kenderaan']) ?></td>
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

    <!-- Tambah Jenis Kenderaan -->
    <div id="addJKModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeAddJKModal()">&times;</span>
                    <h2 class="form-title">Tambah Jenis Kenderaan</h2>
                </div>
                <form id="addJKForm" method="POST">
                    <div class="form-group">
                        <label for="new_jeniskenderaan">Jenis Kenderaan:</label>
                        <input type="text" id="new_jeniskenderaan" name="jeniskenderaan" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeAddJKModal()">
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
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2 class="form-title">Kemaskini Jenis Kenderaan</h2>
                </div>
                <form id="editForm" action="update_jenis_kenderaan.php" method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_jeniskenderaan">Jenis Kenderaan:</label>
                        <input type="text" id="edit_jeniskenderaan" name="jeniskenderaan" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeEditModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Get the modal
        const modal = document.getElementById('editModal');

        // Function to open the edit modal
        function openEditModal(id, jeniskenderaan) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_jeniskenderaan').value = jeniskenderaan;
            modal.style.display = "block";
        }

        // Function to close the edit modal
        function closeEditModal() {
            modal.style.display = "none";
        }

        // Add Modal Functions
        const addModal = document.getElementById('addJKModal');

        function openAddJKModal() {
            addModal.style.display = "block";
        }

        function closeAddJKModal() {
            addModal.style.display = "none";
            document.getElementById('addJKForm').reset();
        }

        // Close modal when clicking outside of it
        window.onclick = function (event) {
            if (event.target == modal) {
                closeEditModal();
            }
            if (event.target == addModal) {
                closeAddJKModal();
            }
        }

        // Function to delete kenderaan
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
                    fetch('delete_jenis_kenderaan.php', {
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

            const jeniskenderaan = document
                .getElementById('edit_jeniskenderaan')
                .value
                .trim();

            if (!jeniskenderaan) {
                Swal.fire(
                    'Perhatian!',
                    'Sila isi semua maklumat yang diperlukan.',
                    'warning'
                );
                return;
            }

            const formData = new FormData(this);

            fetch('update_jenis_kenderaan.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berjaya!',
                            'Jenis kenderaan telah dikemaskini!',
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


        document.getElementById('addJKForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('add_jenis_kenderaan.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berjaya!',
                            'Jenis kenderaan telah ditambah!',
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