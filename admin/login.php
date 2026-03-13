<?php
session_start();

// Eğer zaten giriş yapılmışsa ana sayfaya yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

require dirname(__DIR__) . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Geçersiz kullanıcı adı veya şifre.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Girişi - AKSA Toptan</title>
    <style>
        :root {
            --primary-bg: #1e1e2d;
            --sidebar-bg: #151521;
            --text-main: #b5b5c3;
            --text-light: #ffffff;
            --accent: #3699ff;
            --accent-hover: #1b84ff;
            --danger: #f64e60;
            --border-color: #2b2b40;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--sidebar-bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
            background-color: var(--primary-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .login-container h2 {
            color: var(--text-light);
            margin-bottom: 30px;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background-color: #1b1b29;
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 6px;
            outline: none;
            transition: border 0.3s;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: var(--accent);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: var(--accent-hover);
        }

        .error-message {
            color: var(--danger);
            background: rgba(246, 78, 96, 0.1);
            border: 1px solid rgba(246, 78, 96, 0.2);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: <?php echo empty($error) ? 'none' : 'block'; ?>;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Yönetici Girişi</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Giriş Yap</button>
        </form>
    </div>

</body>
</html>
