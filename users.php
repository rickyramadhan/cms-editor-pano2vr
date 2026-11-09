<?php
declare(strict_types=1);
// users.php — kelola akun CMS (admin) dan ganti kata sandi sendiri (semua pengguna).
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/project-manager.php';   // gerbang login
header('Cache-Control: no-store');

$me = cmsCurrentUser();
$isAdmin = cmsIsAdmin();
$csrf = cmsEnsureCsrfToken();
$msg = ''; $err = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        cmsAssertCsrf((string) ($_POST['csrf'] ?? ''));
        $op = (string) ($_POST['op'] ?? '');
        if ($op === 'password') {
            $u = cmsUsers()[$me['username']] ?? null;
            if (!$u || !password_verify((string) ($_POST['current'] ?? ''), (string) $u['hash'])) throw new RuntimeException('Kata sandi saat ini salah.');
            if ((string) ($_POST['new'] ?? '') !== (string) ($_POST['new2'] ?? '')) throw new RuntimeException('Konfirmasi kata sandi baru tidak sama.');
            cmsUpsertUser($me['username'], [], (string) $_POST['new']);
            session_regenerate_id(true);
            cmsAudit('password.ganti');
            $msg = 'Kata sandi Anda berhasil diganti.';
        } else {
            cmsRequireAdmin('Mengelola pengguna');
            $name = strtolower(trim((string) ($_POST['username'] ?? '')));
            $projects = (array) ($_POST['projects'] ?? []);
            if (in_array($op, ['add', 'update'], true)) {
                if ($op === 'add' && isset(cmsUsers()[$name])) throw new RuntimeException('Nama pengguna sudah dipakai.');
                if ($op === 'update' && !isset(cmsUsers()[$name])) throw new RuntimeException('Pengguna tidak ditemukan.');
                if ($op === 'update' && $name === $me['username'] && (($_POST['role'] ?? '') !== 'admin' || empty($_POST['active']))) throw new RuntimeException('Anda tidak dapat menurunkan peran atau menonaktifkan akun sendiri.');
                $pw = trim((string) ($_POST['password'] ?? ''));
                cmsUpsertUser($name, ['name' => (string) ($_POST['name'] ?? $name), 'role' => (string) ($_POST['role'] ?? 'editor'), 'projects' => $projects, 'active' => !empty($_POST['active'])], $pw === '' ? null : $pw);
                cmsAudit('pengguna.' . $op, ['target' => $name, 'role' => $_POST['role'] ?? '', 'projects' => $projects, 'password_diubah' => $pw !== '']);
                $msg = $op === 'add' ? 'Akun ' . $name . ' dibuat.' : 'Akun ' . $name . ' diperbarui.';
            } elseif ($op === 'delete') {
                cmsDeleteUser($name);
                cmsAudit('pengguna.hapus', ['target' => $name]);
                $msg = 'Akun ' . $name . ' dihapus.';
            }
        }
    } catch (Throwable $x) { $err = $x->getMessage(); }
}
$users = cmsUsers();
$projects = projectRegistry();
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$projectChecks = static function (array $selected) use ($projects, $e): string {
    $all = in_array('*', $selected, true);
    $out = '<div class="u-checks"><label><input type="checkbox" name="projects[]" value="*"' . ($all ? ' checked' : '') . '> Semua project</label>';
    foreach ($projects as $id => $p) $out .= '<label><input type="checkbox" name="projects[]" value="' . $e($id) . '"' . (!$all && in_array($id, $selected, true) ? ' checked' : '') . '> ' . $e($p['name'] ?? $id) . '</label>';
    return $out . '</div>';
};
$roleSelect = static function (string $cur) use ($e): string { $o = ''; foreach (CMS_ROLES as $k => $t) $o .= '<option value="' . $e($k) . '"' . ($k === $cur ? ' selected' : '') . '>' . $e($t) . '</option>'; return '<select name="role">' . $o . '</select>'; };
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title><?= $isAdmin ? 'Pengguna' : 'Akun saya' ?> — CMS Virtual Tour</title>
<link rel="stylesheet" href="assets/css/auth.css?v=<?= $e((string) @filemtime(__DIR__ . '/assets/css/auth.css')) ?>">
</head><body class="users-page"><main class="users-shell">
<header><div><h1><?= $isAdmin ? 'Pengguna CMS' : 'Akun saya' ?></h1><small>Masuk sebagai <b><?= $e($me['name'] ?? $me['username']) ?></b> (<?= $e(CMS_ROLES[$me['role']] ?? $me['role']) ?>)</small></div>
<nav><a href="projects.php">← Project</a><a href="index.php?tab=dashboard">Dashboard</a><a href="logout.php">Keluar</a></nav></header>
<?php if ($msg !== ''): ?><div class="auth-ok" role="status"><?= $e($msg) ?></div><?php endif; ?>
<?php if ($err !== ''): ?><div class="auth-alert" role="alert"><?= $e($err) ?></div><?php endif; ?>

