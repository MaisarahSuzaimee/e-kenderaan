<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header("Location: error.html");
    exit();
}

try {
    $search = $_GET['search'] ?? '';

    if (!empty($search)) {
        $sql = "SELECT * FROM kenderaan_jabatan 
                WHERE no_plat 
                LIKE :search 
                OR ptj LIKE :search 
                OR jenis LIKE :search 
                OR pengeluar LIKE :search 
                OR model LIKE :search 
                OR keadaan_semasa LIKE :search 
                ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $sql = "select kj.* , tj.jenis_kenderaan, p.nama_ptj, tb.bahagian 
                from kenderaan_jabatan kj 
                join ttempah_jenis tj 
                on kj.id_jenis = tj.id
                join ptjs p 
                on kj.id_ptj = p.id
                left join tbahagian tb 
                on kj.id_bahagian = tb.id
                order by id desc
                ";
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
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
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
    <meta name="viewport" content="1024">
    <title>Sistem Tempahan Kenderaan | JKN Kedah</title>
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
                <h1 class="welcome-text">Kenderaan Jabatan</h1>
                <div class="left-actions">
                    <a href="#" onclick="openModal('addKenderaanJabatanModal')" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Kenderaan Jabatan
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>

            <!-- <div class="search-container"> -->
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" name="search" class="search-input" placeholder="Cari kenderaan jabatan..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </form>
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">Bil</th>
                            <th class="text-left">PTJ</th>
                            <th class="text-left">Kenderaan</th>
                            <th class="text-left">Maklumat Kenderaan</th>
                            <th class="text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($kenderaans)):
                            $no = 1;
                            foreach ($kenderaans as $kenderaan): ?>
                                <tr>
                                    <td style="text-align: center; font-size: 15px;"><?= $no++ ?></td>
                                    <td class="nama" style="font-size:15px;">
                                        <strong><?= htmlspecialchars($kenderaan['nama_ptj']) ?></strong><br>
                                        <?= htmlspecialchars($kenderaan['bahagian'] ?? 'Tiada Bahagian') ?>
                                    </td>
                                    <td class="nama" style="font-size:15px;">
                                        <strong>No Plat: <?= htmlspecialchars($kenderaan['no_plat']) ?></strong><br>
                                        Model: <?= htmlspecialchars($kenderaan['pengeluar']) ?>
                                        <?= htmlspecialchars($kenderaan['model']) ?><br>
                                        Jenis: <?= htmlspecialchars($kenderaan['jenis_kenderaan']) ?>
                                    </td>

                                    <td class="nama" style="font-size:15px;">
                                        <strong>
                                            Tahun Pengeluaran:
                                            <?= htmlspecialchars($kenderaan['tahun_pengeluaran'] ?? 'Tiada Maklumat') ?>
                                        </strong><br>

                                        Jumlah Tahun:
                                        <?php
                                        if (!empty($kenderaan['tahun_pengeluaran'])) {
                                            echo date('Y') - (int) $kenderaan['tahun_pengeluaran'] . ' tahun';
                                        } else {
                                            echo 'Tiada Maklumat';
                                        }
                                        ?>
                                        <br>

                                        Keadaan Semasa:
                                        <?= htmlspecialchars($kenderaan['keadaan_semasa'] ?? 'Tiada Maklumat') ?>
                                    </td>

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
    <!-- </div> -->

    <div id="addKenderaanJabatanModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addKenderaanJabatanModal')">&times;</span>
                    <h2 class="form-title">Tambah Kenderaan Jabatan</h2>

                </div>
                <form id="addKenderaanJabatanForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="no_plat">No. Plat:</label>
                            <input type="text" id="no_plat" name="no_plat" class="no-plat" required>
                        </div>
                        <div class="form-group">
                            <label for="jenis">Jenis:</label>
                            <select id="jenis" name="jenis" required>
                                <option value="">Pilih Jenis Kenderaan</option>
                                <?php
                                try {
                                    $jenis_sql = "SELECT id, jenis_kenderaan FROM ttempah_jenis ORDER BY jenis_kenderaan";
                                    $jenis_stmt = $pdo->prepare($jenis_sql);
                                    $jenis_stmt->execute();
                                    $jenis_list = $jenis_stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($jenis_list as $jenis_item) {
                                        echo '<option value="' . htmlspecialchars($jenis_item['id']) . '">' .
                                            htmlspecialchars($jenis_item['jenis_kenderaan']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    error_log("Error fetching PTJ: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>

                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pengeluar">Pengeluar:</label>
                            <input type="text" id="pengeluar" name="pengeluar" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()" required>
                        </div>

                        <div class="form-group">
                            <label for="model">Model:</label>
                            <input type="text" id="model" name="model" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()" required>
                        </div>



                    </div>
                    <div class="form-row">
                        <div class="form-group">

                            <label for="ptj">PTJ:</label>
                            <select id="ptj" name="ptj" required>
                                <option value="">-- Pilih PTJ --</option>
                                <?php
                                try {
                                    $ptj_sql = "SELECT id, nama_ptj FROM ptjs ORDER BY nama_ptj";
                                    $ptj_stmt = $pdo->prepare($ptj_sql);
                                    $ptj_stmt->execute();
                                    $ptj_list = $ptj_stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($ptj_list as $ptj_item) {
                                        echo '<option value="' . htmlspecialchars($ptj_item['id']) . '">' .
                                            htmlspecialchars($ptj_item['nama_ptj']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    error_log("Error fetching PTJ: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="bahagian">Bahagian:</label>
                            <select id="bahagian" name="idbahagian" required disabled>
                                <option value="">-- Sila Pilih Bahagian --</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">

                        <div class="form-group">
                            <label for="tahun_pengeluaran">Tahun Pengeluaran:</label>
                            <input type="number" id="tahun_pengeluaran" name="tahun_pengeluaran" class="form-control"
                                placeholder="Contoh: 2020" min="1900" max="2099" required>
                        </div>
                        <div class="form-group">
                            <label for="keadaan_semasa">Keadaan Semasa:</label>
                            <select id="keadaan_semasa" name="keadaan_semasa" required>
                                <option value="Baik">Baik</option>
                                <option value="Rosak">Rosak</option>
                                <option value="Lupus">Lupus</option>
                            </select>
                        </div>


                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <!-- <button type="button" class="btn-batal" onclick="closeModal('addModal')"> -->
                        <button type="button" class="btn btn-cancel" onclick="handleCancel()">

                            <i class="fas fa-times"></i> Batal
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </div>

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
                            <input type="text" id="edit_no_plat" name="no_plat" class="form-control no-plat"
                                placeholder="Contoh: WXY 1234" required>
                        </div>

                        <div class="form-group">
                            <label for="jenis">Jenis:</label>
                            <select id="edit_jenis" name="jenis" required>
                                <option value="">Pilih Jenis Kenderaan</option>
                                <?php
                                try {
                                    $current_jenis = $kenderaan['id_jenis'];

                                    $jenis_sql = "SELECT id, jenis_kenderaan FROM ttempah_jenis ORDER BY jenis_kenderaan";
                                    $jenis_stmt = $pdo->prepare($jenis_sql);
                                    $jenis_stmt->execute();
                                    $jenis_list = $jenis_stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($jenis_list as $jenis_item) {
                                        $selected = ($jenis_item['id'] == $current_jenis) ? 'selected' : '';

                                        echo '<option value="' . htmlspecialchars($jenis_item['id']) . '" ' . $selected . '>' .
                                            htmlspecialchars($jenis_item['jenis_kenderaan']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    error_log("Error fetching PTJ: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>

                        <!-- <div class="form-group">
                            <label for="edit_ptj">
                                PTJ
                            </label>
                            <input type="text" id="edit_ptj" name="ptj" class="form-control" placeholder="Masukkan PTJ"
                                required>
                        </div> -->

                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_pengeluar">
                                Pengeluar
                            </label>
                            <input type="text" id="edit_pengeluar" name="pengeluar" class="form-control"
                                placeholder="Contoh: Toyota/Honda" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_model">
                                Model
                            </label>
                            <input type="text" id="edit_model" name="model" class="form-control"
                                placeholder="Contoh: Camry/Civic" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase()" required>
                        </div>

                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ptj">PTJ:</label>
                            <select id="edit_ptj" name="ptj" required>
                                <option value="">Pilih PTJ</option>
                                <?php
                                try {
                                    $current_ptj = $record['ptj']; // saved value
                                
                                    $ptj_sql = "SELECT id, nama_ptj FROM ptjs ORDER BY nama_ptj";
                                    $ptj_stmt = $pdo->prepare($ptj_sql);
                                    $ptj_stmt->execute();
                                    $ptj_list = $ptj_stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($ptj_list as $ptj_item) {
                                        $selected = ($ptj_item['id'] == $current_ptj) ? 'selected' : '';

                                        echo '<option value="' . htmlspecialchars($ptj_item['id']) . '" ' . $selected . '>' .
                                            htmlspecialchars($ptj_item['nama_ptj']) . '</option>';
                                    }
                                } catch (PDOException $e) {
                                    error_log("Error fetching PTJ: " . $e->getMessage());
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bahagian">Bahagian:</label>
                            <select id="edit_bahagian" name="idbahagian" required disabled>
                                <option value="">-- Sila Pilih Bahagian --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_tahun_pengeluaran">
                                Tahun Pengeluaran
                            </label>
                            <input type="number" id="edit_tahun_pengeluaran" name="tahun_pengeluaran"
                                class="form-control" placeholder="Contoh: 2020" min="1900" max="2099" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_keadaan_semasa">
                                Keadaan Semasa
                            </label>
                            <select id="edit_keadaan_semasa" name="keadaan_semasa" class="form-control" required>
                                <option value="Baik">Baik</option>
                                <option value="Rosak">Rosak</option>
                                <option value="Sederhana">Lupus</option>
                            </select>
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
        document.querySelectorAll('.no-plat').forEach(function (el) {
            el.addEventListener('input', function (e) {
                let value = e.target.value.toUpperCase();

                value = value.replace(/([A-Z])([0-9])/g, '$1 $2');
                value = value.replace(/([0-9])([A-Z])/g, '$1 $2');

                e.target.value = value;
            });
        });

        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        // function openEditModal(kenderaan) {
        //     document.getElementById('edit_id').value = kenderaan.id;
        //     document.getElementById('edit_no_plat').value = kenderaan.no_plat;
        //     document.getElementById('edit_pengeluar').value = kenderaan.pengeluar;
        //     document.getElementById('edit_model').value = kenderaan.model;
        //     document.getElementById('edit_keadaan_semasa').value = kenderaan.keadaan_semasa;
        //     document.getElementById('edit_tahun_pengeluaran').value = kenderaan.tahun_pengeluaran;
        //                 document.getElementById('edit_bahagian').disabled = false;

        //     fetch('get_dropdown_options.php')
        //         .then(response => response.json())
        //         .then(data => {
        //             if (data.success) {
        //                 populateDropdown('idjawatan', data.jawatan, 'jawatan', idjawatan);
        //                 populateDropdown('idgred', data.gred, 'gred', idgred);
        //                 populateDropdown('idptj', data.ptj, 'namaptj', idptj);
        //                 populateDropdown('idbahagian', data.bahagian, 'bahagian', idbahagian);
        //                 populateDropdown('idunit', data.unit, 'unit', idunit, true);
        //                 populateDropdown('edit_bahagian', data.bahagian, 'bahagian', kenderaan.id_bahagian); 

        //             }
        //         })
        //         .catch(error => console.error('Error:', error));

        //     // set PTJ dropdown
        //     const ptjSelect = document.getElementById('edit_ptj');
        //     if (ptjSelect) ptjSelect.value = kenderaan.id_ptj; // must be ID, not name

        //     // set Jenis Kenderaan dropdown if you have one
        //     const jenisSelect = document.getElementById('edit_jenis');
        //     if (jenisSelect) jenisSelect.value = kenderaan.id_jenis; // must be ID

        //     const bahagianSelect = document.getElementById('edit_bahagian');
        //     if (bahagianSelect) bahagianSelect.value = kenderaan.id_bahagian; // must be ID

        //     openModal('editModal');
        // }

       function openEditModal(kenderaan) {
    document.getElementById('edit_id').value = kenderaan.id;
    document.getElementById('edit_no_plat').value = kenderaan.no_plat;
    document.getElementById('edit_pengeluar').value = kenderaan.pengeluar;
    document.getElementById('edit_model').value = kenderaan.model;
    document.getElementById('edit_keadaan_semasa').value = kenderaan.keadaan_semasa;
    document.getElementById('edit_tahun_pengeluaran').value = kenderaan.tahun_pengeluaran;
    document.getElementById('edit_jenis').value = kenderaan.id_jenis;

    const ptjSelect = document.getElementById('edit_ptj');
    const bahagianSelect = document.getElementById('edit_bahagian');

    // set PTJ
    ptjSelect.value = kenderaan.id_ptj;

    // trigger change to load bahagian
    ptjSelect.dispatchEvent(new Event('change'));

    // wait a bit then set bahagian
    setTimeout(() => {
        bahagianSelect.value = kenderaan.id_bahagian;
    }, 300);

    openModal('editModal');
}
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
                    fetch('delete_kenderaan.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + id
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
                                    data.message,
                                    'error'
                                );
                            }
                        });
                }
            });
        }

        function handleCancel() {

            const no_plat = document.getElementById('no_plat').value.trim();
            const ptj = document.getElementById('ptj').value.trim();
            const jenis = document.getElementById('jenis').value.trim();
            const pengeluar = document.getElementById('pengeluar').value.trim();
            const model = document.getElementById('model').value.trim();

            const modal = document.getElementById('addKenderaanJabatanModal');
            const form = document.getElementById('addKenderaanJabatanForm');

            if (no_plat === '' && ptj === '' && jenis === '' && pengeluar === '' && model === '') {
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
                    const form = document.getElementById('addKenderaanJabatanForm');
                    form.reset();
                    const modal = document.getElementById('addKenderaanJabatanModal');
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

        document.getElementById('addKenderaanJabatanForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('add_kenderaan.php', {
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

        document.getElementById('editForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('update_kenderaan.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berjaya!',
                            'Maklumat kenderaan telah dikemaskini!',
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

        const ptjSelect = document.getElementById('ptj');
        const bahagianSelect = document.getElementById('bahagian');
        // const unitSelect = document.getElementById('unit');

        // PTJ change handler
        ptjSelect.addEventListener('change', function () {
            bahagianSelect.innerHTML = '<option value="">-- Sila Pilih Bahagian --</option>';
            // unitSelect.innerHTML = '<option value="">-- Sila Pilih Unit --</option>';
            // unitSelect.disabled = true;

            if (this.value) {
                bahagianSelect.disabled = false;
                // Fetch Bahagian based on selected PTJ
                fetch(`get_departments.php?action=getBahagian&idptj=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            data.data.forEach(bahagian => {
                                const option = document.createElement('option');
                                option.value = bahagian.id;
                                option.textContent = bahagian.bahagian;
                                bahagianSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error loading Bahagian:', error));
            } else {
                bahagianSelect.disabled = true;
            }
        });

        // Bahagian change handler

        const editPtjSelect = document.getElementById('edit_ptj');
        const editBahagianSelect = document.getElementById('edit_bahagian');
        // const unitSelect = document.getElementById('unit');

        // PTJ change handler
        editPtjSelect.addEventListener('change', function () {
    editBahagianSelect.innerHTML = '<option value="">-- Sila Pilih Bahagian --</option>';

    if (this.value) {
        editBahagianSelect.disabled = false;

        fetch(`get_departments.php?action=getBahagian&idptj=${this.value}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(bahagian => {
                        const option = document.createElement('option');
                        option.value = bahagian.id;
                        option.textContent = bahagian.bahagian;
                        editBahagianSelect.appendChild(option);
                    });

                    // ✅ SET SELECTED HERE
                    if (window.selectedBahagianId) {
                        editBahagianSelect.value = window.selectedBahagianId;
                    }
                }
            });
    }
});


    </script>
</body>

</html>