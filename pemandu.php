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

// Check if we need to fix the table structure
try {
    // Check for duplicate or NULL primary keys
    $check_sql = "SELECT COUNT(*) as count FROM (
                    SELECT id, COUNT(*) as count 
                    FROM tpemandu 
                    GROUP BY id 
                    HAVING count > 1 OR id IS NULL
                  ) as duplicates";
    $check_stmt = $pdo->query($check_sql);
    $has_issues = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;

    if ($has_issues) {
        // Redirect to fix script
        header("Location: fix_pemandu_table.php");
        exit;
    }
} catch (PDOException $e) {
    // Log error but continue
    error_log("Error checking table structure: " . $e->getMessage());
}

try {
    // Fetch Jawatan
    $jawatan_sql = "SELECT id, desc_jawatan FROM jawatans ORDER BY desc_jawatan";
    $jawatan_stmt = $pdo->prepare($jawatan_sql);
    $jawatan_stmt->execute();
    $jawatan_list = $jawatan_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Gred
    $gred_sql = "SELECT id, kod_gred FROM greds ORDER BY kod_gred";
    $gred_stmt = $pdo->prepare($gred_sql);
    $gred_stmt->execute();
    $gred_list = $gred_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch PTJ
    $ptj_sql = "SELECT id, nama_ptj FROM ptjs ORDER BY nama_ptj";
    $ptj_stmt = $pdo->prepare($ptj_sql);
    $ptj_stmt->execute();
    $ptj_list = $ptj_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize bahagian and unit lists as empty arrays
    $bahagian_list = [];
    $unit_list = [];
} catch (PDOException $e) {
    error_log("Error fetching dropdown data: " . $e->getMessage());
    // Initialize empty arrays if queries fail
    $jawatan_list = $gred_list = $ptj_list = $bahagian_list = $unit_list = [];
}

