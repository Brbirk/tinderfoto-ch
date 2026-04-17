<?php
require_once 'config.php';
require_login();

$count = 0;
$details = [];

foreach (glob(BLOG_DIR . '*.php') as $f) {
    $c = file_get_contents($f);
    $o = $c;
    $name = basename($f);

    // Find article content
    $artStart = strpos($c, '<article');
    $artEnd = strpos($c, '</article>');
    if ($artStart === false || $artEnd === false) continue;

    $article = substr($c, $artStart, $artEnd - $artStart);

    // Count opening and closing divs
    preg_match_all('/<div/i', $article, $m1);
    preg_match_all('/<\/div>/i', $article, $m2);
    $opens = count($m1[0]);
    $closes = count($m2[0]);
    $extra = $closes - $opens;

    if ($extra > 0) {
        // Remove extra </div> tags from end of article
        for ($i = 0; $i < $extra; $i++) {
            $lastDiv = strrpos($article, '</div>');
            if ($lastDiv !== false) {
                $article = substr($article, 0, $lastDiv) . substr($article, $lastDiv + 6);
            }
        }
        $c = substr($c, 0, $artStart) . $article . substr($c, $artEnd);
        file_put_contents($f, $c);
        $count++;
        $details[] = "$name: removed $extra extra div(s)";
    }
}

echo "<h2>$count Dateien korrigiert</h2>";
if ($details) {
    echo "<ul>";
    foreach ($details as $d) echo "<li>$d</li>";
    echo "</ul>";
}
echo "<p><a href='dashboard.php'>Zurueck</a></p>";
?>
