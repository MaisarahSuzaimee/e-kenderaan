<?php
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nokp = trim($_POST['nokp']);

    if (!empty($nokp)) {

        $stmt = $conn->prepare("SELECT email FROM penggunajkn WHERE nokp = ?");
        $stmt->bind_param("s", $nokp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            $newPassword = bin2hex(random_bytes(4));
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE penggunajkn SET password = ?, must_change_password = 1 WHERE nokp = ?");
            $update->bind_param("ss", $hashedPassword, $nokp);
            $update->execute();

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'msrhszm@gmail.com';
                $mail->Password = 'miar lgbv cftw jhzt';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('msrhszm@gmail.com', 'Sistem Tempahan Kenderaan');
                $mail->addAddress($user['email']);

                $mail->isHTML(true);
                $mail->Subject = 'Kata Laluan Sementara';
                $mail->Body = "
                <!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Tempahan Kenderaan Baru</title>

<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #eef2f7;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    .wrapper {
        width: 100%;
        padding: 20px 10px;
    }

    .container {
        max-width: 650px;
        margin: auto;
        background: #ffffff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .header {
        background: linear-gradient(135deg, #2C3E50, #34495E);
        color: #fff;
        padding: 20px;
        text-align: center;
    }

    .header h2 {
        margin: 0;
        font-size: 20px;
        letter-spacing: 0.5px;
    }

    .subtext {
        padding: 15px 20px;
        font-size: 14px;
        color: #555;
        background: #f8f9fb;
        border-bottom: 1px solid #eee;
    }

    .content {
        padding: 10px 20px 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .label {
        font-weight: 600;
        color: #2C3E50;
        width: 45%;
    }

    .value {
        width: 55%;
        text-align: right;
        color: #555;
    }

    .highlight {
        background: #f4f8ff;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .footer {
        text-align: center;
        font-size: 12px;
        color: #888;
        padding: 15px;
        background: #fafafa;
    }

    @media (max-width: 600px) {
        .info-row {
            flex-direction: column;
            text-align: left;
        }

        .value {
            text-align: left;
            margin-top: 3px;
        }
    }
</style>
</head>

<body>

<div class='wrapper'>
<div class='container'>

    <div class='header'>
        <h2>Kata Laluan Sementara</h2>
    </div>

    <div class='subtext'>
        Salam Sejahtera, <br><br>
        Sila log masuk semula menggunakan kata laluan ini.
    </div>

   <div class='content' style='padding:20px;'>

<table width='100%' cellpadding='8' cellspacing='0' style='border-collapse: collapse; font-size:14px;'>

    <tr>
        <td style='font-weight:bold; width:40%;  border:1px solid #eee; background:#f9f9f9;'>Kata Laluan Sementara</td>
        <td style='border:1px solid #eeee;'>$newPassword</td>
    </tr>

</table>

</div>

    <div class='footer'>
        Ini adalah email automatik dari <b>Sistem Tempahan Kenderaan JKN Kedah</b>.<br>
        Sila jangan balas email ini.
    </div>

</div>
</div>

</body>
</html>

";

                $mail->send();

                $message = "Kata laluan dihantar ke email.";

            } catch (Exception $e) {
                $error_message = "Email failed: " . $mail->ErrorInfo;
            }

        } else {
            $error_message = "No KP not found!";
        }
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
        <h2>Lupa Kata Laluan</h2>
        <p style="color:gray; font-size:12px; margin-bottom:20px; margin-top:-15px;">Kata laluan sementara akan dihantar ke emel anda.</p>
        
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
                <input type="text" name="nokp" placeholder="No. Kad Pengenalan" required>
            </div>
            
            

            <button type="submit" class="btn-login">Hantar Email</button>
        </form>
<div style="text-align:center; margin-top:10px;">
    <a href="login.php">Log Masuk</a>
</div>
        
    </div>
</body>
</html>

