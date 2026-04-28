<?php
// Start session
session_start();
require 'config.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_nokp = trim($_POST['username'] ?? '');
    $user_password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if (empty($user_nokp) || empty($user_password)) {
        $error_message = "No KP and Password are required!";
    } else {
        // Debug: Log the login attempt
        error_log("Login attempt for user: $user_nokp");

        // First try with MySQLi connection
        $stmt = $conn->prepare("SELECT id, nama, password, role, nokp, email, must_change_password, bahagian FROM penggunajkn WHERE nokp = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("s", $user_nokp);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Debug: Log if user was found
        if ($user) {
            error_log("User found in database: " . $user['nama']);
            error_log("Stored password: " . $user['password']);
            error_log("Entered password: " . $user_password);
        } else {
            error_log("No user found with nokp: $user_nokp");
        }

        // Direct comparison of passwords (plain text)
        if ($user && (password_verify($user_password, $user['password']) || $user_password === $user['password'])) {

            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = trim($user['role']);
            $_SESSION['bahagian'] = trim($user['bahagian']);
            $_SESSION['show_popup'] = true;
            // ✅ CHECK HERE
            if ($user['must_change_password'] == 1) {
                header("Location: reset_password.php");
                exit;
            }

            // 🔽 Only continue if no forced change
            if ($_SESSION['role'] === 'superadmin') {
                header("Location: superadmin.php");
            } elseif ($_SESSION['role'] === 'admin') {
                header("Location: dashboard_admin.php");
             } elseif ($_SESSION['role'] === 'penyelaras_bahagian') {
                header("Location: STK_Bahagian.php");
            } else {
                header("Location: user_page.php");
            }
            exit;
        } else {
            error_log("Password match failed");
            $error_message = "Invalid No KP or Password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Pengurusan & Tempahan Kenderaan</title>
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
        <h2>Log Masuk</h2>
        <?php if (isset($_GET['changed']) && $_GET['changed'] == 1): ?>
            <script>
                alert("Kata laluan berjaya disimpan. Sila log masuk menggunakan kata laluan baru");
            </script>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <i class="fas fa-id-card"></i>
                <input type="text" name="username" placeholder="No. Kad Pengenalan" required>
            </div>

            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Kata Laluan" required>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat Saya</label>
            </div>

            <button type="submit" class="btn-login">Log Masuk</button>
        </form>
        <div style="text-align:center; margin-top:10px;">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>

    </div>
</body>

</html>