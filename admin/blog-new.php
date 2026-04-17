<?php
/**
 * Blog Management Page for Tinderfoto Admin Panel
 * Handles creating new blog posts and editing existing ones
 */

require_once 'config.php';
require_login();

$basePath = dirname(__DIR__) . '/';
$blogDir = $basePath . 'blog/';
$editMode = false;
$editFile = '';
$formData = [
    'title' => '',
    'slug' => '',
    'category' => 'Allgemein',
    'image' => '',
    'teaser' => '',
    'content' => ''
];

// German month names for date formatting
$germanMonths = [
    1 => 'Jan.', 2 => 'Feb.', 3 => 'März', 4 => 'Apr.', 5 => 'Mai', 6 => 'Juni',
    7 => 'Juli', 8 => 'Aug.', 9 => 'Sep.', 10 => 'Okt.', 11 => 'Nov.', 12 => 'Dez.'
];

function formatGermanDate($timestamp) {
    global $germanMonths;
    $month = $germanMonths[(int)date('n', $timestamp)];
    $day = (int)date('j', $timestamp);
    $year = date('Y', $timestamp);
    return "$month $day, $year";
}

function generateSlug($title) {
    $slug = mb_strtolower($title);
    $slug = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

function extractTitle($htmlContent) {
    if (preg_match('/<h1[^>]*>([^<]+)<\/h1>/', $htmlContent, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

function extractDate($htmlContent) {
    if (preg_match('/<span class="date">von Tinder Foto \| ([^<]+)<\/span>/', $htmlContent, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

function extractCategory($htmlContent) {
    if (preg_match('/<span class="category">([^<]+)<\/span>/', $htmlContent, $matches)) {
        return trim($matches[1]);
    }
    return 'Allgemein';
}

function extractContent($htmlContent) {
    if (preg_match('/<div class="post-body">\s*(.*?)\s*<\/div>/s', $htmlContent, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

function extractTeaserFromOverview($slug, $filePath) {
    if (!file_exists($filePath)) {
        return '';
    }
    $content = file_get_contents($filePath);
    $pattern = '/<a href="blog\/' . preg_quote($slug) . '\.html"[^>]*>.*?<p class="post-teaser">([^<]+)<\/p>/s';
    if (preg_match($pattern, $content, $matches)) {
        return trim($matches[1]);
    }
    return '';
}

function getRecentBlogPosts($blogDir, $limit = 6) {
    $files = glob($blogDir . '*.html');
    if (!$files) { return []; }
    usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });
    $recent = [];
    foreach (array_slice($files, 0, $limit) as $file) {
        $filename = basename($file);
        $slug = str_replace('.html', '', $filename);
        $htmlContent = file_get_contents($file);
        $title = extractTitle($htmlContent);
        if ($title) {
            $recent[] = ['slug' => $slug, 'title' => $title, 'filename' => $filename];
        }
    }
    return $recent;
}

function buildBlogPostHtml($title, $slug, $category, $content, $teaser, $image, $existingDate = null) {
    global $blogDir;
    $date = $existingDate ?: formatGermanDate(time());
    $description = htmlspecialchars(!empty($teaser) ? $teaser : substr(strip_tags($content), 0, 160));
    $recentPosts = getRecentBlogPosts($blogDir);
    $sidebarLinks = '';
    foreach ($recentPosts as $post) {
        $postTitle = htmlspecialchars($post['title']);
        $sidebarLinks .= "                            <li><a href=\"{$post['filename']}\">{$postTitle}</a></li>\n";
    }

    $html = <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - Tinderfoto</title>
    <meta name="description" content="{$description}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.html"><img src="../images/logo-tinderfoto.png" alt="Tinderfoto Logo"></a>
                </div>
                <nav class="nav">
                    <a href="../index.html" class="nav-link">ANGEBOT</a>
                    <a href="../infos-faq.html" class="nav-link">INFOS / FAQ</a>
                    <a href="../ueber-uns.html" class="nav-link">ÜBER UNS</a>
                    <a href="../buchen.html" class="nav-link">BUCHEN / KONTAKT</a>
                    <a href="../blog.html" class="nav-link">BLOG</a>
                </nav>
                <div class="hamburger"><span></span><span></span><span></span></div>
            </div>
        </div>
    </header>

    <main class="blog-single">
        <div class="container">
            <div class="blog-wrapper">
                <article class="blog-main">
                    <div class="post-header">
                        <h1>{$title}</h1>
                        <div class="post-meta">
                            <span class="date">von Tinder Foto | {$date}</span> |
                            <span class="category">{$category}</span>
                        </div>
                    </div>
                    <div class="post-body">
                        {$content}
                    </div>
                </article>
                <aside class="blog-sidebar">
                    <div class="sidebar-widget">
                        <h3>Neueste Beiträge</h3>
                        <ul class="widget-list">
{$sidebarLinks}                        </ul>
                    </div>
                    <div class="sidebar-widget">
                        <h3>Kategorien</h3>
                        <ul class="widget-list categories">
                            <li><a href="../blog.html#allgemein">Allgemein</a></li>
                            <li><a href="../blog.html#dating-profil-tipps">Dating Profil Tipps</a></li>
                            <li><a href="../blog.html#dating-tipps">Dating Tipps</a></li>
                            <li><a href="../blog.html#die-grosse-liebe">Die Grosse Liebe</a></li>
                            <li><a href="../blog.html#erfolg-mit-online-dating">Erfolg mit Online Dating</a></li>
                            <li><a href="../blog.html#online-dating-fotografie">Online Dating Fotografie</a></li>
                            <li><a href="../blog.html#online-dating-schweiz">Online Dating in der Schweiz</a></li>
                            <li><a href="../blog.html#partner-platformen">Partner Platformen Schweiz</a></li>
                            <li><a href="../blog.html#portrait-fotografie">Portrait Fotografie Schweiz</a></li>
                            <li><a href="../blog.html#profil-fotografie">Profil Fotografie</a></li>
                            <li><a href="../blog.html#singleboersen">Singlebörsen Schweiz</a></li>
                            <li><a href="../blog.html#tinder-shooting-tipps">Tinder Foto Shooting Tipps</a></li>
                            <li><a href="../blog.html#tipps-erstes-date">Tipps für das erste Date</a></li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h4 class="footer-heading">screensolutions gmbh / Bruno Birkhofer</h4>
                    <p>screensolutions gmbh<br>Bruno Birkhofer<br>Fehlwiesstrasse 34<br>CH-8580 Amriswil / Hefenhofen<br>Schweiz</p>
                </div>
                <div class="footer-column">
                    <h4 class="footer-heading">Kontakt:</h4>
                    <p>Tel: 076 344 17 94<br>Mail: info@screensolutions.ch</p>
                </div>
            </div>
            <div class="footer-bottom"><p>Designed &amp; Powered by screensolutions gmbh / Affeltrangen - Schweiz</p></div>
        </div>
    </footer>

    <a href="https://wa.me/41763441794" class="whatsapp-button" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>
    <script src="../js/main.js"></script>
</body>
</html>
HTML;

    return $html;
}

function insertIntoOverview($filePath, $slug, $title, $image, $teaser, $date) {
    if (!file_exists($filePath)) { return false; }
    $content = file_get_contents($filePath);
    $article = <<<HTML
<article class="blog-post">
    <a href="blog/{$slug}.html" class="post-image">
        <img src="images/{$image}" alt="{$title}" loading="lazy">
    </a>
    <div class="post-content">
        <h2><a href="blog/{$slug}.html">{$title}</a></h2>
        <div class="post-meta">
            <span class="date">von Tinder Foto | {$date}</span>
        </div>
        <p class="post-teaser">{$teaser}</p>
    </div>
</article>

HTML;
    $pattern = '/<div class="blog-posts">/';
    if (preg_match($pattern, $content)) {
        $newContent = preg_replace($pattern, '<div class="blog-posts">' . "\n" . $article, $content, 1);
        return file_put_contents($filePath, $newContent) !== false;
    }
    return false;
}

function removeFromOverview($filePath, $slug) {
    if (!file_exists($filePath)) { return false; }
    $content = file_get_contents($filePath);
    $pattern = '/<article class="blog-post">\s*<a href="blog\/' . preg_quote($slug) . '\.html"[^>]*>.*?<\/article>\s*/s';
    $newContent = preg_replace($pattern, '', $content);
    return file_put_contents($filePath, $newContent) !== false;
}

function updateInOverview($filePath, $slug, $title, $image, $teaser, $date) {
    if (!file_exists($filePath)) { return false; }
    $content = file_get_contents($filePath);
    $pattern = '/<article class="blog-post">\s*<a href="blog\/' . preg_quote($slug) . '\.html"[^>]*>.*?<\/article>/s';
    $article = <<<HTML
<article class="blog-post">
    <a href="blog/{$slug}.html" class="post-image">
        <img src="images/{$image}" alt="{$title}" loading="lazy">
    </a>
    <div class="post-content">
        <h2><a href="blog/{$slug}.html">{$title}</a></h2>
        <div class="post-meta">
            <span class="date">von Tinder Foto | {$date}</span>
        </div>
        <p class="post-teaser">{$teaser}</p>
    </div>
</article>
HTML;
    if (preg_match($pattern, $content)) {
        $newContent = preg_replace($pattern, $article, $content, 1);
        return file_put_contents($filePath, $newContent) !== false;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = $_POST['category'] ?? 'Allgemein';
    $image = trim($_POST['image'] ?? '');
    $teaser = trim($_POST['teaser'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($slug) || empty($content)) {
        $error = 'Titel, Dateiname und Inhalt sind erforderlich.';
    } else {
        $date = formatGermanDate(time());

        if (isset($_POST['create'])) {
            $htmlFile = $blogDir . $slug . '.html';
            if (file_exists($htmlFile)) {
                $error = 'Ein Blog-Post mit diesem Dateinamen existiert bereits.';
            } else {
                $htmlContent = buildBlogPostHtml($title, $slug, $category, $content, $teaser, $image);
                if (file_put_contents($htmlFile, $htmlContent)) {
                    insertIntoOverview($basePath . 'blog.html', $slug, $title, $image, $teaser, $date);
                    insertIntoOverview($basePath . 'blog.php', $slug, $title, $image, $teaser, $date);
                    header('Location: blog-new.php?created=1');
                    exit;
                } else {
                    $error = 'Fehler beim Speichern der Blog-Post-Datei.';
                }
            }
        } elseif (isset($_POST['update'])) {
            $editFile = $_POST['editfile'] ?? '';
            $htmlFile = $blogDir . $editFile;
            if (!file_exists($htmlFile)) {
                $error = 'Blog-Post-Datei nicht gefunden.';
            } else {
                $existingHtml = file_get_contents($htmlFile);
                $existingDate = extractDate($existingHtml);
                if (!empty($existingDate)) { $date = $existingDate; }
                $htmlContent = buildBlogPostHtml($title, $slug, $category, $content, $teaser, $image, $existingDate);
                if (file_put_contents($htmlFile, $htmlContent)) {
                    updateInOverview($basePath . 'blog.html', $slug, $title, $image, $teaser, $date);
                    updateInOverview($basePath . 'blog.php', $slug, $title, $image, $teaser, $date);
                    header('Location: blog-new.php?updated=1');
                    exit;
                } else {
                    $error = 'Fehler beim Aktualisieren der Blog-Post-Datei.';
                }
            }
        }
    }
}

if (isset($_GET['edit'])) {
    $editFile = basename($_GET['edit']);
    $htmlFile = $blogDir . $editFile;
    if (file_exists($htmlFile)) {
        $editMode = true;
        $htmlContent = file_get_contents($htmlFile);
        $slug = str_replace('.html', '', $editFile);
        $formData['title'] = extractTitle($htmlContent);
        $formData['slug'] = $slug;
        $formData['category'] = extractCategory($htmlContent);
        $formData['content'] = extractContent($htmlContent);
        $formData['teaser'] = extractTeaserFromOverview($slug, $basePath . 'blog.html');
        if (preg_match('/<a href="blog\/' . preg_quote($slug) . '\.html"[^>]*>.*?<img src="images\/([^"]+)"/', file_get_contents($basePath . 'blog.html'), $matches)) {
            $formData['image'] = $matches[1];
        }
    } else {
        $error = 'Blog-Post nicht gefunden.';
    }
}

$recentPosts = getRecentBlogPosts($blogDir);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinderfoto Admin – <?php echo $editMode ? 'Blog-Post bearbeiten' : 'Neuer Blog-Post'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <nav class="admin-nav">
        <div class="nav-brand">
            <img src="../images/logo-tinderfoto.png" alt="Tinderfoto">
            <span>Admin</span>
        </div>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="edit.php"><i class="fas fa-edit"></i> Seiten</a>
            <a href="blog.php" class="active"><i class="fas fa-newspaper"></i> Blog</a>
            <a href="images.php"><i class="fas fa-images"></i> Bilder</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Einstellungen</a>
            <a href="index.php?logout=1" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="admin-main">
        <h1><?php echo $editMode ? '<i class="fas fa-edit"></i> Blog-Post bearbeiten' : '<i class="fas fa-plus"></i> Neuer Blog-Post'; ?></h1>

        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Blog-Post erfolgreich erstellt und zur Blog-Übersicht hinzugefügt!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Blog-Post erfolgreich aktualisiert!
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST">
                <?php if ($editMode): ?>
                    <input type="hidden" name="editfile" value="<?php echo htmlspecialchars($editFile); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="titleInput">Titel <span class="required">*</span></label>
                    <input type="text" id="titleInput" name="title" placeholder="z.B. 10 Tipps für bessere Tinder-Fotos" value="<?php echo htmlspecialchars($formData['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="slugInput">Dateiname/URL-Slug <span class="required">*</span></label>
                    <input type="text" id="slugInput" name="slug" placeholder="z.B. 10-tipps-bessere-tinder-fotos" value="<?php echo htmlspecialchars($formData['slug']); ?>" <?php echo $editMode ? 'readonly' : ''; ?> required>
                    <small>Wird automatisch aus dem Titel generiert.</small>
                </div>

                <div class="form-group">
                    <label for="categorySelect">Kategorie</label>
                    <select id="categorySelect" name="category">
                        <option value="Allgemein" <?php echo $formData['category'] === 'Allgemein' ? 'selected' : ''; ?>>Allgemein</option>
                        <option value="Dating Profil Tipps" <?php echo $formData['category'] === 'Dating Profil Tipps' ? 'selected' : ''; ?>>Dating Profil Tipps</option>
                        <option value="Dating Tipps" <?php echo $formData['category'] === 'Dating Tipps' ? 'selected' : ''; ?>>Dating Tipps</option>
                        <option value="Die Grosse Liebe" <?php echo $formData['category'] === 'Die Grosse Liebe' ? 'selected' : ''; ?>>Die Grosse Liebe</option>
                        <option value="Erfolg mit Online Dating" <?php echo $formData['category'] === 'Erfolg mit Online Dating' ? 'selected' : ''; ?>>Erfolg mit Online Dating</option>
                        <option value="Online Dating Fotografie" <?php echo $formData['category'] === 'Online Dating Fotografie' ? 'selected' : ''; ?>>Online Dating Fotografie</option>
                        <option value="Online Dating in der Schweiz" <?php echo $formData['category'] === 'Online Dating in der Schweiz' ? 'selected' : ''; ?>>Online Dating in der Schweiz</option>
                        <option value="Partner Platformen Schweiz" <?php echo $formData['category'] === 'Partner Platformen Schweiz' ? 'selected' : ''; ?>>Partner Platformen Schweiz</option>
                        <option value="Portrait Fotografie Schweiz" <?php echo $formData['category'] === 'Portrait Fotografie Schweiz' ? 'selected' : ''; ?>>Portrait Fotografie Schweiz</option>
                        <option value="Profil Fotografie" <?php echo $formData['category'] === 'Profil Fotografie' ? 'selected' : ''; ?>>Profil Fotografie</option>
                        <option value="Tinder Foto Shooting Tipps" <?php echo $formData['category'] === 'Tinder Foto Shooting Tipps' ? 'selected' : ''; ?>>Tinder Foto Shooting Tipps</option>
                        <option value="Tipps für das erste Date" <?php echo $formData['category'] === 'Tipps für das erste Date' ? 'selected' : ''; ?>>Tipps für das erste Date</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="imageInput">Featured Image</label>
                    <input type="text" id="imageInput" name="image" placeholder="z.B. mein-bild-1080x675.jpg" value="<?php echo htmlspecialchars($formData['image']); ?>">
                    <small>Dateiname aus images/ Ordner.</small>
                </div>

                <div class="form-group">
                    <label for="teaserInput">Anreisser-Text / Teaser</label>
                    <textarea id="teaserInput" name="teaser" rows="3" placeholder="Kurzer Vorschautext für die Blog-Übersicht (2-3 Sätze)"><?php echo htmlspecialchars($formData['teaser']); ?></textarea>
                    <small>Dieser Text erscheint in der Blog-Übersicht unter dem Titel.</small>
                </div>

                <div class="form-group">
                    <label for="contentInput">Inhalt/HTML <span class="required">*</span></label>
                    <textarea id="contentInput" name="content" rows="15" placeholder="<p>Dein Blogpost-Text hier...</p>" required><?php echo htmlspecialchars($formData['content']); ?></textarea>
                </div>

                <button type="submit" name="<?php echo $editMode ? 'update' : 'create'; ?>" class="btn-save">
                    <i class="fas fa-save"></i> <?php echo $editMode ? 'Änderungen speichern' : 'Blog-Post erstellen'; ?>
                </button>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('titleInput').addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/[äÄ]/g, 'ae').replace(/[öÖ]/g, 'oe').replace(/[üÜ]/g, 'ue').replace(/ß/g, 'ss')
                .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            <?php if (!$editMode): ?>
                document.getElementById('slugInput').value = slug;
            <?php endif; ?>
        });
    </script>
</body>
</html>