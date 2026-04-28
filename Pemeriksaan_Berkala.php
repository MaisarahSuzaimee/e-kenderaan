<?php
session_start();
require 'config.php';

// Check if we need to fix the table structure
// $check_null_ids = "SELECT COUNT(*) as count FROM tpemeriksaan_berkala WHERE id IS NULL";
// $result = $conn->query($check_null_ids);
// $row = $result->fetch_assoc();

// if ($row['count'] > 0) {
//     // We have NULL IDs, redirect to fix script
//     header("Location: fix_pemeriksaan_table.php");
//     exit;
// }

// Initialize variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$items_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of records
$count_sql = "SELECT COUNT(*) as total FROM tpemeriksaan_berkala";
if (!empty($search)) {
    $count_sql .= " WHERE no_plat LIKE ? OR pusat_pemeriksaan LIKE ? OR catatan LIKE ?";
}
$count_stmt = $conn->prepare($count_sql);
if (!empty($search)) {
    $search_param = "%$search%";
    $count_stmt->bind_param("sss", $search_param, $search_param, $search_param);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Main query
$sql = "SELECT * FROM tpemeriksaan_berkala";
if (!empty($search)) {
    $sql .= " WHERE no_plat LIKE ? OR pusat_pemeriksaan LIKE ? OR catatan LIKE ?";
}
$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";

// Execute the query
$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $items_per_page, $offset);
} else {
    $stmt->bind_param("ii", $items_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$pemeriksaan = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Berkala | JKN Kedah</title>
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
                <h1 class="welcome-text">Pemeriksaan Berkala</h1>
                <div class="left-actions">
                <a href="#" onclick="openAddModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tambah Pemeriksaan Berkala
                </a>
                 <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                    </div>
            </div>

            <form class="search-field" method="GET">
                <div class="search-grid2">
                    <input type="text" class="search-input" name="search"
                        placeholder="Cari pemeriksaan...">
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
                            
                            <th style="text-align: center;">Bil</th>
                            <th>No. Plat</th>
                            <th>Pusat Pemeriksaan</th>
                            <th>Tarikh Pemeriksaan</th>
                            <th>Catatan</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counter = ($page - 1) * $items_per_page + 1;
                        foreach ($pemeriksaan as $row):
                        ?>
                            <tr>
                                
                                <td style="text-align: center;"><?= $counter++ ?></td>
                                <td style="font-size: 15px;"><?= htmlspecialchars($row['no_plat']) ?></td>
                                <td style="font-size: 15px;"><?= htmlspecialchars($row['pusat_pemeriksaan']) ?></td>
                                <td style="font-size: 15px;"><?= htmlspecialchars($row['tarikh_pemeriksaan']) ?></td>
                                <td style="font-size: 15px;"><?= htmlspecialchars($row['catatan']) ?></td>
                                <td class="action-buttons">
                                    <div class="action-cell">
                                        <a
                                            href="#"
                                            onclick="openEditModal(<?= $row['id'] ?>)"
                                            class="action-btn edit-btn"
                                            data-id="<?= $row['id'] ?>"
                                            title="Edit">
                                            <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                        </a>
                                        <a
                                            href="#"
                                            onclick="deletePemeriksaan(<?= $row['id'] ?>, '<?= htmlspecialchars($row['no_plat']) ?>')"
                                            class="action-btn delete-btn"
                                            data-id="<?= $row['id'] ?>"
                                            data-plat="<?= htmlspecialchars($row['no_plat']) ?>"
                                            title="Padam">
                                            <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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

    <!-- Add Modal -->
    <div id="addPemeriksaanModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeAddModal()">&times;</span>
                    <h2 class="form-title">Tambah Pemeriksaan Berkala</h2>
                </div>
                <form id="addPemeriksaanForm" class="styled-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="add_no_plat">
                                No. Plat
                            </label>
                            <input type="text"
                                id="add_no_plat"
                                name="no_plat"
                                class="form-control"
                                
                                required>
                        </div>
                        <div class="form-group">
                            <label for="add_tarikh_pemeriksaan">
                                Tarikh Pemeriksaan
                            </label>
                            <input type="date"
                                id="add_tarikh_pemeriksaan"
                                name="tarikh_pemeriksaan"
                                class="form-control"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="add_pusat_pemeriksaan">
                                Pusat Pemeriksaan
                            </label>
                            <input type="text"
                                id="add_pusat_pemeriksaan"
                                name="pusat_pemeriksaan"
                                class="form-control"
                               
                                required>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <label for="add_catatan">
                            Catatan
                        </label>
                        <textarea id="add_catatan"
                            name="catatan"
                            class="form-control"
                            
                            rows="4"
                            style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;">
                        </textarea>
                    </div>
                    <div class="button-group">
                        <button type="submit"
                            class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button"
                            class="btn btn-cancel"
                            onclick="handleCancel()">
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
                <h2 class="form-title">Kemaskini Pemeriksaan Berkala</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>

            <form id="editForm" class="styled-form">
                <input type="hidden" id="edit_id" name="id">

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_no_plat">
                            No. Plat
                        </label>
                        <input type="text"
                            id="edit_no_plat"
                            name="no_plat"
                            class="form-control"
                            
                            required>
                    </div>

                    <div class="form-group">
                        <label for="edit_tarikh_pemeriksaan">
                            Tarikh Pemeriksaan
                        </label>
                        <input type="date"
                            id="edit_tarikh_pemeriksaan"
                            name="tarikh_pemeriksaan"
                            class="form-control"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="edit_pusat_pemeriksaan">
                            Pusat Pemeriksaan
                        </label>
                        <input type="text"
                            id="edit_pusat_pemeriksaan"
                            name="pusat_pemeriksaan"
                            class="form-control"
                            
                            required>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="edit_catatan">
                        Catatan
                    </label>
                    <textarea id="edit_catatan"
                        name="catatan"
                        class="form-control"
                        rows="4"
                        style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"></textarea>
                </div>

                <div class="button-group">
                    <button type="submit"
                        class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                    <button type="button"
                        class="btn btn-cancel"
                        onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            // Reset form
            document.getElementById('addPemeriksaanForm').reset();

            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('add_tarikh_pemeriksaan').value = today;

            // Show modal
            document.getElementById('addPemeriksaanModal').style.display = 'block';

            // Focus on first field
            document.getElementById('add_no_plat').focus();
        }

        function closeAddModal() {
            document.getElementById('addPemeriksaanModal').style.display = 'none';
        }

        function openEditModal(id) {
            // Show loading indicator
            // Swal.fire({
            //     title: 'Memuat...',
            //     text: 'Sila tunggu sebentar',
            //     allowOutsideClick: false,
            //     didOpen: () => {
            //         Swal.showLoading();
            //     }
            // });

            // Fetch existing data
            fetch(`get_pemeriksaan.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    Swal.close();

                    if (data.success) {
                        // Check if all form elements exist before setting values
                        const idField = document.getElementById('edit_id');
                        const noPlateField = document.getElementById('edit_no_plat');
                        const pusatField = document.getElementById('edit_pusat_pemeriksaan');
                        const dateField = document.getElementById('edit_tarikh_pemeriksaan');
                        const catatanField = document.getElementById('edit_catatan');

                        // Verify all elements exist
                        if (!idField || !noPlateField || !pusatField || !dateField || !catatanField) {
                            console.error('Missing form elements:', {
                                idField: !!idField,
                                noPlateField: !!noPlateField,
                                pusatField: !!pusatField,
                                dateField: !!dateField,
                                catatanField: !!catatanField
                            });
                            throw new Error('Form elements not found');
                        }

                        // Populate form fields
                        idField.value = data.record.id;
                        noPlateField.value = data.record.no_plat;
                        pusatField.value = data.record.pusat_pemeriksaan;
                        dateField.value = data.record.tarikh_pemeriksaan;
                        catatanField.value = data.record.catatan || '';

                        // Show modal
                        document.getElementById('editModal').style.display = 'block';

                        // Focus on first field
                        noPlateField.focus();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ralat!',
                            text: data.message || 'Tidak dapat memuat data pemeriksaan'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat Sistem!',
                        text: 'Sila cuba lagi atau hubungi admin. Error: ' + error.message
                    });
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editForm').reset();
        }

        function deletePemeriksaan(id, noPlat) {
            console.log('Delete function called with ID:', id, 'Plate:', noPlat); // Debug log

            // Ensure id is a number
            id = parseInt(id, 10);

            if (!id || isNaN(id) || id <= 0) {
                Swal.fire({
                    title: 'Ralat!',
                    text: 'ID tidak sah: ' + id,
                    icon: 'error'
                });
                return;
            }

            Swal.fire({
                title: 'Adakah anda pasti?',
                text: `Anda akan memadam pemeriksaan untuk kenderaan: ${noPlat}`,
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
                        text: 'Sila tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch(`delete_pemeriksaan.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berjaya!',
                                    text: data.message || 'Rekod berjaya dipadam',
                                    showConfirmButton: true
                                    
                                }).then(() => {
                                    // Force a full page reload
                                    window.location.href = window.location.pathname;
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ralat!',
                                    text: data.message || 'Ralat semasa memadam rekod',
                                    
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Ralat!',
                                text: 'Ralat sistem: ' + error.message,
                                icon: 'error'
                            });
                        });
                }
            });
        }

        function handleCancel() {
            const no_plat = document.getElementById('add_no_plat').value.trim();
            const pusat_pemeriksaan = document.getElementById('add_pusat_pemeriksaan').value.trim();
            const catatan = document.getElementById('add_catatan').value.trim();

            const modal = document.getElementById('addPemeriksaanModal');
            const form = document.getElementById('addPemeriksaanForm');

            if (no_plat === '' && pusat_pemeriksaan === '' && catatan === ''){
                if(modal) {
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
                    const form = document.getElementById('addPemeriksaanForm');
                    form.reset();
                    const modal = document.getElementById('addPemeriksaanModal');
                    if (modal) {
                        modal.style.display = 'none';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Dibatalkan!',
                        text: 'Borang telah dikosongkan.',
                        showConfirmButton: true

                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener to the add form
            const addForm = document.getElementById('addPemeriksaanForm');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Get form data
                    const formData = new FormData(this);
                    const formObject = {};
                    formData.forEach((value, key) => {
                        formObject[key] = value;
                    });

                    // Show loading indicator
                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Sila tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send data to server
                    fetch('add_pemeriksaan.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(formObject)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('New record added with ID:', data.id);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berjaya!',
                                    text: 'Rekod pemeriksaan telah ditambah',
                                    showConfirmButton: true

                                }).then(() => {
                                    closeAddModal();
                                    // Force a full page reload to ensure all event handlers are properly attached
                                    window.location.href = window.location.pathname + '?added=true';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Ralat!',
                                    text: data.message || 'Gagal menambah rekod',

                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Ralat Sistem!',
                                text: 'Sila cuba lagi atau hubungi admin',

                            });
                        });
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener to the edit form
            const editForm = document.getElementById('editForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get form data
                    const formData = new FormData(this);
                    const formObject = {};
                    formData.forEach((value, key) => {
                        formObject[key] = value;
                    });
                    
                    // Show loading indicator
                    Swal.fire({
                        title: 'Mengemaskini...',
                        text: 'Sila tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Send data to server
                    fetch('update_pemeriksaan.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formObject)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berjaya!',
                                text: 'Rekod pemeriksaan telah dikemaskini',
                                showConfirmButton : true
                            }).then(() => {
                                closeEditModal();
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ralat!',
                                text: data.message || 'Gagal mengemaskini rekod'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Ralat Sistem!',
                            text: 'Sila cuba lagi atau hubungi admin'
                        });
                    });
                });
            } else {
                console.error('Edit form not found');
            }
        });
    </script>
</body>

</html>