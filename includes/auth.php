<?php
declare(strict_types=1);
/*
 * includes/auth.php — login CMS: akun, peran, akses per project, pembatasan percobaan login, log audit.
 *
 * Penyimpanan: <CMS_DATA_DIR>/users.json (bawaan: folder data/ CMS, diblokir .htaccess). Untuk server produksi
 * sebaiknya set variabel lingkungan CMS_DATA_DIR ke folder DI LUAR web root.
 *
 * Peran:
 *   admin  — semua, termasuk kelola pengguna, daftar/hapus project, Terapkan/Lepas Smart Kit, pengaturan & keamanan.
 *   editor — mengelola konten lokasi, bahasa, media, aksi/menu/feed Smart Hub pada project yang diizinkan.
 * Akses project: "projects": ["*"] = semua, atau daftar id project (mis. ["badung"]).
 */

const CMS_ROLES = ['admin' => 'Admin', 'editor' => 'Editor'];
const CMS_IDLE_SECONDS = 8 * 3600;
const CMS_MAX_FAILS = 5;        // per akun
const CMS_MAX_FAILS_IP = 20;    // per alamat IP (kantor bisa berbagi satu IP)
const CMS_LOCK_SECONDS = 15 * 60;

function cmsDataDir(): string
{
    $dir = getenv('CMS_DATA_DIR') ?: dirname(__DIR__) . '/data';
    $dir = rtrim(str_replace('\\', '/', $dir), '/');
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) throw new RuntimeException('Folder data CMS tidak dapat dibuat: ' . $dir);
    if (!is_file($dir . '/.htaccess')) @file_put_contents($dir . '/.htaccess', "Require all denied\nDeny from all\n");
    return $dir;
}

function cmsUsersFile(): string { return cmsDataDir() . '/users.json'; }

function cmsUsers(): array
{
    $f = cmsUsersFile();
    if (!is_file($f)) return [];
    $d = json_decode((string) file_get_contents($f), true);
    return is_array($d['users'] ?? null) ? $d['users'] : [];
}

