<?php

session_start();
require 'config.php';

// Ensure superadmin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Backup directory
$backupDir = __DIR__ . '/backups/';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Messages
$message = '';
$messageType = '';

// ---------------------- BACKUP ----------------------
if (isset($_POST['create_backup'])) {
    $backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

    $mysqli = new mysqli('localhost', 'teknikal_db_ekenderaan', 'q^=TFAt&Z3I4u{Qc', 'teknikal_db_ekenderaan');
    if ($mysqli->connect_error) {
        $message = "Database connection failed: " . $mysqli->connect_error;
        $messageType = 'error';
    } else {
        $handle = fopen($backupFile, 'w');
        if (!$handle) {
            $message = "Failed to create backup file.";
            $messageType = 'error';
        } else {
            $tables = $mysqli->query("SHOW TABLES");
            while ($row = $tables->fetch_array()) {
                $table = $row[0];

                // Write CREATE TABLE
                $res = $mysqli->query("SHOW CREATE TABLE `$table`")->fetch_assoc();
                fwrite($handle, $res['Create Table'] . ";\n\n");

                // Write INSERT data
                $result = $mysqli->query("SELECT * FROM `$table`");
                while ($data = $result->fetch_assoc()) {
                    $columns = array_keys($data);
                    $values = array_map([$mysqli, 'real_escape_string'], array_values($data));
                    $sql = "INSERT INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES ('" . implode("','", $values) . "');\n";
                    fwrite($handle, $sql);
                }
                fwrite($handle, "\n\n");
            }
            fclose($handle);

            // Update last_backup in system_settings
            $updateStmt = $conn->prepare("INSERT INTO system_settings (setting, value) VALUES ('last_backup', NOW()) 
                ON DUPLICATE KEY UPDATE value = NOW()");
            $updateStmt->execute();

            logSystemAction($conn, 'info', 'Database backup created: ' . basename($backupFile));

            $message = "Backup berjaya dibuat: " . basename($backupFile);
            $messageType = 'success';
        }
    }
}

// ---------------------- RESTORE ----------------------
if (isset($_POST['restore_backup']) && isset($_FILES['backup_file'])) {
    $uploadedFile = $_FILES['backup_file'];
    if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
        $fileInfo = pathinfo($uploadedFile['name']);
        if ($fileInfo['extension'] === 'sql') {

            $mysqli = new mysqli('localhost', 'teknikal_db_ekenderaan', 'q^=TFAt&Z3I4u{Qc', 'teknikal_db_ekenderaan');
            if ($mysqli->connect_error) {
                $message = "Database connection failed: " . $mysqli->connect_error;
                $messageType = 'error';
            } else {
                // Disable FK checks
                $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

                // Drop all tables
                $tablesResult = $mysqli->query("SHOW TABLES");
                while ($row = $tablesResult->fetch_array()) {
                    $mysqli->query("DROP TABLE IF EXISTS `{$row[0]}`");
                }

                // Restore from uploaded .sql file
                $handle = fopen($uploadedFile['tmp_name'], 'r');
                if (!$handle) {
                    $message = "Failed to open uploaded file.";
                    $messageType = 'error';
                } else {
                    $query = '';
                    $success = true;

                    while (($line = fgets($handle)) !== false) {
                        $line = trim($line);

                        if ($line === '' || strpos($line, '--') === 0 || strpos($line, '/*') === 0) continue;

                        $query .= $line . ' ';
                        if (substr($line, -1) === ';') {
                            if (!$mysqli->query($query)) {
                                $success = false;
                                $message = "Gagal memulihkan pangkalan data: " . $mysqli->error;
                                $messageType = 'error';
                                break;
                            }
                            $query = '';
                        }
                    }

                    fclose($handle);

                    // Re-enable FK checks
                    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

                    if ($success) {
                        logSystemAction($mysqli, 'info', 'Database restored from: ' . $uploadedFile['name']);
                        $message = "Pangkalan data berjaya dipulihkan dari: " . $uploadedFile['name'];
                        $messageType = 'success';
                    }
                }
            }

        } else {
            $message = "Jenis fail tidak sah. Sila muat naik fail .sql sahaja.";
            $messageType = 'error';
        }

    } else {
        $message = "Ralat muat naik fail: " . getUploadErrorMessage($uploadedFile['error']);
        $messageType = 'error';
    }
}

// ---------------------- LIST EXISTING BACKUPS ----------------------
$backups = [];
$files = scandir($backupDir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        $backups[] = [
            'name' => $file,
            'size' => formatFileSize(filesize($backupDir . $file)),
            'date' => date('d/m/Y H:i:s', filemtime($backupDir . $file))
        ];
    }
}
usort($backups, function ($a, $b) {
    return strtotime(str_replace('/', '-', $b['date'])) - strtotime(str_replace('/', '-', $a['date']));
});

// ---------------------- HELPER FUNCTIONS ----------------------
function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

