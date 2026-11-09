<?php
declare(strict_types=1);
// logout.php — keluar dari CMS. Hanya lewat POST bertoken agar situs lain tidak bisa mengeluarkan pengguna; GET menampilkan konfirmasi.
define('CMS_AUTH_PUBLIC', true);
require_once __DIR__ . '/includes/bootstrap.php';
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($_POST['csrf'] ?? ''))) {
    cmsLogout();
    header('Location: login.php');
    exit;
}
if (!cmsCurrentUser()) { header('Location: login.php'); exit; }
$csrf = cmsEnsureCsrfToken();
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Keluar — CMS Virtual Tour</title>
<link rel="stylesheet" href="assets/css/auth.css"></head><body class="auth-page"><main class="auth-card">
<h1>Keluar dari CMS?</h1><p>Perubahan yang belum disimpan di tab lain akan hilang.</p>
<form method="post" class="auth-form"><input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>"><button type="submit">Ya, keluar</button></form>
<footer><a href="javascript:history.back()">Batal</a></footer></main>
</body></html>
