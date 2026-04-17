<?php
require_once 'config.php';
require_login();

$pages = get_pages();
$posts = get_blog_posts();
$images = get_images();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinderfoto Admin – Dashboard</title>
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
            <a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="edit.php"><i class="fas fa-edit"></i> Seiten</a>
            <a href="blog.php"><i class="fas fa-newspaper"></i> Blog</a>
            <a href="images.php"><i class="fas fa-images"></i> Bilder</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Einstellungen</a>
            <a href="../index.html" target="_blank"><i class="fas fa-external-link-alt"></i> Website</a>
            <a href="index.php?logout=1" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <h1>Dashboard</h1>
        <p class="welcome">Willkommen im Admin-Bereich von tinderfoto.ch</p>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                <div class="stat-number"><?= count($pages) ?></div>
                <div class="stat-label">Seiten</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-newspaper"></i>
                <div class="stat-number"><?= count($posts) ?></div>
                <div class="stat-label">Blog-Posts</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-images"></i>
                <div class="stat-number"><?= count($images) ?></div>
                <div class="stat-label">Bilder</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2><i class="fas fa-file-alt"></i> Seiten</h2>
                <table>
                    <thead><tr><th>Datei</th><th>Titel</th><th>Geändert</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($pages as $p): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($p['file']) ?></code></td>
                            <td><?= htmlspecialchars(mb_substr($p['title'], 0, 40)) ?></td>
                            <td><?= $p['modified'] ?></td>
                            <td><a href="edit.php?file=<?= urlencode($p['file']) ?>" class="btn-small">Bearbeiten</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h2><i class="fas fa-newspaper"></i> Letzte Blog-Posts</h2>
                <table>
                    <thead><tr><th>Titel</th><th>Geändert</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($posts, 0, 10) as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars(mb_substr($p['title'], 0, 50)) ?></td>
                            <td><?= $p['modified'] ?></td>
                            <td><a href="edit.php?file=blog/<?= urlencode($p['file']) ?>" class="btn-small">Bearbeiten</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="blog.php" class="btn-link">Alle Blog-Posts anzeigen &rarr;</a>
            </div>
        </div>
    </main>
</body>
</html>
