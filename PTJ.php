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
// Initialize variables
$records = [];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$items_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

try {
    // Get total number of records first
    $count_sql = "SELECT COUNT(*) as total FROM ptjs";
    if (!empty($search)) {
        $count_sql .= " WHERE nama_ptj LIKE ?";
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
    $sql = "SELECT id, nama_ptj FROM ptjs";
    if (!empty($search)) {
        $sql .= " WHERE nama_ptj LIKE ?";
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
    $_SESSION['error'] = "Error: " . $e->getMessage();
    $records = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PTJ | JKN Kedah</title>
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
                <h1 class="welcome-text">PTJ</h1>
                <a href="#" onclick="openAddPTJModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah PTJ
                </a>
            </div>
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" class="search-input" name="search" placeholder="Cari PTJ...">
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
                            <th>Nama PTJ</th>
                            <!-- <th>Nama Ringkas</th> -->
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
                                    onclick="openEditModal('<?= $record['id'] ?>', '<?= htmlspecialchars($record['nama_ptj'], ENT_QUOTES) ?>')"
                                    class="action-btn" title="Edit">
                                    <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                </a>
                                <a href="#" onclick="deleteRecord(<?= $record['id'] ?>)" class="action-btn"
                                    title="Padam">
                                    <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                </a>
                            </td>
                            <td><?= $offset + $index + 1 ?></td>
                            <td><?= htmlspecialchars($record['nama_ptj']) ?></td>
                           
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

    <!-- Edit Modal -->
    <div id="editModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h2 class="form-title">Edit PTJ</h2>
                <form id="editForm" action="update_ptj.php" method="POST">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="form-group">
                        <label for="edit_namaptj">Nama PTJ:</label>
                        <input type="text" id="edit_namaptj" name="namaptj" required class="form-control">
                    </div>
<!-- 
                    <div class="form-group">
                        <label for="edit_namaringkas">Nama Ringkas:</label>
                        <input type="text" id="edit_namaringkas" name="namaringkas" required class="form-control">
                    </div> -->

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

    <!-- Add Modal -->
    <div id="addPTJModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <span class="close" onclick="closeAddPTJModal()">&times;</span>
                <h2 class="form-title">Tambah PTJ</h2>
                <form id="addPTJForm" method="POST">
                    <div class="form-group">
                        <label for="add_namaptj">Nama PTJ:</label>
                        <input type="text" id="add_namaptj" name="namaptj" required class="form-control" style="text-transform:uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>

                    <!-- <div class="form-group">
                        <label for="add_namaringkas">Nama Ringkas:</label>
                        <input type="text" id="add_namaringkas" name="namaringkas" required class="form-control" >
                    </div> -->

                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeAddPTJModal()">
                            <i class="fas fa-times"></i>
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openEditModal(id, namaptj) {
        document.getElementById('editModal').style.display = 'block';
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_namaptj').value = namaptj;
        // document.getElementById('edit_namaringkas').value = namaringkas;
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
                fetch('delete_ptj.php', {
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

    function openAddPTJModal() {
        document.getElementById('addPTJModal').style.display = 'block';
        document.getElementById('add_namaptj').value = '';
        // document.getElementById('add_namaringkas').value = '';
    }

    function closeAddPTJModal() {
        document.getElementById('addPTJModal').style.display = 'none';
    }

    document.getElementById('addPTJForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('add_ptj.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berjaya!',
                        'PTJ telah ditambah!',
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
        e.preventDefault();

        const namaptj = document
            .getElementById('edit_namaptj')
            .value
            .trim();

        // const namaringkas = document
        //     .getElementById('edit_namaringkas')
        //     .value
        //     .trim();

        // ✅ validation
        if (!namaptj) {
            Swal.fire(
                'Perhatian!',
                'Sila isi semua maklumat yang diperlukan.',
                'warning'
            );
            return;
        }

        const formData = new FormData(this);

        fetch('update_ptj.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berjaya!',
                        data.message || 'PTJ telah dikemaskini!',
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