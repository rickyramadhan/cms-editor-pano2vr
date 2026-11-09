<?php
declare(strict_types=1);
// login.php — masuk ke CMS. Bila belum ada akun sama sekali, halaman ini membuat ADMIN PERTAMA
// (hanya dari komputer server sendiri, atau dengan token CMS_SETUP_TOKEN dari variabel lingkungan).
define('CMS_AUTH_PUBLIC', true);
require_once __DIR__ . '/includes/bootstrap.php';
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

$csrf = cmsEnsureCsrfToken();
$setup = cmsUsers() === [];
$setupToken = (string) (getenv('CMS_SETUP_TOKEN') ?: '');
$setupAllowed = $setup && (cmsIsLocalRequest() || $setupToken !== '');
$next = (string) ($_GET['next'] ?? $_POST['next'] ?? 'projects.php');
// hanya tujuan di dalam folder CMS ini (cegah pengalihan ke situs lain)
$target = basename((string) parse_url($next, PHP_URL_PATH));
$next = preg_match('/^[a-z0-9-]+\.php$/', $target) && $target !== 'login.php' && $target !== 'logout.php' ? $target . ((string) parse_url($next, PHP_URL_QUERY) !== '' ? '?' . parse_url($next, PHP_URL_QUERY) : '') : 'projects.php';
if (!$setup && cmsCurrentUser()) { header('Location: ' . $next); exit; }

$error = ''; $username = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        cmsAssertCsrf((string) ($_POST['csrf'] ?? ''), 'Formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.');
        $username = strtolower(trim((string) ($_POST['username'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        if ($setup) {
            if (!$setupAllowed) throw new RuntimeException('Admin pertama hanya dapat dibuat dari komputer server (localhost) atau dengan token penyiapan.');
            if (!cmsIsLocalRequest() && !hash_equals($setupToken, (string) ($_POST['setup_token'] ?? ''))) throw new RuntimeException('Token penyiapan salah.');
            if ($password !== (string) ($_POST['password2'] ?? '')) throw new RuntimeException('Konfirmasi kata sandi tidak sama.');
            cmsUpsertUser($username, ['name' => (string) ($_POST['name'] ?? $username), 'role' => 'admin', 'projects' => ['*'], 'active' => true], $password);
            cmsAudit('setup.admin', ['as' => $username]);
        }
        cmsLogin($username, $password);
        header('Location: ' . $next); exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title><?= $setup ? 'Penyiapan Admin' : 'Masuk' ?> — CMS Virtual Tour</title>
<link rel="stylesheet" href="assets/css/auth.css?v=<?= $e((string) @filemtime(__DIR__ . '/assets/css/auth.css')) ?>">
</head><body class="auth-page">
<main class="auth-card">
  <div class="auth-brand"><span>G</span><div><strong>Govirtual</strong><small>Tour Manager</small></div></div>
  <?php if ($setup): ?>
    <h1>Buat admin pertama</h1>
    <p>Belum ada akun di CMS ini. Akun pertama otomatis menjadi <b>admin</b> untuk semua project.</p>
  <?php else: ?>
    <h1>Masuk ke CMS</h1>
    <p>Kelola virtual tour semua wilayah dari satu dashboard.</p>
  <?php endif; ?>
  <?php if ($error !== ''): ?><div class="auth-alert" role="alert"><?= $e($error) ?></div><?php endif; ?>
  <?php if ($setup && !$setupAllowed): ?>
    <div class="auth-alert">Penyiapan admin pertama hanya bisa dilakukan dari komputer server (buka lewat localhost), atau set variabel lingkungan <code>CMS_SETUP_TOKEN</code>. Alternatif: jalankan <code>php includes/cli-user.php add &lt;nama&gt; admin</code> di server.</div>
  <?php else: ?>
  <form method="post" class="auth-form" autocomplete="on">
    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="next" value="<?= $e($next) ?>">
    <?php if ($setup): ?><label><span>Nama lengkap</span><input name="name" required maxlength="80" autocomplete="name"></label><?php endif; ?>
    <label><span>Nama pengguna</span><input name="username" required value="<?= $e($username) ?>" autocomplete="username" autocapitalize="none" spellcheck="false" pattern="[a-z0-9][a-z0-9._\-]{2,39}" title="3–40 karakter: huruf kecil, angka, titik, - atau _"></label>
    <label><span>Kata sandi</span><input type="password" name="password" required autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>" <?= $setup ? 'minlength="10"' : '' ?>></label>
    <?php if ($setup): ?>
      <label><span>Ulangi kata sandi</span><input type="password" name="password2" required minlength="10" autocomplete="new-password"></label>
      <?php if (!cmsIsLocalRequest()): ?><label><span>Token penyiapan</span><input name="setup_token" required autocomplete="off"></label><?php endif; ?>
      <small class="auth-hint">Minimal 10 karakter, memuat huruf dan angka.</small>
    <?php endif; ?>
    <button type="submit"><?= $setup ? 'Buat admin & masuk' : 'Masuk' ?></button>
  </form>
  <?php endif; ?>
  <footer>Sesi berakhir otomatis setelah 8 jam tidak aktif. Lupa kata sandi? Hubungi admin CMS.</footer>
</main>
</body></html>
