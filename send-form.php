<?php
/**
 * Tinderfoto.ch – Formular-Handler
 * Sendet die Buchungsanfrage per E-Mail an info@tinderfoto.ch
 */

// Konfiguration
$empfaenger = 'info@tinderfoto.ch';
$betreff_prefix = '[Tinderfoto] Neue Anfrage: ';
$success_redirect = 'danke.html';
$error_redirect = 'buchen.html?error=1';

// Nur POST-Requests akzeptieren
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buchen.html');
    exit;
}

// Honeypot-Check (Spam-Schutz)
if (!empty($_POST['website'])) {
    // Bot hat das versteckte Feld ausgefüllt
    header('Location: ' . $success_redirect);
    exit;
}

// Captcha prüfen
$captcha_answer = isset($_POST['captcha']) ? intval($_POST['captcha']) : 0;
$captcha_expected = isset($_POST['captcha_expected']) ? intval($_POST['captcha_expected']) : 0;
if ($captcha_answer !== $captcha_expected || $captcha_expected === 0) {
    header('Location: buchen.html?error=captcha');
    exit;
}

// Formular-Daten auslesen und bereinigen
$name = htmlspecialchars(strip_tags(trim($_POST['name'] ?? '')));
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$angebot = htmlspecialchars(strip_tags(trim($_POST['offer'] ?? '')));
$region = htmlspecialchars(strip_tags(trim($_POST['region'] ?? '')));
$telefon = htmlspecialchars(strip_tags(trim($_POST['phone'] ?? '')));
$nachricht = htmlspecialchars(strip_tags(trim($_POST['message'] ?? '')));

// Pflichtfelder prüfen
if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: buchen.html?error=fields');
    exit;
}

// Angebot-Label
$angebot_label = 'Nicht angegeben';
if ($angebot === 'basic') {
    $angebot_label = 'Partnersuche «BASIC» (CHF 370.-)';
} elseif ($angebot === 'pro') {
    $angebot_label = 'Partnersuche «PRO» (CHF 490.-)';
}

// Region-Label
$region_label = 'Nicht angegeben';
if ($region === 'east') {
    $region_label = 'Zürich oder östlich von Zürich';
} elseif ($region === 'west') {
    $region_label = 'Westlich von Zürich';
}

// E-Mail-Betreff
$betreff = $betreff_prefix . $name;

// E-Mail-Inhalt (HTML)
$mail_body = "
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        table { border-collapse: collapse; width: 100%; max-width: 600px; }
        td { padding: 10px 15px; border-bottom: 1px solid #eee; vertical-align: top; }
        td:first-child { font-weight: bold; width: 200px; color: #666; }
        h2 { color: #b71c1c; font-size: 20px; }
        .message { background-color: #f9f9f9; padding: 15px; margin-top: 10px; border-left: 3px solid #b71c1c; }
    </style>
</head>
<body>
    <h2>Neue Anfrage über tinderfoto.ch</h2>
    <table>
        <tr><td>Name:</td><td>{$name}</td></tr>
        <tr><td>E-Mail:</td><td><a href='mailto:{$email}'>{$email}</a></td></tr>
        <tr><td>Angebot:</td><td>{$angebot_label}</td></tr>
        <tr><td>Region:</td><td>{$region_label}</td></tr>
        <tr><td>Telefon:</td><td>{$telefon}</td></tr>
    </table>
    <p><strong>Nachricht:</strong></p>
    <div class='message'>" . nl2br($nachricht) . "</div>
    <br>
    <p style='color: #999; font-size: 12px;'>Diese Nachricht wurde über das Kontaktformular auf tinderfoto.ch gesendet.</p>
</body>
</html>
";

// E-Mail-Header
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Tinderfoto Webformular <noreply@tinderfoto.ch>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// E-Mail versenden
$mail_sent = mail($empfaenger, $betreff, $mail_body, $headers);

// Bestätigungsmail an den Kunden
if ($mail_sent && !empty($email)) {
    $kunden_betreff = 'Deine Anfrage bei Tinderfoto – Wir melden uns!';
    $kunden_body = "
<html>
<head><meta charset='UTF-8'></head>
<body style='font-family: Arial, sans-serif; color: #333;'>
    <h2 style='color: #b71c1c;'>Vielen Dank für deine Anfrage, {$name}!</h2>
    <p>Wir haben deine Anfrage erhalten und werden uns so schnell wie möglich bei dir melden – in der Regel innert 48 Stunden.</p>
    <p><strong>Deine Angaben:</strong></p>
    <ul>
        <li>Angebot: {$angebot_label}</li>
        <li>Region: {$region_label}</li>
    </ul>
    <p>Falls du Fragen hast, erreichst du uns jederzeit unter:<br>
    Tel: 076 344 17 94<br>
    E-Mail: info@tinderfoto.ch</p>
    <p>Herzliche Grüsse,<br>Bruno Birkhofer<br>Tinderfoto.ch</p>
</body>
</html>
";
    $kunden_headers = "MIME-Version: 1.0\r\n";
    $kunden_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $kunden_headers .= "From: Tinderfoto <info@tinderfoto.ch>\r\n";

    mail($email, $kunden_betreff, $kunden_body, $kunden_headers);
}

// Weiterleiten
if ($mail_sent) {
    header('Location: ' . $success_redirect);
} else {
    header('Location: buchen.html?error=mail');
}
exit;
?>
