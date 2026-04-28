<?php
session_start();
require_once 'config.php';

// Initialize variables
$records = [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Prepare base query
    $query = "SELECT * FROM ttempah_jenis";
    $params = [];

    // Add search condition if search term exists
    if (!empty($search)) {
        $query .= " WHERE jenis_kenderaan LIKE :search"; // Changed from jenis_tempahan
        $params[':search'] = "%{$search}%";
    }

    // Add ordering
    $query .= " ORDER BY id DESC";

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $records = [];
}

// Add these variables after the existing query execution
$items_per_page = 10; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_items = count($records);
$total_pages = ceil($total_items / $items_per_page);
$offset = ($page - 1) * $items_per_page;

// Slice the array to get only items for current page
$records = array_slice($records, $offset, $items_per_page);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kemaskini Jenis Tempahan Kenderaan | JKN Kedah</title>
    <link rel="stylesheet" href="CSS/STK.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include 'dashboard.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Jenis Tempahan</h1>
                <a href="#" onclick="openAddJTModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Jenis Tempahan
                </a>
            </div>
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" class="search-input" name="search" placeholder="Cari jenis tempahan...">
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
                            <th>Jenis Tempahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="3" class="no-records">Tiada rekod ditemui.</td>
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
                                    <i class="fas fa-trash" style="color: #E74C3C;"></i>
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
    </div>
    </div>

    <!-- Tambah Jenis Tempahan -->
    <div id="addJTModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addJTModal')">&times;</span>
                    <h2 class="form-title">Tambah Jenis Tempahan</h2>
                </div>
                <form id="addJTForm" method="POST">
                    <div class="form-group">
                        <label for="new_jenis_kenderaan">Jenis Kenderaan:</label>
                        <input type="text" id="new_jenis_kenderaan" name="jenis_kenderaan" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeModal('addJTModal')">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Jenis Tempahan -->
    <div id="editModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('editModal')">&times;</span>
                    <h2 class="form-title">Edit Jenis Tempahan</h2>
                </div>
                <form id="editForm" method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="jenis_tempahan">Jenis Tempahan:</label>
                        <input type="text" id="jenis_tempahan" name="jenis_tempahan" required>
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

    <script>
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
                fetch('delete_jenis_tempahan.php', {
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

    function openAddJTModal() {
        document.getElementById('addJTModal').style.display = 'block';
        document.getElementById('new_jenis_kenderaan').value = ''; // Clear the input
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    document.getElementById('addJTForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('add_jenis_tempahan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berjaya!',
                        'Jenis tempahan telah ditambah!',
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

    function openEditModal(id, jenisTemplahan) {
        document.getElementById('edit_id').value = id;
        document.getElementById('jenis_tempahan').value = jenisTemplahan;
        document.getElementById('editModal').style.display = 'block';
    }

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('update_jenis_tempahan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berjaya!',
                        'Jenis tempahan telah dikemaskini!',
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