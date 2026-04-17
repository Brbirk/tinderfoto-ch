<?php
require_once 'config.php';
require_login();

$message = '';
$error = '';

// Bild hochladen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        if (in_array($file['type'], $allowed)) {
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '-', basename($file['name']));
            $dest = IMAGES_DIR . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $message = 'Bild hochgeladen: ' . $filename;
            } else {
                $error = 'Fehler beim Hochladen.';
            }
        } else {
            $error = 'Nur JPG, PNG, GIF und WebP erlaubt.';
        }
    } else {
        $error = 'Upload-Fehler: ' . $file['error'];
    }
}

// Bild löschen
if (isset($_GET['delete']) && isset($_GET['confirm'])) {
    $del_file = basename($_GET['delete']);
    $del_path = IMAGES_DIR . $del_file;
    if (file_exists($del_path) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $del_file)) {
        unlink($del_path);
        $message = 'Bild gelöscht: ' . $del_file;
    }
}

$images = get_images();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinderfoto Admin – Bilder</title>
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
            <a href="images.php" class="active"><i class="fas fa-images"></i> Bilder</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Einstellungen</a>
            <a href="index.php?logout=1" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <h1><i class="fas fa-images"></i> Bilder (<?= count($images) ?>)</h1>

        <?php if ($message): ?>
            <div class="alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Bild hochladen</h2>
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="file" name="image" accept="image/*" required>
                <button type="submit" class="btn-save"><i class="fas fa-upload"></i> Hochladen</button>
            </form>
        </div>

        <div class="card">
            <h2>Alle Bilder</h2>
            <div class="image-grid">
                <?php foreach ($images as $img): ?>
                    <div class="image-card">
                        <img src="<?= htmlspecialchars($img['url']) ?>" alt="<?= htmlspecialchars($img['file']) ?>" loading="lazy">
                        <div class="image-info">
                            <code class="image-name" title="Klick zum Kopieren" onclick="navigator.clipboard.writeText('images/<?= htmlspecialchars($img['file']) ?>'); this.textContent='Kopiert!'; setTimeout(() => this.textContent='<?= htmlspecialchars($img['file']) ?>', 1500);"><?= htmlspecialchars($img['file']) ?></code>
                            <span class="image-size"><?= $img['size'] ?></span>
                            <a href="images.php?delete=<?= urlencode($img['file']) ?>&confirm=1" class="btn-delete-small" onclick="return confirm('Bild löschen?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>