<?php if ($isAdmin): ?>
<section class="u-card">
  <h2>Daftar pengguna</h2>
  <p><b>Admin</b>: semua termasuk pengguna, project, Terapkan/Lepas Smart Kit, dan pengaturan keamanan. <b>Editor</b>: konten, bahasa, media, serta aksi/menu/feed Smart Hub pada project yang dicentang.</p>
  <table class="u-table"><thead><tr><th>Pengguna</th><th>Peran</th><th>Project</th><th>Terakhir masuk</th><th></th></tr></thead><tbody>
  <?php foreach ($users as $name => $u): $sel = (array) ($u['projects'] ?? []); ?>
    <tr>
      <td><b><?= $e($u['name'] ?? $name) ?></b><small><?= $e($name) ?><?= ($u['active'] ?? true) ? '' : ' · nonaktif' ?></small></td>
      <td><span class="u-badge <?= ($u['active'] ?? true) ? '' : 'is-off' ?>"><?= $e(CMS_ROLES[$u['role'] ?? ''] ?? '?') ?></span></td>
      <td><?= ($u['role'] ?? '') === 'admin' || in_array('*', $sel, true) ? 'Semua' : $e(implode(', ', $sel) ?: '— belum ada —') ?></td>
      <td><small><?= $e($u['last_login'] ?? 'belum pernah') ?></small></td>
      <td>
        <details class="u-edit"><summary>Ubah</summary>
          <form method="post" class="u-form">
            <input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="op" value="update"><input type="hidden" name="username" value="<?= $e($name) ?>">
            <label>Nama <input name="name" value="<?= $e($u['name'] ?? $name) ?>" maxlength="80"></label>
            <label>Peran <?= $roleSelect((string) ($u['role'] ?? 'editor')) ?></label>
            <label>Akses project <?= $projectChecks($sel) ?></label>
            <label class="u-checks"><span><input type="checkbox" name="active" value="1" <?= ($u['active'] ?? true) ? 'checked' : '' ?>> Aktif</span></label>
            <label>Kata sandi baru <small>kosongkan bila tidak diganti</small><input type="password" name="password" autocomplete="new-password" minlength="10"></label>
            <div class="u-actions"><button class="is-primary" type="submit">Simpan</button></div>
          </form>
          <?php if ($name !== $me['username']): ?>
          <form method="post" class="u-actions" onsubmit="return confirm('Hapus akun <?= $e($name) ?>?')"><input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="op" value="delete"><input type="hidden" name="username" value="<?= $e($name) ?>"><button class="is-danger" type="submit">Hapus akun</button></form>
          <?php endif; ?>
        </details>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</section>

<section class="u-card">
  <h2>Tambah pengguna</h2>
  <p>Berikan kata sandi awal secara langsung (bukan lewat chat grup); minta pengguna menggantinya setelah masuk.</p>
  <form method="post" class="u-form">
    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="op" value="add"><input type="hidden" name="active" value="1">
    <div class="u-grid">
      <label>Nama pengguna <input name="username" required pattern="[a-z0-9][a-z0-9._\-]{2,39}" autocapitalize="none" spellcheck="false" placeholder="mis. tim.badung"></label>
      <label>Nama lengkap <input name="name" required maxlength="80"></label>
      <label>Peran <?= $roleSelect('editor') ?></label>
      <label>Kata sandi awal <input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
    </div>
    <label>Akses project <?= $projectChecks([]) ?></label>
    <div class="u-actions"><button class="is-primary" type="submit">Tambah pengguna</button></div>
  </form>
</section>
<?php endif; ?>

<section class="u-card">
  <h2>Ganti kata sandi saya</h2>
  <p>Minimal 10 karakter, memuat huruf dan angka.</p>
  <form method="post" class="u-form" autocomplete="on">
    <input type="hidden" name="csrf" value="<?= $e($csrf) ?>"><input type="hidden" name="op" value="password">
    <input type="text" name="username" value="<?= $e($me['username']) ?>" autocomplete="username" hidden>
    <div class="u-grid">
      <label>Kata sandi saat ini <input type="password" name="current" required autocomplete="current-password"></label>
      <label>Kata sandi baru <input type="password" name="new" required minlength="10" autocomplete="new-password"></label>
      <label>Ulangi kata sandi baru <input type="password" name="new2" required minlength="10" autocomplete="new-password"></label>
    </div>
    <div class="u-actions"><button class="is-primary" type="submit">Ganti kata sandi</button></div>
  </form>
</section>
</main></body></html>
