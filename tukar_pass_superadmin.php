<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukar Kata Laluan Superadmin | Sistem Tempahan Kenderaan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="CSS/STK.css">
    <link rel="stylesheet" href="CSS/layout.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: #2C3E50;
            --secondary-color: #3498DB;
            --success-color: #2ECC71;
            --danger-color: #E74C3C;
            --warning-color: #F1C40F;
            --gray-100: #F7FAFC;
            --gray-200: #EDF2F7;
            --gray-300: #E2E8F0;
            --gray-600: #718096;
            --gray-800: #2D3748;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            padding: 20px 0;
            margin-bottom: 2rem;
            width: 100%;
        }

        .logo-container {
            margin-right: 20px;
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .header-text {
            text-align: center;
        }

        .header-text h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .header-text p {
            margin: 5px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .nav-container {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .password-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
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
    <?php include 'dashboard.php' ?>
    <div class="dashboard">
        <div class="main-content">
             <div class="password-form">
            <h2>Tukar Kata Laluan Superadmin</h2>
            
            <?php if ($message): ?>
                <div class="message <?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="current_password">Kata Laluan Semasa</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           class="form-control" 
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">Kata Laluan Baru</label>
                    <input type="password" 
                           id="new_password" 
                           name="new_password" 
                           class="form-control" 
                           required>
                    <div class="password-requirements">
                        Kata laluan mestilah sekurang-kurangnya 6 aksara
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Sahkan Kata Laluan Baru</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="form-control" 
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
        // Client-side password validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Kata laluan baru mestilah sekurang-kurangnya 6 aksara.');
                return;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Kata laluan baru dan pengesahan tidak sepadan.');
                return;
            }
        });
    </script>
</body>

