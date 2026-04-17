<?php
require_once 'config.php';
require_login();

$message = '';
$error = '';

// Passwort ändern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // Aktuelles Passwort prüfen
    if (!password_verify($current_pass, ADMIN_PASS_HASH)) {
        $error = 'Das aktuelle Passwort ist falsch.';
    } elseif (strlen($new_pass) < 8) {
        $error = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
    } elseif ($new_pass !== $confirm_pass) {
        $error = 'Die neuen Passwörter stimmen nicht überein.';
    } else {
        // Neuen Hash mit PHP generieren und in pass.hash speichern
        $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $pass_file = ADMIN_DIR . 'pass.hash';

        if (file_put_contents($pass_file, $new_hash) !== false) {
            $message = 'Passwort erfolgreich geändert! Du wirst in 3 Sekunden zum Login weitergeleitet...';
            session_destroy();
            header('Refresh: 3; URL=index.php');
        } else {
            $error = 'Fehler beim Speichern. Prüfe die Schreibrechte des admin/-Ordners.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinderfoto Admin – Einstellungen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-brand">
            <img src="../images/logo-tinderfoto.png" alt="Tinderfoto" height="30">
            <span>Admin</span>
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="edit.php"><i class="fas fa-edit"></i> Seiten</a>
            <a href="blog.php"><i class="fas fa-newspaper"></i> Blog</a>
            <a href="images.php"><i class="fas fa-images"></i> Bilder</a>
            <a href="settings.php" class="active"><i class="fas fa-cog"></i> Einstellungen</a>
            <a href="index.php?logout=1" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <h1><i class="fas fa-cog"></i> Einstellungen</h1>

        <?php if ($message): ?>
            <div class="alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card" style="max-width: 500px;">
            <h2>Passwort ändern</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Aktuelles Passwort</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>Neues Passwort (mind. 8 Zeichen)</label>
                    <input type="password" name="new_password" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Neues Passwort bestätigen</label>
                    <input type="password" name="confirm_password" required minlength="8">
                </div>
                <button type="submit" name="change_password" class="btn-save"><i class="fas fa-key"></i> Passwort ändern</button>
            </form>
        </div>

        <div class="card" style="max-width: 500px;">
            <h2>Info</h2>
            <table class="info-table">
                <tr><td>PHP Version</td><td><?= phpversion() ?></td></tr>
                <tr><td>Server</td><td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unbekannt') ?></td></tr>
                <tr><td>Website-Pfad</td><td><code><?= SITE_ROOT ?></code></td></tr>
                <tr><td>Speicherplatz Bilder</td><td><?php
                    $size = 0;
                    foreach (glob(IMAGES_DIR . '*') as $f) $size += filesize($f);
                    echo round($size / 1024 / 1024, 1) . ' MB';
                ?></td></tr>
            </table>
        </div>
    </main>
</body>
</html>