function cmsSaveUsers(array $users): void
{
    ksort($users, SORT_NATURAL);
    $json = json_encode(['_catatan' => 'Akun CMS. Kata sandi disimpan sebagai hash. Ubah lewat halaman Pengguna atau includes/cli-user.php.', 'users' => $users], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $f = cmsUsersFile();
    if (file_put_contents($f . '.tmp', $json . "\n", LOCK_EX) === false || !rename($f . '.tmp', $f)) throw new RuntimeException('Data pengguna gagal disimpan.');
    @chmod($f, 0660);
}

function cmsAudit(string $event, array $data = []): void
{
    $line = json_encode(['t' => date(DATE_ATOM), 'ip' => cmsClientIp(), 'user' => $_SESSION['cms_user'] ?? null, 'event' => $event] + $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    @file_put_contents(cmsDataDir() . '/audit.log', $line . "\n", FILE_APPEND | LOCK_EX);
}

function cmsClientIp(): string { return (string) ($_SERVER['REMOTE_ADDR'] ?? 'cli'); }
function cmsIsLocalRequest(): bool { return in_array(cmsClientIp(), ['127.0.0.1', '::1'], true); }

function cmsValidUsername(string $u): bool { return (bool) preg_match('/^[a-z0-9][a-z0-9._-]{2,39}$/', $u); }

function cmsValidatePassword(string $p): void
{
    if (mb_strlen($p) < 10) throw new RuntimeException('Kata sandi minimal 10 karakter.');
    if (mb_strlen($p) > 200) throw new RuntimeException('Kata sandi terlalu panjang.');
    if (!preg_match('/[A-Za-z]/', $p) || !preg_match('/[0-9]/', $p)) throw new RuntimeException('Kata sandi harus memuat huruf dan angka.');
}

/** Buat/perbarui akun. $password null = tidak diubah. */
function cmsUpsertUser(string $username, array $fields, ?string $password = null): array
{
    $username = strtolower(trim($username));
    if (!cmsValidUsername($username)) throw new RuntimeException('Nama pengguna 3–40 karakter: huruf kecil, angka, titik, - atau _.');
    $users = cmsUsers();
    $u = $users[$username] ?? ['created' => date(DATE_ATOM)];
    if (!isset($users[$username]) && $password === null) throw new RuntimeException('Kata sandi wajib untuk akun baru.');
    if (array_key_exists('name', $fields)) $u['name'] = mb_substr(trim(strip_tags((string) $fields['name'])), 0, 80) ?: $username;
    if (array_key_exists('role', $fields)) { if (!isset(CMS_ROLES[$fields['role']])) throw new RuntimeException('Peran tidak dikenal.'); $u['role'] = $fields['role']; }
    if (array_key_exists('projects', $fields)) {
        $p = array_values(array_unique(array_filter(array_map(static fn($x) => strtolower(trim((string) $x)), (array) $fields['projects']), static fn($x) => $x === '*' || preg_match('/^[a-z0-9_-]{1,60}$/', $x))));
        $u['projects'] = in_array('*', $p, true) ? ['*'] : $p;
    }
    if (array_key_exists('active', $fields)) $u['active'] = (bool) $fields['active'];
    if ($password !== null) { cmsValidatePassword($password); $u['hash'] = password_hash($password, PASSWORD_DEFAULT); $u['pw_changed'] = date(DATE_ATOM); }
    $u += ['name' => $username, 'role' => 'editor', 'projects' => [], 'active' => true];
    $users[$username] = $u;
    // selalu harus ada minimal satu admin aktif
    if (!array_filter($users, static fn($x) => ($x['role'] ?? '') === 'admin' && ($x['active'] ?? true))) throw new RuntimeException('Harus ada minimal satu admin aktif.');
    cmsSaveUsers($users);
    return $u;
}

function cmsDeleteUser(string $username): void
{
    $users = cmsUsers();
    if (!isset($users[$username])) throw new RuntimeException('Pengguna tidak ditemukan.');
    if ($username === ($_SESSION['cms_user'] ?? '')) throw new RuntimeException('Tidak dapat menghapus akun sendiri.');
    unset($users[$username]);
    if (!array_filter($users, static fn($x) => ($x['role'] ?? '') === 'admin' && ($x['active'] ?? true))) throw new RuntimeException('Harus ada minimal satu admin aktif.');
    cmsSaveUsers($users);
}

/* ------------------------------ pembatasan percobaan ------------------------------ */
function cmsAttempts(callable $fn): mixed
{
    $f = cmsDataDir() . '/login-attempts.json';
    $h = fopen($f, 'c+'); if (!$h) return $fn([])[1] ?? null;
    flock($h, LOCK_EX);
    $data = json_decode((string) stream_get_contents($h), true) ?: [];
    $now = time();
    foreach ($data as $k => $v) if (($v['until'] ?? 0) < $now && ($v['first'] ?? 0) < $now - CMS_LOCK_SECONDS) unset($data[$k]);
    [$data, $result] = $fn($data);
    ftruncate($h, 0); rewind($h); fwrite($h, (string) json_encode($data)); fflush($h);
    flock($h, LOCK_UN); fclose($h);
    return $result;
}

function cmsLockedFor(string $username): int
{
    return (int) cmsAttempts(static function (array $d) use ($username) {
        $now = time(); $wait = 0;
        foreach (['ip:' . cmsClientIp(), 'u:' . $username] as $k) if (($d[$k]['until'] ?? 0) > $now) $wait = max($wait, $d[$k]['until'] - $now);
        return [$d, $wait];
    });
}

function cmsRecordAttempt(string $username, bool $ok): void
{
    cmsAttempts(static function (array $d) use ($username, $ok) {
        $now = time();
        foreach (['ip:' . cmsClientIp(), 'u:' . $username] as $k) {
            if ($ok) { if (str_starts_with($k, 'u:')) unset($d[$k]); continue; }   // login berhasil tidak menghapus hitungan IP
            $e = $d[$k] ?? ['n' => 0, 'first' => $now];
            if ($e['first'] < $now - CMS_LOCK_SECONDS) $e = ['n' => 0, 'first' => $now];
            $e['n']++;
            if ($e['n'] >= (str_starts_with($k, 'ip:') ? CMS_MAX_FAILS_IP : CMS_MAX_FAILS)) $e['until'] = $now + CMS_LOCK_SECONDS;
            $d[$k] = $e;
        }
        return [$d, null];
    });
}

/* ------------------------------ sesi ------------------------------ */
function cmsLogin(string $username, string $password): array
{
    $username = strtolower(trim($username));
    $wait = cmsLockedFor($username);
    if ($wait > 0) throw new RuntimeException('Terlalu banyak percobaan gagal. Coba lagi dalam ' . (int) ceil($wait / 60) . ' menit.');
    $u = cmsUsers()[$username] ?? null;
    // verifikasi tetap dijalankan walau akun tidak ada (waktu respons seragam)
    $ok = password_verify($password, (string) ($u['hash'] ?? '$2y$10$YhlrSyQdKpEoJ6PbjG.t4elalRz5O/YYgB6c9AXpGbgzURG/mI0sC')) && $u !== null && ($u['active'] ?? true);
    cmsRecordAttempt($username, $ok);
    if (!$ok) { cmsAudit('login.gagal', ['as' => $username]); usleep(400000); throw new RuntimeException('Nama pengguna atau kata sandi salah.'); }
    if (password_needs_rehash((string) $u['hash'], PASSWORD_DEFAULT)) { $users = cmsUsers(); $users[$username]['hash'] = password_hash($password, PASSWORD_DEFAULT); cmsSaveUsers($users); }
    session_regenerate_id(true);
    $_SESSION['cms_user'] = $username;
    $_SESSION['cms_seen'] = time();
    $_SESSION['cms_csrf'] = bin2hex(random_bytes(24));
    unset($_SESSION['cms_project']);
    $users = cmsUsers(); $users[$username]['last_login'] = date(DATE_ATOM); cmsSaveUsers($users);
    cmsAudit('login.ok');
    return $u;
}

function cmsLogout(): void
{
    cmsAudit('logout');
    $_SESSION = [];
    if (ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'domain' => $p['domain'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']); }
    session_destroy();
}

/** Pengguna yang sedang login (dibaca ulang dari berkas tiap permintaan: penonaktifan langsung berlaku) atau null. */
function cmsCurrentUser(): ?array
{
    static $cache = false;
    if ($cache !== false) return $cache;
    $name = (string) ($_SESSION['cms_user'] ?? '');
    if ($name === '') return $cache = null;
    if ((int) ($_SESSION['cms_seen'] ?? 0) < time() - CMS_IDLE_SECONDS) { unset($_SESSION['cms_user']); return $cache = null; }
    $u = cmsUsers()[$name] ?? null;
    if (!$u || !($u['active'] ?? true)) { unset($_SESSION['cms_user']); return $cache = null; }
    $_SESSION['cms_seen'] = time();
    return $cache = ['username' => $name] + $u;
}

function cmsIsAdmin(): bool { return (cmsCurrentUser()['role'] ?? '') === 'admin'; }

function cmsCanAccessProject(string $projectId): bool
{
    $u = cmsCurrentUser();
    if (!$u) return false;
    $p = (array) ($u['projects'] ?? []);
    return ($u['role'] ?? '') === 'admin' || in_array('*', $p, true) || in_array($projectId, $p, true);
}

function cmsWantsJson(): bool
{
    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (!in_array($script, ['index.php', 'projects.php', 'users.php'], true)) return true;   // endpoint fetch()
    return str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') || str_contains((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');
}

function cmsDeny(int $status, string $message): never
{
    if (cmsWantsJson()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message, 'auth' => $status === 401 ? 'login' : 'forbidden'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($status === 401) { header('Location: login.php?next=' . rawurlencode((string) ($_SERVER['REQUEST_URI'] ?? 'index.php'))); exit; }
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>Akses ditolak</title><body style="font:15px system-ui;padding:40px"><h1>Akses ditolak</h1><p>' . htmlspecialchars($message, ENT_QUOTES) . '</p><p><a href="projects.php">Kembali</a> · <a href="logout.php">Keluar</a></p>';
    exit;
}

/** Gerbang semua halaman & endpoint CMS (dipanggil otomatis oleh project-manager.php). */
function cmsAuthGate(): void
{
    if (PHP_SAPI === 'cli' || defined('CMS_AUTH_PUBLIC')) return;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    if (!cmsCurrentUser()) cmsDeny(401, 'Sesi login berakhir. Silakan masuk kembali.');
}

/** Hentikan permintaan bila bukan admin. */
function cmsRequireAdmin(string $what = 'Tindakan ini'): void
{
    if (!cmsIsAdmin()) { cmsAudit('ditolak', ['what' => $what]); cmsDeny(403, $what . ' hanya untuk admin.'); }
}
