<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "Passwords do not match!";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
    UPDATE penggunajkn 
    SET password = ?, must_change_password = 0 
    WHERE id = ?
");

        $stmt->bind_param("si", $hashed, $_SESSION['user_id']);
        $stmt->execute();

        header("Location: login.php?changed=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengurusan & Tempahan Kenderaan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #268dc9;
            --secondary-color: #1a6592;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../IMG/jkn2.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header {
            width: 100%;
            padding: 20px;
            background: rgba(255, 255, 255, 0.95);
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 400px;
        }

        .login-container h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .error-message {
            background: #fde8e8;
            color: var(--error-color);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .success-message {
            background: #ebfde8;
            color: var(--success-color);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e1e1e1;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            user-select: none;
        }

        .remember-me input {
            margin-right: 10px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-login:hover {
            background: var(--secondary-color);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: var(--secondary-color);
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="IMG/logo jkn.png" alt="JKN Logo" class="logo">
        <h1>SISTEM PENGURUSAN & TEMPAHAN KENDERAAN</h1>
        <p>Jabatan Kesihatan Negeri Kedah</p>
    </div>

    <div class="login-container">
        <h2>Tukar Kata Laluan</h2>
        <!-- <p style="color:gray; font-size:12px; margin-bottom:20px; margin-top:-15px;"></p> -->

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <i class="fas fa-id-card"></i>
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="form-group">
                <i class="fas fa-id-card"></i>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>



            <button type="submit" class="btn-login">Simpan</button>
        </form>
        <div style="text-align:center; margin-top:10px;">
            <a href="login.php">Log Masuk</a>
        </div>

    </div>
</body>

</html>