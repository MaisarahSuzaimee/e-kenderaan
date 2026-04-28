<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Database connection
require 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'superadmin') {
    header("Location: error.html");
    exit();
}


try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create MySQLi connection
    $conn = new mysqli($host, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Initialize variables
    $records = [];
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $items_per_page = 10;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $items_per_page;

    // Get total number of records first
    $count_sql = "SELECT COUNT(*) as total FROM tbahagian b 
                  LEFT JOIN ptjs p ON b.idptj = p.id ";
    if (!empty($search)) {
        $count_sql .= " WHERE b.bahagian LIKE :search OR p.nama_ptj LIKE :search";
    }
    $count_stmt = $pdo->prepare($count_sql);
    if (!empty($search)) {
        $search_param = "%$search%";
        $count_stmt->bindParam(':search', $search_param);
    }
    $count_stmt->execute();
    $total_rows = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_rows / $items_per_page);

    // Prepare the base query with pagination
    $sql = "SELECT b.id, b.bahagian, b.idptj, p.nama_ptj
            FROM tbahagian b 
            LEFT JOIN ptjs p ON b.idptj = p.id ";

    // Add search condition if search term exists
    if (!empty($search)) {
        $sql .= " WHERE b.bahagian LIKE :search OR p.nama_ptj LIKE :search";
    }

    // Add ORDER BY and LIMIT clauses
    $sql .= " ORDER BY b.id DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    if (!empty($search)) {
        $search_param = "%$search%";
        $stmt->bindParam(':search', $search_param);
    }
    $stmt->bindParam(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
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
    <title>Bahagian | JKN Kedah</title>
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
                <h1 class="welcome-text">Bahagian</h1>

                <div class="left-actions">
                    <a href="#" onclick="openAddBModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Bahagian
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" class="search-input" name="search" placeholder="Cari bahagian...">
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
                            <th>PTJ</th>
                            <th>Bahagian</th>
                            <!-- <th>Unit</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Tiada rekod ditemui.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $index => $record): ?>
                                <tr>
                                    <td class="action-cell">
                                        <a href="#" onclick="openEditModal(
                                               '<?= $record['id'] ?>', 
                                               '<?= $record['idptj'] ?>', 
                                               '<?= htmlspecialchars($record['bahagian'] ?? '', ENT_QUOTES) ?>', 
                                               '<?= htmlspecialchars($record['unit'] ?? '', ENT_QUOTES) ?>'
                                           )" class="action-btn" title="Edit">
                                            <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                        </a>
                                        <a href="#" onclick="deleteRecord('<?= $record['id'] ?>')" class="action-btn"
                                            title="Delete">
                                            <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                        </a>
                                    </td>
                                    <td><?= $offset + $index + 1 ?></td>
                                    <td><?= htmlspecialchars($record['nama_ptj'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($record['bahagian'] ?? '') ?></td>
                                    <!-- <td><?= htmlspecialchars($record['unit'] ?? '') ?></td> -->
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

    <!-- Tambah Bahagian -->
    <div id="addBModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <span class="close" onclick="closeAddBModal()">&times;</span>
                <h2 class="form-title">Tambah Bahagian</h2>
                <form id="addBForm" method="POST">
                    <div class="form-group">
                        <label for="add_ptj">PTJ:</label>
                        <select id="add_ptj" name="idptj" required class="form-control">
                            <option value="">Pilih PTJ</option>
                            <?php
                            try {
                                $ptj_sql = "SELECT id, nama_ptj FROM ptjs ORDER BY nama_ptj";
                                $ptj_stmt = $pdo->query($ptj_sql);
                                while ($ptj = $ptj_stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . htmlspecialchars($ptj['id']) . "'>" .
                                        htmlspecialchars($ptj['nama_ptj']) . "</option>";
                                }
                            } catch (PDOException $e) {
                                error_log("Error fetching PTJ: " . $e->getMessage());
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="add_bahagian">Bahagian:</label>
                        <input type="text" id="add_bahagian" name="bahagian" required class="form-control" style="text-transform: uppercase;" required>
                    </div>

                    <!-- <div class="form-group">
                        <label for="add_unit">Unit:</label>
                        <input type="text" id="add_unit" name="unit" class="form-control">
                    </div> -->

                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Simpan
                        </button>
                        <button type="button" class="btn btn-cancel" onclick="closeAddBModal()">
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
                <h2 class="form-title">Edit Bahagian</h2>
                <form id="editForm" method="POST">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="form-group">
                        <label for="edit_ptj">PTJ:</label>
                        <select id="edit_ptj" name="idptj" required class="form-control">
                            <?php
                            // Fetch PTJ list
                            $ptj_sql = "SELECT id, namaptj FROM tptj ORDER BY namaptj";
                            $ptj_result = $pdo->query($ptj_sql);
                            while ($ptj = $ptj_result->fetch()) {
                                echo "<option value='" . $ptj['id'] . "'>" . htmlspecialchars($ptj['namaptj']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_bahagian">Bahagian:</label>
                        <input type="text" id="edit_bahagian" name="bahagian" required class="form-control" style="text-transform: uppercase;">
                    </div>

                    <!-- <div class="form-group">
                        <label for="edit_unit">Unit:</label>
                        <input type="text" id="edit_unit" name="unit" class="form-control">
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

    <script>
        function openEditModal(id, idptj, bahagian, unit) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_ptj').value = idptj;
            document.getElementById('edit_bahagian').value = bahagian || '';
            // document.getElementById('edit_unit').value = unit || '';
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
                    fetch('delete_bahagian.php', {
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

        function openAddBModal() {
            document.getElementById('addBModal').style.display = 'block';
            document.getElementById('add_ptj').value = '';
            document.getElementById('add_bahagian').value = '';
        }

        function closeAddBModal() {
            document.getElementById('addBModal').style.display = 'none';
        }

        function validateForm() {
            const ptj = document.getElementById('add_ptj').value;
            const bahagian = document.getElementById('add_bahagian').value.trim();

            if (!ptj || !bahagian) {
                alert('Sila isi semua maklumat yang diperlukan');
                return false;
            }

            console.log('Submitting form with:', {
                ptj: ptj,
                bahagian: bahagian,
                unit: document.getElementById('add_unit').value
            });

            return true;
        }

        document.getElementById('addBForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('add_bahagian.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berjaya!',
                            'Bahagian telah ditambah!',
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

        document.getElementById('editForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const idptj = document
                .getElementById('edit_ptj')
                .value
                .trim();

            const bahagian = document
                .getElementById('edit_bahagian')
                .value
                .trim();

            // const unit = document
            //     .getElementById('edit_unit')
            //     .value
            //     .trim();


            if (!idptj || !bahagian) {
                Swal.fire(
                    'Perhatian!',
                    'Sila isi semua maklumat yang diperlukan.',
                    'warning'
                );
                return;
            }

            const formData = new FormData(this);

            fetch('update_bahagian.php', {
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

    </script>
</body>

</html>