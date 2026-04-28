<?php
session_start();
require_once 'config.php';

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Fetch records with search functionality
    if (!empty($search)) {
        $sql = "SELECT * FROM kenderaan_rasmi WHERE no_plat LIKE :search OR model LIKE :search OR ptj LIKE :search OR nama_pegawai LIKE :search OR jawatan LIKE :search OR gred LIKE :search ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $sql = "SELECT * FROM kenderaan_rasmi ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $kenderaans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $kenderaans = [];
}

// Add pagination variables
$items_per_page = 10; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$total_items = count($kenderaans);
$total_pages = ceil($total_items / $items_per_page);
$offset = ($page - 1) * $items_per_page;

// Slice the array to get only items for current page
$kenderaans = array_slice($kenderaans, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenderaan Rasmi Jawatan | JKN Kedah</title>
    <link rel="stylesheet" href="CSS/STK.css" />
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
</head>

<body>
    <?= include 'dashboard.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Kenderaan Rasmi Jawatan</h1>
                <div class="left-actions">
                    <a href="#" onclick="openModal('addKenderaanRasmiModal')" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Kenderaan Rasmi Jawatan
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>

            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" name="search" class="search-input" placeholder="Cari kenderaan rasmi..."
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
                            
                            <th class="text-center" style="width: 50px;">No.</th>
                            <th class="text-left">No. Plat</th>
                            <th class="text-left">Model</th>
                            <th class="text-left">PTJ</th>
                            <th class="text-left">Nama Pegawai</th>
                            <th class="text-left">Jawatan</th>
                            <th class="text-left">Gred</th>
                            <th class="text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kenderaans)):
                            $no = 1;
                            foreach ($kenderaans as $kenderaan): ?>
                        <tr>
                            
                            <td style="text-align: center; font-size:15px;"><?= $no++ ?></td>
                            <td class="text-left" style="font-size:15px;"><?= htmlspecialchars($kenderaan['no_plat']) ?></td>
                            <td class="text-left" style="font-size:15px;"><?= htmlspecialchars($kenderaan['model']) ?></td>
                            <td class="text-left" style="font-size:15px;"><?= htmlspecialchars($kenderaan['ptj']) ?></td>
                            <td class="text-left" style="font-size:15px;"><?= htmlspecialchars($kenderaan['nama_pegawai']) ?></td>
                            <td class="text-left" style="font-size:15px;"><?= htmlspecialchars($kenderaan['jawatan']) ?></td>
                            <td class="text-left" style="font-size:15px;"><?= htmlspecialchars($kenderaan['gred']) ?></td>
                            <td class="action-buttons">
                                <div class="action-cell">
                                    <a href="#"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($kenderaan)) ?>)"
                                        class="action-btn" title="Edit">
                                        <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                    </a>

                                    <a href="#" onclick="deleteKenderaan(<?= $kenderaan['id'] ?>)" class="action-btn"
                                        title="Padam">
                                        <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tiada rekod ditemui</td>
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
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addKenderaanRasmiModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addKenderaanRasmiModal')">&times;</span>
                    <h2 class="form-title">Tambah Kenderaan Rasmi Baru</h2>
                </div>
                <form id="addKenderaanRasmiForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="no_plat">No. Plat:</label>
                            <input type="text" id="no_plat" name="no_plat" required>
                        </div>
                        <div class="form-group">
                            <label for="model">Model:</label>
                            <input type="text" id="model" name="model" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama_pegawai">Nama Pegawai:</label>
                        <input type="text" id="nama_pegawai" name="nama_pegawai" required
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-group">
                        <label for="jawatan">Jawatan:</label>
                        <input type="text" id="jawatan" name="jawatan" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ptj">PTJ:</label>
                            <select id="ptj" name="ptj" required>
                                <option value="">-- Pilih PTJ --</option>
                                <?php
                                try {
                                    $ptj_sql = "SELECT id, namaptj FROM tptj ORDER BY namaptj";
                                    $ptj_stmt = $pdo->prepare($ptj_sql);
                                    $ptj_stmt->execute();
                                    $ptj_list = $ptj_stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($ptj_list as $ptj_item) {
                                        echo '<option value="' . htmlspecialchars($ptj_item['namaptj']) . '">' .
                                            htmlspecialchars($ptj_item['namaptj']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    error_log("Error fetching PTJ: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gred">Gred:</label>
                            <input type="text" id="gred" name="gred" required>
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
                    <h2 class="form-title">Kemaskini Maklumat Kenderaan</h2>
                </div>

                <form id="editForm" class="styled-form">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_no_plat">
                                No. Plat
                            </label>
                            <input type="text" id="edit_no_plat" name="no_plat" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_model">
                                Model
                            </label>
                            <input type="text" id="edit_model" name="model" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_nama_pegawai">
                                Nama Pegawai
                            </label>
                            <input type="text" id="edit_nama_pegawai" name="nama_pegawai" class="form-control" required
                                style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="form-group">
                            <label for="edit_ptj">
                                PTJ
                            </label>
                            <select id="edit_ptj" name="ptj" class="form-control" required>
                                <option value="">-- Pilih PTJ --</option>
                                <?php foreach ($ptj_list as $ptj_item): ?>
                                <option value="<?= htmlspecialchars($ptj_item['namaptj']) ?>">
                                    <?= htmlspecialchars($ptj_item['namaptj']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_jawatan">
                                Jawatan
                            </label>
                            <input type="text" id="edit_jawatan" name="jawatan" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_gred">
                                Gred
                            </label>
                            <input type="text" id="edit_gred" name="gred" class="form-control" required>
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

    <script>
    // Modal functionality
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Function to open edit modal with data
    function openEditModal(kenderaan) {
        document.getElementById('edit_id').value = kenderaan.id;
        document.getElementById('edit_no_plat').value = kenderaan.no_plat;
        document.getElementById('edit_model').value = kenderaan.model;
        document.getElementById('edit_ptj').value = kenderaan.ptj;
        document.getElementById('edit_nama_pegawai').value = kenderaan.nama_pegawai;
        document.getElementById('edit_jawatan').value = kenderaan.jawatan;
        document.getElementById('edit_gred').value = kenderaan.gred;

        openModal('editModal');
    }

    // Delete functionality
    function deleteKenderaan(id) {
        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Rekod ini akan dipadam secara kekal!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, padam!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('delete_kenderaan_rasmi.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
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
                                'Gagal memadam rekod.',
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

    function handleCancel() {
        const no_plat = document.getElementById('no_plat').value.trim();
        const model = document.getElementById('model').value.trim();
        const nama_pegawai = document.getElementById('nama_pegawai').value.trim();
        const jawatan = document.getElementById('jawatan').value.trim();
        const ptj = document.getElementById('ptj').value.trim();
        const gred = document.getElementById('gred').value.trim();

        const modal = document.getElementById('addKenderaanRasmiModal');
        const form = document.getElementById('addKenderaanRasmiForm');

        if (no_plat === '' && model === '' && nama_pegawai === '' && jawatan === '' && ptj === '' && gred === '') {
            if (modal) {
                modal.style.display = 'none';
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
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                // Clear all form inputs
                const form = document.getElementById('addKenderaanRasmiForm');
                form.reset();

                // Close the modal if it exists
                const modal = document.getElementById('addKenderaanRasmiModal');
                if (modal) {
                    modal.style.display = 'none';
                }

                // Show success message
                Swal.fire(
                    'Dibatalkan!',
                    'Borang telah dikosongkan.',
                    'success'
                );
            }
        });
    }

    // Form submission handlers
    document.getElementById('addKenderaanRasmiForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('add_kenderaan_rasmi.php', {
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
            });
    });

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('update_kenderaan_rasmi.php', {
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
            });
    });

    function toggleDropdown(button) {
        // Close all other dropdowns
        const allDropdowns = document.querySelectorAll('.dropdown-btn');
        allDropdowns.forEach(btn => {
            if (btn !== button) {
                btn.classList.remove('active');
                btn.nextElementSibling.classList.remove('show');
            }
        });

        // Toggle current dropdown
        button.classList.toggle('active');
        button.nextElementSibling.classList.toggle('show');

        // Handle overlay
        const overlay = document.querySelector('.overlay');
        if (button.classList.contains('active')) {
            overlay.classList.add('show');
        } else {
            overlay.classList.remove('show');
        }
    }

    // Add overlay to close dropdowns when clicking outside
    document.body.insertAdjacentHTML('beforeend', '<div class="overlay"></div>');
    const overlay = document.querySelector('.overlay');

    overlay.addEventListener('click', () => {
        const allDropdowns = document.querySelectorAll('.dropdown-btn');
        allDropdowns.forEach(btn => {
            btn.classList.remove('active');
            btn.nextElementSibling.classList.remove('show');
        });
        overlay.classList.remove('show');
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            const allDropdowns = document.querySelectorAll('.dropdown-btn');
            allDropdowns.forEach(btn => {
                btn.classList.remove('active');
                btn.nextElementSibling.classList.remove('show');
            });
            overlay.classList.remove('show');
        }
    });
    </script>
</body>

</html>