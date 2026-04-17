<?php
require_once 'config.php';
require_login();

$message = '';
$error = '';
$file = $_GET['file'] ?? '';
$filepath = '';
$content = '';

// Sicherheit: Erlaubte Dateitypen inkl. PHP
if ($file) {
    $file = str_replace('..', '', $file);
    $file = ltrim($file, '/');

    if (preg_match('/\.(html|css|js|php)$/', $file)) {
        $filepath = SITE_ROOT . $file;
        if (file_exists($filepath)) {
            $content = file_get_contents($filepath);
        } else {
            $error = 'Datei nicht gefunden: ' . htmlspecialchars($file);
        }
    } else {
        $error = 'Nur HTML-, CSS-, JS- und PHP-Dateien können bearbeitet werden.';
    }
}

// Speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $save_file = $_POST['filepath'] ?? '';
    $save_content = $_POST['content'] ?? '';

    if ($save_file && file_exists($save_file) && preg_match('/\.(html|css|js|php)$/', $save_file)) {
        // Backup erstellen
        $backup_dir = ADMIN_DIR . 'backups/';
        if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);
        $backup_name = basename($save_file) . '.' . date('Y-m-d_H-i-s') . '.bak';
        copy($save_file, $backup_dir . $backup_name);

        // Speichern
        file_put_contents($save_file, $save_content);
        $message = 'Datei gespeichert! Backup erstellt: ' . $backup_name;
        $content = $save_content;
        $filepath = $save_file;
    } else {
        $error = 'Fehler beim Speichern.';
    }
}

// Alle editierbaren Dateien auflisten
$pages = get_pages();
$css_files = glob(SITE_ROOT . 'css/*.css');
$js_files = glob(SITE_ROOT . 'js/*.js');
$php_files = glob(SITE_ROOT . '*.php');
$admin_php_files = glob(ADMIN_DIR . '*.php');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinderfoto Admin – Seiten bearbeiten</title>
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
            <a href="edit.php" class="active"><i class="fas fa-edit"></i> Seiten</a>
            <a href="blog.php"><i class="fas fa-newspaper"></i> Blog</a>
            <a href="images.php"><i class="fas fa-images"></i> Bilder</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Einstellungen</a>
            <a href="index.php?logout=1" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <h1><i class="fas fa-edit"></i> Seiten bearbeiten</h1>

        <?php if ($message): ?>
            <div class="alert success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!$file): ?>
            <!-- Datei-Auswahl -->
            <div class="card">
                <h2>Hauptseiten</h2>
                <div class="file-list">
                    <?php foreach ($pages as $p): ?>
                        <a href="edit.php?file=<?= urlencode($p['file']) ?>" class="file-item">
                            <i class="fas fa-file-code"></i>
                            <div>
                                <strong><?= htmlspecialchars($p['file']) ?></strong>
                                <small><?= htmlspecialchars($p['title']) ?></small>
                            </div>
                            <span class="modified"><?= $p['modified'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h2>CSS & JavaScript</h2>
                <div class="file-list">
                    <?php foreach ($css_files as $f): ?>
                        <a href="edit.php?file=css/<?= urlencode(basename($f)) ?>" class="file-item">
                            <i class="fas fa-paint-brush"></i>
                            <div><strong>css/<?= htmlspecialchars(basename($f)) ?></strong></div>
                            <span class="modified"><?= date('d.m.Y H:i', filemtime($f)) ?></span>
                        </a>
                    <?php endforeach; ?>
                    <?php foreach ($js_files as $f): ?>
                        <a href="edit.php?file=js/<?= urlencode(basename($f)) ?>" class="file-item">
                            <i class="fas fa-code"></i>
                            <div><strong>js/<?= htmlspecialchars(basename($f)) ?></strong></div>
                            <span class="modified"><?= date('d.m.Y H:i', filemtime($f)) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h2>PHP-Dateien</h2>
                <div class="file-list">
                    <?php foreach ($php_files as $f): ?>
                        <a href="edit.php?file=<?= urlencode(basename($f)) ?>" class="file-item">
                            <i class="fab fa-php"></i>
                            <div><strong><?= htmlspecialchars(basename($f)) ?></strong></div>
                            <span class="modified"><?= date('d.m.Y H:i', filemtime($f)) ?></span>
                        </a>
                    <?php endforeach; ?>
                    <?php foreach ($admin_php_files as $f): ?>
                        <a href="edit.php?file=admin/<?= urlencode(basename($f)) ?>" class="file-item">
                            <i class="fab fa-php"></i>
                            <div><strong>admin/<?= htmlspecialchars(basename($f)) ?></strong></div>
                            <span class="modified"><?= date('d.m.Y H:i', filemtime($f)) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Editor -->
            <div class="editor-header">
                <a href="edit.php" class="btn-back"><i class="fas fa-arrow-left"></i> Zurück</a>
                <span class="current-file"><i class="fas fa-file-code"></i> <?= htmlspecialchars($file) ?></span>
                <?php if (strpos($file, 'blog/') === false): ?>
                    <a href="../<?= htmlspecialchars($file) ?>" target="_blank" class="btn-preview"><i class="fas fa-eye"></i> Vorschau</a>
                <?php else: ?>
                    <a href="../<?= htmlspecialchars($file) ?>" target="_blank" class="btn-preview"><i class="fas fa-eye"></i> Vorschau</a>
                <?php endif; ?>
            </div>

            <form method="POST" class="editor-form">
                <input type="hidden" name="filepath" value="<?= htmlspecialchars($filepath) ?>">
                <textarea name="content" id="codeEditor" class="code-editor"><?= htmlspecialchars($content) ?></textarea>
                <div class="editor-actions">
                    <button type="submit" name="save" class="btn-save"><i class="fas fa-save"></i> Speichern</button>
                    <span class="save-hint">Tipp: Ctrl+S / Cmd+S zum Speichern</span>
                </div>
            </form>

            <script>
                // Ctrl+S / Cmd+S zum Speichern
                document.getElementById('codeEditor').addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        e.preventDefault();
                        this.form.submit();
                    }
                });

                // Tab-Taste im Editor erlauben
                document.getElementById('codeEditor').addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        e.preventDefault();
                        var start = this.selectionStart;
                        var end = this.selectionEnd;
                        this.value = this.value.substring(0, start) + '    ' + this.value.substring(end);
                        this.selectionStart = this.selectionEnd = start + 4;
                    }
                });
            </script>
        <?php endif; ?>
    </main>
</body>
</html>
