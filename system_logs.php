<?php
// Start session and check if user is logged in as superadmin
session_start();
require 'config.php';

// Check if user is logged in and is a superadmin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

// Initialize message variables
$message = '';
$messageType = '';

// Handle clear logs request
if (isset($_POST['clear_logs']) && isset($_POST['confirm_clear']) && $_POST['confirm_clear'] === 'yes') {
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Delete all logs
        $clearQuery = "DELETE FROM system_logs";
        $conn->query($clearQuery);
        
        // Reset auto-increment
        $resetQuery = "ALTER TABLE system_logs AUTO_INCREMENT = 1";
        $conn->query($resetQuery);
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        $message = "Semua log sistem telah berjaya dikosongkan.";
        $messageType = "success";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        
        // Set error message
        $message = "Ralat: " . $e->getMessage();
        $messageType = "error";
    }
}

// Pagination settings
$items_per_page = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total records for pagination
$sql_count = "SELECT COUNT(*) AS total FROM system_logs";
$result_count = $conn->query($sql_count);
$total_rows = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);

// Get logs with pagination
$sql = "SELECT l.*, u.nama as username 
        FROM system_logs l 
        LEFT JOIN penggunajkn u ON l.user_id = u.id 
        ORDER BY l.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$logs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Sistem | JKN Kedah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    .logs-section {
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

    .log-actions {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .export-btn,
    .clear-btn {
        padding: 8px 15px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .export-btn {
        background-color: #27ae60;
        color: white;
    }

    .export-btn:hover {
        background-color: #219653;
    }

    .clear-btn {
        background-color: #e74c3c;
        color: white;
    }

    .clear-btn:hover {
        background-color: #c0392b;
    }

    .log-level {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .level-info {
        background-color: #3498db;
        color: white;
    }

    .level-warning {
        background-color: #f39c12;
        color: white;
    }

    .level-error {
        background-color: #e74c3c;
        color: white;
    }

    .no-data {
        text-align: center;
        padding: 20px;
        color: #7f8c8d;
    }
    </style>
</head>

<body>
    <?php include 'dashboard.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text">Log Sistem</h1>
            </div>

             <div class="logs-section">
                <div class="log-actions">
                    <a href="export_logs.php" class="export-btn">
                        <i class="fas fa-file-export"></i> Export ke CSV
                    </a>

                    <button onclick="confirmClearLogs()" class="clear-btn">
                        <i class="fas fa-trash"></i> Kosongkan Log
                    </button>
                </div>

                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Level</th>
                            <th>Pengguna</th>
                            <th>Mesej</th>
                            <th>IP Address</th>
                            <th>Tarikh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs->num_rows > 0): ?>
                        <?php while ($log = $logs->fetch_assoc()): 
                                $levelClass = match($log['level']) {
                                    'info' => 'level-info',
                                    'warning' => 'level-warning',
                                    'error' => 'level-error',
                                    default => ''
                                };
                            ?>
                        <tr>
                            <td><?= $log['id'] ?></td>
                            <td>
                                <span class="log-level <?= $levelClass ?>">
                                    <?= ucfirst($log['level']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['username'] ?? 'System') ?></td>
                            <td><?= htmlspecialchars($log['message']) ?></td>
                            <td><?= htmlspecialchars($log['ip_address']) ?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">Tiada log sistem</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Clear Logs Confirmation Form (hidden) -->
    <form id="clearLogsForm" method="POST" style="display: none;">
        <input type="hidden" name="clear_logs" value="1">
        <input type="hidden" name="confirm_clear" value="yes">
    </form>

    <script>
    function confirmClearLogs() {
        Swal.fire({
            title: 'Kosongkan Log Sistem?',
            text: "Semua log sistem akan dipadam. Tindakan ini tidak boleh dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('clearLogsForm').submit();
            }
        });
    }
    </script>

    <?php
    // Helper function to get username
    function getUserName($conn, $userId) {
        $stmt = $conn->prepare("SELECT nama FROM penggunajkn WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['nama'];
        }
        
        return "User #" . $userId;
    }
    ?>
</body>

</html>