// Initialize variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$items_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of records
$count_sql = "SELECT COUNT(*) as total FROM tpemandu";
if (!empty($search)) {
    $count_sql .= " WHERE namapemandu LIKE ? OR nokp LIKE ?";
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

// Modify your main query to include LIMIT and OFFSET
$sql = "SELECT 
    p.*,
    j.desc_jawatan as nama_jawatan,
    g.kod_gred as nama_gred,
    ptj.nama_ptj as nama_ptj,
    b.bahagian as nama_bahagian,
    u.unit as nama_unit
FROM 
    tpemandu p
    LEFT JOIN jawatans j ON p.idjawatan = j.id
    LEFT JOIN greds g ON p.idgred = g.id
    LEFT JOIN ptjs ptj ON p.idptj = ptj.id
    LEFT JOIN tbahagian b ON p.idbahagian = b.id
    LEFT JOIN tunit u ON p.idunit = u.id";
if (!empty($search)) {
    $sql .= " WHERE p.namapemandu LIKE ? OR p.nokp LIKE ?";
}
$sql .= " ORDER BY p.id DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("ssii", $search_param, $search_param, $items_per_page, $offset);
} else {
    $stmt->bind_param("ii", $items_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$pemandu = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Pemandu | JKN Kedah</title>
    <link rel="stylesheet" href="CSS/STK2.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>

    </style>
</head>

<body>
    <?= include 'dashboard2.php' ?>

    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Pemandu</h1>
                <div class="left-actions">
                    <a href="#" onclick="openAddModal()" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Tambah Pemandu
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>

            <form action="" method="GET" class="search-field">
                <div class="search-grid2">
                    <input type="text" class="search-input" name="search" placeholder="Cari pemandu...">
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

                            <th>Bil.</th>
                            <th>Butiran Pemandu</th>
                            <th>Ptj / Bahagian / Unit</th>
                            <th>Jawatan / Gred</th>
                            <th>Status</th>
                            <th style="width: 100px;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $offset + 1;
                        foreach ($pemandu as $row):
                            ?>
                            <tr data-id="<?= htmlspecialchars($row['id']) ?>">

                                <td style="text-align: center; font-size: 15px;"><?= $no++ ?></td>
                                <td class="nama" style="font-size: 15px;">
                                    <strong><?= htmlspecialchars($row['namapemandu']) ?></strong><br>
                                    <?= htmlspecialchars($row['nokp']) ?><br>
                                    <?= htmlspecialchars($row['notelefon']) ?>
                                </td>
                                <td class="nama" style="font-size: 15px;">
                                    <strong><?= htmlspecialchars($row['nama_ptj']) ?></strong><br>
                                    <?= htmlspecialchars($row['nama_bahagian']) ?><br>
                                    <?= htmlspecialchars($row['nama_unit']) ?>
                                </td>
                                <td class="nama" style="font-size: 15px;">
                                    <strong><?= htmlspecialchars($row['nama_jawatan']) ?></strong><br>
                                    <?= htmlspecialchars($row['nama_gred']) ?><br>
                                </td>

                                <td>
                                    <label class="switch">
                                        <input type="checkbox" class="status-toggle" data-id="<?= $row['id'] ?>"
                                            <?= ($row['status'] == 'Aktif') ? 'checked' : '' ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td class="action-buttons">
                                    <div class="action-cell">
                                        <a href="#" onclick="openEditModal(
                                                        '<?= htmlspecialchars($row['id']) ?>', 
                                                        '<?= htmlspecialchars($row['namapemandu']) ?>', 
                                                        '<?= htmlspecialchars($row['nokp']) ?>', 
                                                        '<?= htmlspecialchars($row['idjawatan']) ?>', 
                                                        '<?= htmlspecialchars($row['idgred']) ?>', 
                                                        '<?= htmlspecialchars($row['idptj']) ?>', 
                                                        '<?= htmlspecialchars($row['idbahagian']) ?>', 
                                                        '<?= htmlspecialchars($row['idunit']) ?>', 
                                                        '<?= htmlspecialchars($row['notelefon']) ?>', 
                                                        '<?= htmlspecialchars($row['status']) ?>',
                                                        '<?= htmlspecialchars($row['catatan']) ?>'
                                                    )" class="action-btn" title="Edit">
                                            <i class="fas fa-edit" style="color: #eb7d00;"></i>
                                        </a>
                                        <a href="#" onclick="deletePemandu(
                                                        '<?= htmlspecialchars($row['id']) ?>', 
                                                        '<?= htmlspecialchars($row['namapemandu']) ?>'
                                                    ); return false;" class="action-btn" title="Padam">
                                            <i class="fas fa-trash" style="color: #E74C3C;"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
    <div id="addPemanduModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('addPemanduModal')">&times;</span>
                    <h2 class="form-title">Tambah Pemandu Baru</h2>

                </div>
                <form id="addPemanduForm" method="POST">

                    <div class="form-group">
                        <label for="nama_pemandu">Nama Pemandu:</label>
                        <input type="text" id="nama_pemandu" name="nama_pemandu" required
                            style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="no_kp">No. KP:</label>
                            <input type="text" id="no_kp" name="no_kp" maxlength="14" inputmode="numeric"
                                oninput="formatIC(this)" required>
                        </div>

                        <div class="form-group">
                            <label for="jawatan">Jawatan:</label>
                            <select id="jawatan" name="jawatan" required>
                                <option value="">-- Sila Pilih Jawatan --</option>
                                <?php foreach ($jawatan_list as $jawatan): ?>
                                    <option value="<?= htmlspecialchars($jawatan['id']) ?>">
                                        <?= htmlspecialchars($jawatan['desc_jawatan']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gred">
                                Gred:
                            </label>
                            <select id="gred" name="gred" required>
                                <option value="">-- Sila Pilih Gred --</option>
                                <?php foreach ($gred_list as $gred): ?>
                                    <option value="<?= htmlspecialchars($gred['id']) ?>">
                                        <?= htmlspecialchars($gred['kod_gred']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="ptj">
                                PTJ:
                            </label>
                            <select id="ptj" name="ptj" required>
                                <option value="">-- Sila Pilih PTJ --</option>
                                <?php foreach ($ptj_list as $ptj): ?>
                                    <option value="<?= htmlspecialchars($ptj['id']) ?>">
                                        <?= htmlspecialchars($ptj['nama_ptj']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bahagian">Bahagian:</label>
                            <select id="bahagian" name="bahagian" required disabled>
                                <option value="">-- Sila Pilih Bahagian --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="unit">Unit:</label>
                            <select id="unit" name="unit" disabled>
                                <option value="">-- Sila Pilih Unit --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="no_telefon">No. Telefon:</label>
                            <input type="text" id="no_telefon" name="no_telefon" maxlength="12" inputmode="numeric"
                                oninput="formatPhone(this)" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="catatan">Catatan:</label>
                        <textarea type="text" id="catatan" name="catatan" rows="3" class="form-control"
                            style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"></textarea>
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

    <!-- Modal for Edit -->
    <div id="editModal" class="modal2">
        <div class="modal-content2">
            <div class="container">
                <div class="modal-header">
                    <span class="close" onclick="closeModal('editModal')">&times;</span>
                    <h2 class="form-title"></i> Edit Pemandu</h2>

                </div>
                <form id="editForm" method="POST">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="form-group" style="margin-bottom: 30px;">
                        <label for="namapemandu">Nama Pemandu:</label>
                        <input type="text" id="namapemandu" name="namapemandu" class="form-control" required>
                    </div>
                    <div class="form-grid">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nokp">No. KP:</label>
                                <input type="text" id="nokp" name="nokp" maxlength="14" inputmode="numeric"
                                    oninput="formatIC(this)" required>
                            </div>

                            <div class="form-group">
                                <label for="idjawatan">Jawatan:</label>
                                <select id="idjawatan" name="idjawatan" class="form-control" required></select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="idgred">Gred:</label>
                                <select id="idgred" name="idgred" class="form-control" required></select>
                            </div>

                            <div class="form-group">
                                <label for="idptj">PTJ:</label>
                                <select id="edit_ptj" name="idptj" class="form-control" required>
                                    <option value="">Pilih PTJ</option>
                                <?php
                                try {
                                    $current_ptj = $pemandu['idptj']; // saved value
                                
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
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="idbahagian">Bahagian:</label>
                                <select id="edit_bahagian" name="idbahagian" class="form-control" required></select>
                            </div>


                            <div class="form-group">
                                <label for="idunit">Unit:</label>
                                <select id="idunit" name="idunit" class="form-control"></select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="notelefon">No. Telefon:</label>
                                <input type="text" id="notelefon" name="notelefon" maxlength="12" inputmode="numeric"
                                    oninput="formatPhone(this)" required>
                            </div>

                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select id="edit_status" name="status" class="form-control" required>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="catatan">Catatan:</label>
                            <textarea type="text" id="edit_catatan" name="catatan" rows="3" class="form-control"
                                style="resize: vertical; font-family: Arial, Helvetica, sans-serif; font-size: medium;"></textarea>

                        </div>
                    </div>

                    <div class="button-group" style="justify-content: flex-end;">

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

    <div id="toast" class="toast"><i class="fas fa-circle-check"></i></div>

    <script>
        function showToast(message, type = "success") {
            const toast = document.getElementById("toast");

            toast.className = "toast " + type;
            toast.innerText = message;
            toast.classList.add("show");

            setTimeout(() => {
                toast.classList.remove("show");
            }, 2500);
        }

        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {

                let checkbox = this;
                let userId = checkbox.dataset.id;
                let status = checkbox.checked ? 'Aktif' : 'Tidak Aktif';

                fetch('update_status_pemandu.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id=' + userId + '&status=' + status
                })
                    .then(response => response.text())
                    .then(data => {
                        if (data.trim() === "Berjaya") {
                            // Show toast longer (e.g., 3 seconds)
                            showToast("Status berjaya dikemaskini!", "success", 1500);

                            // Redirect after toast duration
                            setTimeout(() => {
                                location.reload();
                            }, 1500);

                        } else {
                            checkbox.checked = !checkbox.checked;
                            showToast("Kemaskini gagal!", "error", 1500);
                        }
                    })
                    .catch(error => {
                        checkbox.checked = !checkbox.checked;
                        showToast("Ralat sistem!", "error", 1500);
                    });
            });
        });
        // Modal functions
        function openEditModal(id, namapemandu, nokp, idjawatan, idgred, idptj, idbahagian, idunit, notelefon, status, catatan) {

    // Set basic fields
    document.getElementById('edit_id').value = id;
    document.getElementById('namapemandu').value = namapemandu;
    document.getElementById('nokp').value = nokp;
    document.getElementById('notelefon').value = notelefon || '';
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_catatan').value = catatan || '';

    // 🔥 store selected values globally (important)
    window.selectedJawatan = idjawatan;
    window.selectedGred = idgred;
    window.selectedPtj = idptj;
    window.selectedBahagian = idbahagian;
    window.selectedUnit = idunit;

    // 🔥 load all dropdowns
    loadEditDropdowns();

    document.getElementById('editModal').style.display = 'block';
}


