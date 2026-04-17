<?php
/**
 * Tinderfoto Admin – Konfiguration
 */

// Login-Daten
define('ADMIN_USER', 'admin');

// Passwort-Hash aus separater Datei lesen (sicher und einfach überschreibbar)
$pass_file = __DIR__ . '/pass.hash';
if (file_exists($pass_file)) {
    define('ADMIN_PASS_HASH', trim(file_get_contents($pass_file)));
} else {
    // Erster Aufruf: Standard-Passwort "tinderfoto2025" hashen und speichern
    $default_hash = password_hash('tinderfoto2025', PASSWORD_BCRYPT);
    file_put_contents($pass_file, $default_hash);
    define('ADMIN_PASS_HASH', $default_hash);
}

// Pfade
define('SITE_ROOT', dirname(__DIR__) . '/');
define('IMAGES_DIR', SITE_ROOT . 'images/');
define('BLOG_DIR', SITE_ROOT . 'blog/');
define('ADMIN_DIR', __DIR__ . '/');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hilfsfunktionen
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function get_pages() {
    $pages = [];
    $files = glob(SITE_ROOT . '*.html');
    foreach ($files as $f) {
        $pages[] = [
            'file' => basename($f),
            'path' => $f,
            'title' => get_page_title($f),
            'modified' => date('d.m.Y H:i', filemtime($f))
        ];
    }
    return $pages;
}

function get_blog_posts() {
    $posts = [];
    $files = glob(BLOG_DIR . '*.html');
    foreach ($files as $f) {
        $posts[] = [
            'file' => basename($f),
            'path' => $f,
            'title' => get_page_title($f),
            'modified' => date('d.m.Y H:i', filemtime($f))
        ];
    }
    usort($posts, function($a, $b) { return filemtime($b['path']) - filemtime($a['path']); });
    return $posts;
}

function get_page_title($filepath) {
    $content = file_get_contents($filepath);
    if (preg_match('/<title>(.*?)<\/title>/i', $content, $m)) {
        return html_entity_decode(strip_tags($m[1]));
    }
    return basename($filepath);
}

function get_images() {
    $images = [];
    $files = glob(IMAGES_DIR . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    foreach ($files as $f) {
        $images[] = [
            'file' => basename($f),
            'path' => $f,
            'size' => round(filesize($f) / 1024) . ' KB',
            'url' => '../images/' . basename($f)
        ];
    }
    usort($images, function($a, $b) { return filemtime($b['path']) - filemtime($a['path']); });
    return $images;
}
?>
