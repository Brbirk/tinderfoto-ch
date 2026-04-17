<?php
require_once 'config.php';

// Login verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Benutzername oder Passwort falsch.';
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Falls bereits eingeloggt, weiter zum Dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinderfoto Admin – Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Roboto, Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-box { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 20px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-box h1 { font-size: 24px; color: #b71c1c; margin-bottom: 8px; }
        .login-box p { color: #666; font-size: 14px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; color: #333; margin-bottom: 6px; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group input:focus { border-color: #b71c1c; outline: none; }
        .btn { width: 100%; padding: 12px; background: #b71c1c; color: #fff; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #8e0000; }
        .error { background: #ffebee; color: #b71c1c; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo img { height: 40px; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">
            <img src="../images/logo-tinderfoto.png" alt="Tinderfoto">
        </div>
        <h1>Admin-Bereich</h1>
        <p>Melde dich an, um die Website zu verwalten.</p>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Benutzername</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Passwort</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn">Anmelden</button>
        </form>
    </div>
</body>
</html>