function getUploadErrorMessage($errorCode)
{
    $messages = [
        UPLOAD_ERR_INI_SIZE => "Fail terlalu besar (php.ini limit).",
        UPLOAD_ERR_FORM_SIZE => "Fail terlalu besar (form limit).",
        UPLOAD_ERR_PARTIAL => "Fail hanya dimuat naik sebahagiannya.",
        UPLOAD_ERR_NO_FILE => "Tiada fail dimuat naik.",
        UPLOAD_ERR_NO_TMP_DIR => "Folder sementara hilang.",
        UPLOAD_ERR_CANT_WRITE => "Gagal menulis fail ke disk.",
        UPLOAD_ERR_EXTENSION => "Extension PHP menghentikan muat naik."
    ];
    return $messages[$errorCode] ?? "Ralat muat naik tidak diketahui.";
}

function logSystemAction($conn, $level, $message)
{
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare("INSERT INTO system_logs (level, user_id, message, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $level, $userId, $message, $ipAddress);
    $stmt->execute();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .backup-section,
        .restore-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .section-title {
            margin-top: 0;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .backup-btn,
        .restore-btn {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .backup-btn:hover,
        .restore-btn:hover {
            background-color: #2980b9;
        }

        .backup-list {
            margin-top: 20px;
        }

        .file-input-container {
            margin-bottom: 20px;
        }

        .file-input {
            margin-bottom: 10px;
        }

        .backup-warning {
            color: #e74c3c;
            margin-bottom: 15px;
        }

        .download-backup {
            color: #3498db;
            text-decoration: none;
            margin-right: 10px;
        }

        .delete-backup {
            color: #e74c3c;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include 'dashboard.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Backup Pangkalan Data</h1>
            </div>

            <div class="backup-section">
                <h3 class="section-title">Buat Backup</h3>
                <p>Buat backup pangkalan data untuk memastikan data anda selamat.</p>

                <form method="POST" action="">
                    <button type="submit" name="create_backup" class="backup-btn">
                        <i class="fas fa-download"></i> Buat Backup Sekarang
                    </button>
                </form>

                <!-- List of existing backups -->
                <div class="backup-list">
                    <h4>Backup Sedia Ada</h4>

                    <?php if (count($backups) > 0): ?>
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Nama Fail</th>
                                    <th>Saiz</th>
                                    <th>Tarikh</th>
                                    <th>Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($backup['name']) ?></td>
                                        <td><?= $backup['size'] ?></td>
                                        <td><?= $backup['date'] ?></td>
                                        <td>
                                            <a href="download_backup.php?file=<?= urlencode($backup['name']) ?>"
                                                class="download-backup" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="delete-backup"
                                                data-file="<?= htmlspecialchars($backup['name']) ?>"
                                                data-token="<?= $_SESSION['csrf_token'] ?>" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Tiada backup sedia ada.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- <div class="restore-section">
                <h3 class="section-title">Pulihkan Pangkalan Data</h3>
                <p class="backup-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Amaran:</strong> Memulihkan pangkalan data akan menggantikan semua data semasa.
                    Sila buat backup terlebih dahulu.
                </p>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="file-input-container">
                        <label for="backup_file">Pilih fail backup (.sql):</label>
                        <input type="file" name="backup_file" id="backup_file" class="file-input" accept=".sql"
                            required>
                    </div>

                    <button type="submit" name="restore_backup" class="restore-btn"
                        onclick="return confirm('Adakah anda pasti untuk memulihkan pangkalan data? Semua data semasa akan digantikan.')">
                        <i class="fas fa-upload"></i> Pulihkan Pangkalan Data
                    </button>
                </form>
            </div> -->
        </div>
    </div>

    <script>
        document.querySelectorAll('.delete-backup').forEach(btn => {

            btn.addEventListener('click', function () {

                const filename = this.dataset.file;
                const token = this.dataset.token;

                Swal.fire({
                    title: 'Adakah anda pasti?',
                    text: 'Backup ini akan dipadam secara kekal!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, padam',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33'
                }).then((result) => {

                    if (result.isConfirmed) {

                        fetch('delete_backup.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'file=' + encodeURIComponent(filename) +
                                '&csrf_token=' + encodeURIComponent(token)
                        })
                            .then(res => res.json())
                            .then(data => {

                                if (data.success) {

                                    Swal.fire(
                                        'Berjaya!',
                                        'Backup telah dipadam.',
                                        'success'
                                    ).then(() => location.reload());

                                } else {

                                    Swal.fire(
                                        'Ralat!',
                                        data.message,
                                        'error'
                                    );

                                }

                            })
                            .catch(err => {

                                console.error(err);

                                Swal.fire(
                                    'Ralat!',
                                    'Masalah sambungan ke server.',
                                    'error'
                                );

                            });

                    }

                });

            });

        });
    </script>

</body>

</html>