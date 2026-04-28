<?php
session_start();
require 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// if ($_SESSION['role'] !== 'staff') {
//     header("Location: error.html");
//     exit();
// }

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['current_password'] ?? '';
   $newPasswordRaw = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate input
if (empty($currentPassword) || empty($newPasswordRaw) || empty($confirmPassword)) {
    $message = "Sila isi semua medan yang diperlukan.";
    $messageType = "error";
} elseif ($newPasswordRaw !== $confirmPassword) {
    $message = "Kata laluan baru dan pengesahan tidak sepadan.";
    $messageType = "error";
} elseif (strlen($newPasswordRaw) < 6) {
    $message = "Kata laluan baru mestilah sekurang-kurangnya 6 aksara.";
    $messageType = "error";
} else {
    try {
        // Fetch user
        $stmt = $conn->prepare("SELECT * FROM penggunajkn WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            $message = "Pengguna tidak dijumpai.";
            $messageType = "error";
        } else {
            $passwordMatches = password_verify($currentPassword, $user['password']) 
                               || $currentPassword === $user['password'];

            if ($passwordMatches) {
                // Hash the new password now
                $hashedPassword = password_hash($newPasswordRaw, PASSWORD_DEFAULT);

                $updateStmt = $conn->prepare("UPDATE penggunajkn SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $userId);
                $updateStmt->execute();

                $message = "Kata laluan berjaya dikemaskini!";
                $messageType = "success";
            } else {
                $message = "Kata laluan semasa tidak betul.";
                $messageType = "error";
            }
        }
    } catch (Exception $e) {
        $message = "Ralat sistem: " . $e->getMessage();
        $messageType = "error";
        error_log("Password update error: " . $e->getMessage());
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="1200">
    <title>Tukar Kata Laluan | Sistem Tempahan Kenderaan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="CSS/STK2.css">
    <link rel="stylesheet" href="CSS/layout.css">
    <link rel="stylesheet" href="CSS/style.css">


    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .password-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto auto;
            /* margin-left: 25rem; */
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: #007736;
            color: white;
        }

        .message {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .message.success {
            background: #DEF7EC;
            color: #03543F;
        }

        .message.error {
            background: #FDE8E8;
            color: #9B1C1C;
        }

        .password-requirements {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
        }

        .btn-container {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
    </style>
</head>

<body>
    <?= include 'dashboard2.php' ?>
    <div class="dashboard">
        <div class="main-content">
            <div class="welcome-header" style="margin-top: -26px;">
                <h1 class="welcome-text"></h1>
                <div class="left-actions">
                    <a href="profile.php" class="btn btn-primary">
                        <i class="fas fa-user-circle"></i> Profile Pengguna
                    </a>
                    <a href="#" onclick="handleLogout()" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Log Keluar
                    </a>
                </div>
            </div>
            <div class="password-form">
                <h2>Tukar Kata Laluan</h2>
                <?php if ($message): ?>
                    <div class="message <?= $messageType ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <!-- Debug info - only show in development -->
                <?php if (isset($_GET['debug']) && $_GET['debug'] === '1' && $userInfo): ?>
                    <!-- <div style="background: #f8f9fa; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                        <p><strong>Debug Info:</strong></p>
                        <p>User ID: <?= $userInfo['id'] ?></p>
                        <p>Name: <?= htmlspecialchars($userInfo['nama']) ?></p>
                        <p>Email: <?= htmlspecialchars($userInfo['email']) ?></p>
                        <p>Role: <?= htmlspecialchars($userInfo['role']) ?></p>
                    </div> -->
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="current_password">Kata Laluan Semasa</label>
                        <input type="password" id="current_password" name="current_password" class="form-control"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">Kata Laluan Baru</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                        <div class="password-requirements">
                            Kata laluan mestilah sekurang-kurangnya 6 aksara
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Sahkan Kata Laluan Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                            required>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        document.querySelector('form').addEventListener('submit', function (e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword.length < 6) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat',
                    text: 'Kata laluan baru mestilah sekurang-kurangnya 6 aksara.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat',
                    text: 'Kata laluan baru dan pengesahan tidak sepadan.',
                    confirmButtonColor: '#d33'
                });
                return;
            }
        });

    </script>
    <script src="logout.js"></script>
</body>

</html>