function loadEditDropdowns() {
    fetch('get_dropdown_options.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {

                populateDropdown('idjawatan', data.jawatan, 'desc_jawatan', window.selectedJawatan);
                populateDropdown('idgred', data.gred, 'kod_gred', window.selectedGred);
                populateDropdown('edit_ptj', data.ptj, 'nama_ptj', window.selectedPtj);

                // trigger PTJ change to load bahagian
                const ptjSelect = document.getElementById('edit_ptj');
                ptjSelect.dispatchEvent(new Event('change'));
            }
        });
}

const editPtjSelect = document.getElementById('edit_ptj');
const editBahagianSelect = document.getElementById('edit_bahagian');
const editUnitSelect = document.getElementById('idunit');

editPtjSelect.addEventListener('change', function () {
    editBahagianSelect.innerHTML = '<option value="">-- Sila Pilih Bahagian --</option>';
    editUnitSelect.innerHTML = '<option value="">-- Sila Pilih Unit --</option>';
    editUnitSelect.disabled = true;

    if (this.value) {
        editBahagianSelect.disabled = false;

        fetch(`get_departments.php?action=getBahagian&idptj=${this.value}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(b => {
                        const option = new Option(b.bahagian, b.id);
                        editBahagianSelect.add(option);
                    });

                    // ✅ SET SELECTED HERE (IMPORTANT)
                    if (window.selectedBahagian) {
                        editBahagianSelect.value = window.selectedBahagian;
                    }

                    // trigger unit load
                    editBahagianSelect.dispatchEvent(new Event('change'));
                }
            });
    } else {
        editBahagianSelect.disabled = true;
    }
});

editBahagianSelect.addEventListener('change', function () {
    editUnitSelect.innerHTML = '<option value="">-- Sila Pilih Unit --</option>';

    if (this.value) {
        editUnitSelect.disabled = false;

        fetch(`get_departments.php?action=getUnit&idbahagian=${this.value}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(u => {
                        const option = new Option(u.unit, u.id);
                        editUnitSelect.add(option);
                    });

                    // ✅ SET SELECTED UNIT
                    if (window.selectedUnit) {
                        editUnitSelect.value = window.selectedUnit;
                    }
                }
            });
    } else {
        editUnitSelect.disabled = true;
    }
});

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Delete confirmation
        function deletePemandu(id, namaPemandu) {
            // Show confirmation dialog with driver's name
            Swal.fire({
                title: 'Adakah anda pasti?',
                text: `Anda akan memadam pemandu: ${namaPemandu}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Padam!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Sila tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send delete request
                    fetch(`delete_pemandu.php?id=${encodeURIComponent(id)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Berjaya!',
                                    text: data.message || 'Rekod berjaya dipadam',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Ralat!',
                                    text: data.message || 'Gagal memadam rekod',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Ralat Sistem!',
                                text: 'Sila cuba lagi atau hubungi admin',
                                icon: 'error'
                            });
                        });
                }
            });
        }

        // Form submission
        document.getElementById('editForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitButton.disabled = true;

            fetch('update_pemandu.php', {
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
                })
                .finally(() => {
                    // Restore button state
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                });
        });

        // Helper function to populate dropdowns
        function populateDropdown(elementId, data, labelField, selectedValue, allowEmpty = false) {
            const select = document.getElementById(elementId);
            select.innerHTML = allowEmpty ? '<option value="">-- Pilih --</option>' : '';

            data.forEach(item => {
                const option = new Option(item[labelField], item.id);
                select.add(option);
            });

            if (selectedValue) {
                select.value = selectedValue;
            }
        }

        function openAddModal() {
            document.getElementById('addPemanduModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function handleCancel() {

            const pemandu = document.getElementById('nama_pemandu').value.trim();
            const no_kp = document.getElementById('no_kp').value.trim();
            const jawatan = document.getElementById('jawatan').value.trim();
            const gred = document.getElementById('gred').value.trim();
            const ptj = document.getElementById('ptj').value.trim();
            const bahagian = document.getElementById('bahagian').value.trim();
            const unit = document.getElementById('unit').value.trim();
            const no_telefon = document.getElementById('no_telefon').value.trim();
            const catatan = document.getElementById('catatan').value.trim();

            const modal = document.getElementById('addPemanduModal');
            const form = document.getElementById('addPemanduForm');

            if (pemandu === '' && no_kp === '' && jawatan === '' && gred === '' && ptj === '' &&
                bahagian === '' && unit === '' && no_telefon === '' && catatan === '') {
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
                    const form = document.getElementById('addPemanduForm');
                    form.reset();

                    // Close the modal if it exists
                    const modal = document.getElementById('addPemanduModal');
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
        // Add this to handle ESC key press to cancel
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('addPemanduModal');
                if (modal && modal.style.display === 'block') {
                    handleCancel();
                }
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add event listener to the form
            const addPemanduForm = document.getElementById('addPemanduForm');
            if (addPemanduForm) {
                addPemanduForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    addNewPemandu();
                });
            }

            // PTJ change handler for cascading dropdowns
            const ptjSelect = document.getElementById('ptj');
            const bahagianSelect = document.getElementById('bahagian');
            const unitSelect = document.getElementById('unit');

            // PTJ change handler
            ptjSelect.addEventListener('change', function () {
                bahagianSelect.innerHTML = '<option value="">-- Sila Pilih Bahagian --</option>';
                unitSelect.innerHTML = '<option value="">-- Sila Pilih Unit --</option>';
                unitSelect.disabled = true;

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
            bahagianSelect.addEventListener('change', function () {
                unitSelect.innerHTML = '<option value="">-- Sila Pilih Unit --</option>';

                if (this.value) {
                    unitSelect.disabled = false;
                    // Fetch Unit based on selected Bahagian
                    fetch(`get_departments.php?action=getUnit&idbahagian=${this.value}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                data.data.forEach(unit => {
                                    const option = document.createElement('option');
                                    option.value = unit.id;
                                    option.textContent = unit.unit;
                                    unitSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error loading Unit:', error));
                } else {
                    unitSelect.disabled = true;
                }
            });
        });

        // Function to add new pemandu
        function addNewPemandu() {
            // Show loading indicator
            Swal.fire({
                title: 'Memproses...',
                text: 'Sila tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Get form data
            const form = document.getElementById('addPemanduForm');
            const formData = new FormData(form);

            // Send data to server
            fetch('add_pemandu.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Berjaya!',
                            text: 'Pemandu baru telah ditambah',
                            confirmButtonColor: '#3085d6'
                        }).then(() => {
                            // Reset form and close modal
                            form.reset();
                            closeModal('addPemanduModal');

                            // Reload page to show new data
                            window.location.reload();
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            icon: 'error',
                            title: 'Ralat!',
                            text: data.message || 'Gagal menambah pemandu',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat Sistem!',
                        text: 'Sila cuba lagi atau hubungi admin',
                        confirmButtonColor: '#3085d6'
                    });
                });
        }


        function formatPhone(input) {

            let value = input.value.replace(/[^0-9]/g, '');
            value = value.substring(0, 11);

            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            }

            input.value = value;
        }


        function formatIC(input) {

            let value = input.value.replace(/[^0-9]/g, '');
            value = value.substring(0, 14);

            if (value.length > 6 && value.length <= 8) {
                value = value.substring(0, 6) + '-' + value.substring(6);
            } else if (value.length > 8) {
                value = value.substring(0, 6) + '-' +
                    value.substring(6, 8) + '-' +
                    value.substring(8);
            }

            input.value = value;
        }
    </script>
</body>

</html>