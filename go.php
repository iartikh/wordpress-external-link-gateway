<?php
/**
 * External Link Gateway: informational interstitial page.
 * No automatic server-side redirect is performed.
 */
declare(strict_types=1);

$allowedHosts = ['devarts.pro', 'www.devarts.pro']; // Change to your own hostnames.
$siteName = 'My website'; // Change to your website name.

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Robots-Tag: noindex, nofollow, noarchive');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");

function fail(int $code, string $message): never {
    http_response_code($code);
    header('Content-Type: text/plain; charset=UTF-8');
    exit($message);
}

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$refererHost = strtolower((string) parse_url($referer, PHP_URL_HOST));
$fetchSite = strtolower($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '');
$fromOwnSite = in_array($refererHost, $allowedHosts, true) || $fetchSite === 'same-origin';
if (!$fromOwnSite) {
    fail(403, 'Open this link from a page on the website.');
}

$encoded = $_GET['go'] ?? '';
if (!is_string($encoded) || $encoded === '' || strlen($encoded) > 8192) {
    fail(400, 'Invalid link.');
}
// Accept both URL-safe Base64 and legacy Base64 produced by btoa().
$normal = strtr($encoded, '-_', '+/');
if (!preg_match('/^[A-Za-z0-9+\/=]+$/D', $normal)) {
    fail(400, 'Invalid link encoding.');
}
$decoded = base64_decode($normal, true);
if ($decoded === false || strlen($decoded) > 4096 || !filter_var($decoded, FILTER_VALIDATE_URL)) {
    fail(400, 'Invalid destination.');
}
$parts = parse_url($decoded);
$scheme = strtolower($parts['scheme'] ?? '');
if (!in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
    fail(400, 'Unsupported destination.');
}
// Do not present embedded credentials or control characters as a destination.
if (isset($parts['user']) || isset($parts['pass']) || preg_match('/[\x00-\x1F\x7F]/', $decoded)) {
    fail(400, 'Unsupported destination.');
}
$displayUrl = htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$displayHost = htmlspecialchars((string)$parts['host'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$displaySite = htmlspecialchars($siteName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow,noarchive">
  <title>Leaving <?= $displaySite ?></title>
  <style>
    body{font:16px/1.6 system-ui,-apple-system,sans-serif;max-width:650px;margin:12vh auto;padding:0 20px;color:#202124}
    .destination{overflow-wrap:anywhere;padding:14px;background:#f4f5f7;border-radius:8px}
    .button{display:inline-block;background:#202124;color:white;padding:12px 18px;border-radius:7px;text-decoration:none;margin-top:16px}
    .muted{color:#666}
  </style>
</head>
<body>
  <h1>You are leaving <?= $displaySite ?></h1>
  <p>This link leads to an external website. Please check the destination before continuing.</p>
  <p class="destination"><strong><?= $displayHost ?></strong><br><?= $displayUrl ?></p>
  <a class="button" href="<?= $displayUrl ?>" rel="nofollow noopener noreferrer">Continue to external website</a>
  <p class="muted">This page does not automatically redirect you.</p>
</body>
</html>
