<?php
session_start();
require_once 'config.php';

// Initialize variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$items_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of records
$count_sql = "SELECT COUNT(*) as total FROM penyelenggara_kenderaan";
if (!empty($search)) {
    $count_sql .= " WHERE no_plat LIKE ? OR butir_penyelenggaraan LIKE ?";
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

// Main query
$sql = "SELECT p.id, p.no_plat, p.tarikh_penyelenggaraan, 
        p.butir_penyelenggaraan, p.kos_penyelenggaraan, p.harga";

// Add PTJ name if needed
$sql .= ", ptj.namaptj";
$sql .= " FROM penyelenggara_kenderaan p";
$sql .= " LEFT JOIN tptj ptj ON p.ptj_id = ptj.id";

// Add WHERE clause for search
if (!empty($search)) {
    $sql .= " WHERE p.no_plat LIKE ? OR p.butir_penyelenggaraan LIKE ?";
}

$sql .= " ORDER BY p.id DESC LIMIT ? OFFSET ?";

// Execute the query
$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("ssii", $search_param, $search_param, $items_per_page, $offset);
} else {
    $stmt->bind_param("ii", $items_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$penyelenggaraan = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penyelenggaraan | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="CSS/STK.css" />
    <link rel="stylesheet" href="CSS/layout.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
</head>

<body>
    <?= include 'dashboard.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Penyelenggaraan</h1>
                <div class="left-actions">
                    <a href="#" onclick="openAddModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Penyelenggaraan
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>
            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" name="search" class="search-input" placeholder="Cari penyelenggaraan"
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
                            
                            <th style="text-align: center;">Bil</th>
                            <th>PTJ</th>
                            <th>No. Plat</th>
                            <th>Tarikh & Butir Penyelenggaraan</th>
                            <th>Kos Penyelenggaraan (RM) / Harga (RM)</th>
                            
                            <th>Peratus kos berbanding harga</th>
                            
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = ($page - 1) * $items_per_page + 1;
                        foreach ($penyelenggaraan as $row):
                        ?>
                        <tr>
                            
                            <td style="font-size:15px; text-align:center;"><?= $counter++ ?></td>
                            <td style="font-size:15px;"><?= htmlspecialchars($row['namaptj'] ?? '-') ?></td>
                            <td style="font-size:15px;"><?= htmlspecialchars($row['no_plat']) ?></td>
                            <td class="nama" style="font-size: 15px;">
                                <strong><?= htmlspecialchars($row['tarikh_penyelenggaraan']) ?></strong><br>
                                <?= htmlspecialchars($row['butir_penyelenggaraan']) ?>
                            </td>
                            <td class="nama" style="font-size: 15px;">
                                <strong><?= htmlspecialchars($row['kos_penyelenggaraan']) ?></strong><br>
                                <?= htmlspecialchars($row['harga']) ?>
                            </td>
                            <td style="font-size:15px;"><?= number_format(($row['harga'] > 0 ? ($row['kos_penyelenggaraan'] / $row['harga'] * 100) : 0), 2) ?>%
                            </td>
                            
                            <td class="action-buttons">
                                <div class="action-cell">
                                    <a href="#" onclick="openEditModal(<?= $row['id'] ?>)" class="action-btn"
                                        title="Edit">
                                        <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                    </a>

                                    <a href="#" onclick="deletePenyelenggaraan(<?= $row['id'] ?>)" class="action-btn"
                                        title="Padam">
                                        <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($penyelenggaraan)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Tiada rekod ditemui</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link page-nav" title="Muka Pertama">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link page-nav" title="Sebelum">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <?php endif; ?>
                    <a href="?page=<?= $page ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link active">
                        <?= $page ?>
                    </a>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link page-nav" title="Seterusnya">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>"
                        class="page-link page-nav" title="Muka Terakhir">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="addPenyelenggaraanModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeAddModal()">&times;</span>
                    <h2 class="form-title">Tambah Penyelenggaraan Baru</h2>
                </div>
                <form id="addPenyelenggaraanForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="no_plat">No. Plat:</label>
                            <input type="text" id="no_plat" name="no_plat" required>
                        </div>

                        <div class="form-group">
                            <label for="tarikh">Tarikh Penyelenggaraan:</label>
                            <input type="date" id="tarikh" name="tarikh_penyelenggaraan" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="butir">Butir Penyelenggaraan:</label>
                        <textarea id="butir" name="butir_penyelenggaraan" required rows="4" class="form-control"
                            style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;">
                        </textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kos">Kos Penyelenggaraan (RM):</label>
                            <input type="number" id="kos" name="kos_penyelenggaraan" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="harga">Harga (RM):</label>
                            <input type="number" id="harga" name="harga" step="0.01">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ptj">PTJ:</label>
                        <select id="ptj" name="ptj_id">
                            <option value="">Pilih PTJ</option>
                            <?php
                            $ptj_query = "SELECT id, namaptj FROM tptj ORDER BY namaptj";
                            $ptj_result = $conn->query($ptj_query);
                            while ($ptj = $ptj_result->fetch_assoc()) {
                                echo "<option value='" . $ptj['id'] . "'>" . htmlspecialchars($ptj['namaptj']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="button-group">
                        <button type="button" onclick="submitAddForm()" class="btn btn-success">
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

    <div id="editModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2 class="form-title">Kemaskini Maklumat Kenderaan</h2>

                </div>
                <!-- <div class="modal-body"> -->
                <form id="editForm">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_no_plat">
                                No. Plat</label>
                            <input type="text" id="edit_no_plat" name="no_plat" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_ptj">
                                PTJ</label>
                            <select id="edit_ptj" name="ptj_id">
                                <option value="">Pilih PTJ</option>
                                <?php
                                $ptj_query = "SELECT id, namaptj FROM tptj ORDER BY namaptj";
                                $ptj_result = $conn->query($ptj_query);
                                while ($ptj = $ptj_result->fetch_assoc()) {
                                    echo "<option value='" . $ptj['id'] . "'>" . htmlspecialchars($ptj['namaptj']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_tarikh">
                                Tarikh Penyelenggaraan
                            </label>
                            <input type="date" id="edit_tarikh" name="tarikh_penyelenggaraan" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_kos">Kos Penyelenggaraan (RM)</label>
                            <input type="number" id="edit_kos" name="kos_penyelenggaraan" step="0.01" min="0.01"
                                required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1 0 100%;">
                            <label for="edit_harga">Harga (RM)</label>
                            <input type="number" id="edit_harga" name="harga" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1 0 100%;">
                            <label for="edit_butir"><i class="fas fa-clipboard-list"></i> Butir Penyelenggaraan</label>
                            <textarea id="edit_butir" name="butir_penyelenggaraan" class="form-control" required
                                rows="4"
                                style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"></textarea>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="button" onclick="submitEditForm()" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button href="#" type="button" onclick="closeEditModal()" class="btn btn-cancel">
                            <i class="fas fa-times"></i> Batal
                        </button>

                    </div>

                </form>
                <!-- </div> -->
            </div>
        </div>
    </div>

    <script>
    // Add your JavaScript functions here
    function openAddModal() {
        document.getElementById('addPenyelenggaraanModal').style.display = 'block';
    }

    function closeAddModal() {
        document.getElementById('addPenyelenggaraanModal').style.display = 'none';
        document.getElementById('addPenyelenggaraanForm').reset();
    }

    function submitAddForm() {
        // Get form data
        const formData = new FormData(document.getElementById('addPenyelenggaraanForm'));

        // Validate form
        const no_plat = formData.get('no_plat');
        const tarikh = formData.get('tarikh_penyelenggaraan');
        const butir = formData.get('butir_penyelenggaraan');
        const kos = parseFloat(formData.get('kos_penyelenggaraan'));

        if (!no_plat) {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'No. Plat diperlukan'
            });
            return;
        }

        if (!tarikh) {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Tarikh penyelenggaraan diperlukan'
            });
            return;
        }

        if (!butir) {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Butir penyelenggaraan diperlukan'
            });
            return;
        }

        if (isNaN(kos) || kos <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Kos penyelenggaraan mesti lebih dari 0'
            });
            return;
        }

        // Send POST request to server
        fetch('add_penyelenggara.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berjaya!',
                        text: data.message,
                        showConfirmButton: true
                    }).then(() => {
                        closeAddModal();
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat!',
                        text: data.message || 'Ralat semasa menambah penyelenggaraan'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat!',
                    text: 'Ralat semasa menambah penyelenggaraan'
                });
            });
    }

    // Function to open edit modal and populate form
    function openEditModal(id) {
        // Show loading indicator
        Swal.fire({
            title: 'Memuat...',
            text: 'Sila tunggu',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch record data
        fetch(`get_penyelenggara.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                Swal.close();

                if (data.success) {
                    const record = data.data;

                    // Populate form fields
                    document.getElementById('edit_id').value = record.id;
                    document.getElementById('edit_no_plat').value = record.no_plat || '';
                    document.getElementById('edit_tarikh').value = record.tarikh_penyelenggaraan || '';
                    document.getElementById('edit_butir').value = record.butir_penyelenggaraan || '';
                    document.getElementById('edit_kos').value = record.kos_penyelenggaraan || '';
                    document.getElementById('edit_harga').value = record.harga || '';

                    // Set PTJ dropdown
                    const ptjSelect = document.getElementById('edit_ptj');
                    if (record.ptj_id) {
                        ptjSelect.value = record.ptj_id;
                    } else {
                        ptjSelect.selectedIndex = 0;
                    }

                    // Show modal
                    document.getElementById('editModal').style.display = 'block';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat!',
                        text: data.message || 'Gagal mendapatkan data rekod'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);

                Swal.close();

                Swal.fire({
                    icon: 'error',
                    title: 'Ralat Sistem!',
                    text: 'Sila cuba lagi atau hubungi admin'
                });
            });
    }

    function handleCancel() {

        const no_plat = document.getElementById('no_plat').value.trim();
        const tarikh = document.getElementById('tarikh').value.trim();
        const butir = document.getElementById('butir').value.trim();
        const kos = document.getElementById('kos').value.trim();
        const harga = document.getElementById('harga').value.trim();
        const ptj = document.getElementById('ptj').value.trim();

        const modal = document.getElementById('addPenyelenggaraanModal');
        const form = document.getElementById('addPenyelenggaraanForm');

        if (no_plat === '' && tarikh === '' && butir === '' && kos === '' && harga === '' && ptj === '') {
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
                const form = document.getElementById('addPenyelenggaraanForm');
                form.reset();

                // Close the modal if it exists
                const modal = document.getElementById('addPenyelenggaraanModal');
                if (modal) {
                    modal.style.display = 'none';
                }

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Dibatalkan!',
                    text: 'Borang telah dikosongkan.',
                    showConfirmButton: true

                });
            }
        });
    }

    // Function to close edit modal
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editForm').reset();
    }

    function submitEditForm() {
        // Get form data
        const formData = new FormData(document.getElementById('editForm'));

        // Validate form
        const no_plat = formData.get('no_plat');
        const tarikh = formData.get('tarikh_penyelenggaraan');
        const butir = formData.get('butir_penyelenggaraan');
        const kos = parseFloat(formData.get('kos_penyelenggaraan'));

        if (!no_plat) {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'No. Plat diperlukan'
            });
            return;
        }

        if (!tarikh) {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Tarikh penyelenggaraan diperlukan'
            });
            return;
        }

        if (!butir) {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Butir penyelenggaraan diperlukan'
            });
            return;
        }

        if (isNaN(kos) || kos <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Kos penyelenggaraan mesti lebih dari 0'
            });
            return;
        }

        // Send POST request to server
        fetch('update_penyelenggara.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berjaya!',
                        text: data.message,
                        showConfirmButton: true
                    }).then(() => {
                        closeEditModal();
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat!',
                        text: data.message || 'Ralat semasa mengemaskini penyelenggaraan'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat!',
                    text: 'Ralat semasa mengemaskini penyelenggaraan'
                });
            });
    }

    function deletePenyelenggaraan(id) {
        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Rekod ini akan dipadam secara kekal!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Padam!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading indicator
                Swal.fire({
                    title: 'Memadam...',
                    text: 'Sila tunggu',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create form data
                const formData = new FormData();
                formData.append('id', id);

                // Send delete request
                fetch('delete_penyelenggara.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);

                        Swal.close();

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berjaya!',
                                text: data.message || 'Rekod berjaya dipadam',
                                showConfirmButton: true
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Ralat!',
                                text: data.message || 'Gagal memadam rekod',
                                icon: 'error',
                                showConfirmButton: true,
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        Swal.close();

                        Swal.fire({
                            title: 'Ralat Sistem!',
                            text: 'Sila cuba lagi atau hubungi admin',
                            icon: 'error',
                            showConfirmButton: true
                        });
                    });
            }
        });
    }
    </script>
</body>

</html>