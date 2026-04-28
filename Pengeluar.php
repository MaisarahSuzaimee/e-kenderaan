<?php
session_start();
// Include database configuration
require 'config.php';

// Initialize variables
$records = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Set the number of items per page to exactly 10
$items_per_page = 10;

// Get the current page number from URL, default to 1 if not set
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the OFFSET for SQL query
$offset = ($page - 1) * $items_per_page;

try {
    // Get total number of records first
    $count_sql = "SELECT COUNT(*) as total FROM tpengeluar";
    if (!empty($search)) {
        $count_sql .= " WHERE namapengeluar LIKE ?";
    }
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($search)) {
        $search_param = "%$search%";
        $count_stmt->bind_param("s", $search_param);
    }
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
    $total_rows = $total_result->fetch_assoc()['total'];
    $total_pages = ceil($total_rows / $items_per_page);

    // Prepare the base query
    $sql = "SELECT * FROM tpengeluar";
    if (!empty($search)) {
        $sql .= " WHERE namapengeluar LIKE ?";
    }
    $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    
    if (!empty($search)) {
        $search_param = "%$search%";
        $stmt->bind_param("sii", $search_param, $items_per_page, $offset);
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

echo "<!-- Debug Info:
Total Rows: " . $total_rows . "
Items Per Page: " . $items_per_page . "
Total Pages: " . $total_pages . "
Current Page: " . $page . "
-->";
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

</head>

<body>
    <?php include 'dashboard.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Pengeluar / Model</h1>
                <a href="#" onclick="openAddPMModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Pengeluar / Model
                </a>
            </div>
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" class="search-input" name="search" placeholder="Cari pengeluar...">
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
                            <th>Nama Pengeluar</th>
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
                            <td class="action-cell" style="padding-right: 0;">
                                <a href="#"
                                    onclick="openEditModal('<?= $record['id'] ?>', '<?= htmlspecialchars($record['namapengeluar'], ENT_QUOTES) ?>')"
                                    class="action-btn" title="Edit">
                                    <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                </a>
                                <a href="#" onclick="deleteRecord('<?= htmlspecialchars($record['id']) ?>')"
                                    class="action-btn" title="Delete">
                                    <i class="fas fa-trash" style="color: #e74c3c;"></i>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($offset + $index + 1) ?></td>
                            <td><?= htmlspecialchars($record['namapengeluar']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
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
    <div id="addPMModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
            <span class="close" onclick="closeAddPMModal()">&times;</span>
            <h2 class="form-title">Tambah Pengeluar</h2>
            <form id="addPMForm" method="POST">
                <div class="form-group">
                    <label for="namapengeluar">Nama Pengeluar:</label>
                    <input type="text" id="namapengeluar" name="namapengeluar" required>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i>
                        Simpan
                    </button>
                    <button type="button" class="btn btn-cancel" onclick="closeAddPMModal()">
                        <i class="fas fa-times"></i>
                        Batal
                    </button>
                </div>
            </form>
                    </div>
        </div>
    </div>

    <!-- Add the Edit Modal -->
    <div id="editModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2 class="form-title">Edit Pengeluar</h2>
            <form id="editForm" method="POST" action="update_pengeluar.php">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_namapengeluar">Nama Pengeluar:</label>
                    <input type="text" id="edit_namapengeluar" name="namapengeluar" required>
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

    <!-- Add Modal Script -->
    <script>
    function openEditModal(id, namapengeluar) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_namapengeluar').value = namapengeluar;
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    function openAddPMModal() {
        document.getElementById('addPMModal').style.display = 'block';
    }

    function closeAddPMModal() {
        document.getElementById('addPMModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        var addModal = document.getElementById('addPMModal');
        var editModal = document.getElementById('editModal');
        if (event.target == addPMModal) {
            addModal.style.display = 'none';
        }
        if (event.target == editModal) {
            editModal.style.display = 'none';
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
                fetch('delete_pengeluar.php', {
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

    document.getElementById('addPMForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('add_pengeluar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berjaya!',
                        'Pengeluar telah ditambah!',
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

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault(); // stop normal form submit

        const namapengeluar = document
            .getElementById('edit_namapengeluar')
            .value
            .trim();

        // ✅ validation first
        if (!namapengeluar) {
            Swal.fire(
                'Perhatian!',
                'Sila isi semua maklumat yang diperlukan.',
                'warning'
            );
            return; // stop here
        }

        // ✅ submit using fetch
        const formData = new FormData(this);

        fetch('update_pengeluar.php', {
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
    </script>
</body>

</html>