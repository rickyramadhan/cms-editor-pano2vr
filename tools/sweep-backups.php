<?php
/**
 * sweep-backups.php: pindahkan semua cadangan yang menempel di folder tur/wilayah ke folder pusat CMS (data/backups).
 *
 * Aturan: folder wilayah (badung, jakarta, ...) dan folder tur-nya TIDAK boleh berisi cadangan apa pun
 * (.ai-generator-backup, .skin-backups, .language-backups, .media-backups, .database-backups, .history, folder/berkas *backup*, *.bak, dst.),
 * supaya bersih saat di-commit ke git dan di-upload ke server.
 *
 * Pakai (PHP CLI):
 *   php tools/sweep-backups.php badung jakarta            -> DRY RUN: hanya melaporkan apa yang akan dipindah
 *   php tools/sweep-backups.php badung jakarta --apply    -> pindahkan sungguhan
 *   php tools/sweep-backups.php badung --check            -> keluar kode 1 bila masih ada cadangan (untuk pemeriksaan sebelum deploy/commit)
 * Argumen wilayah = nama folder di htdocs (atau jalur lengkap). Tujuan: <CMS>/data/backups/<jalur relatif>/<jenis>-backups/
 * Memulihkan: pindahkan balik dari folder pusat ke lokasi aslinya (jalur relatif sama).
 */
require_once dirname(__DIR__) . '/includes/backup-store.php';

$args = array_slice($argv, 1);
$apply = in_array('--apply', $args, true);
$check = in_array('--check', $args, true);
$regions = array_values(array_filter($args, static fn($a) => $a !== '' && $a[0] !== '-'));
if (!$regions) { fwrite(STDERR, "Pakai: php tools/sweep-backups.php <wilayah...> [--apply|--check]\n"); exit(2); }

$htdocs = str_replace('\\', '/', dirname(dirname(__DIR__)));
const SKIP_DIRS = ['tiles', 'node_modules', '.git', 'smart-kit', '.claude', 'graphify-out'];
const DIR_KIND = ['.ai-generator-backup' => 'deploy', '.skin-backups' => 'skin', '.language-backups' => 'language', '.media-backups' => 'media', '.database-backups' => 'database', '.history' => 'history'];
$isBackupDir = static fn(string $n): bool => isset(DIR_KIND[$n]) || (bool) preg_match('~(^|[-_.])(backup|backups|bak|old-backup)([-_.]|\d|$)~i', $n);
$isBackupFile = static fn(string $n): bool => (bool) preg_match('#\.(bak|orig|old)$|~$|(^|[-_.])backup([-_.]|\d|$)#i', $n);

function bkSizeOf(string $p): int { if (is_file($p)) return (int) filesize($p); $s = 0; foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($p, FilesystemIterator::SKIP_DOTS)) as $f) if ($f->isFile()) $s += $f->getSize(); return $s; }
function moveInto(string $from, string $to): void
{
    if (!is_dir(dirname($to)) && !mkdir(dirname($to), 0775, true) && !is_dir(dirname($to))) throw new RuntimeException('Gagal membuat ' . dirname($to));
    if (!file_exists($to)) { if (!@rename($from, $to)) throw new RuntimeException('Gagal memindah ' . $from); return; }
    // tujuan sudah ada (sweep ulang): gabungkan, tabrakan nama diberi akhiran waktu
    if (is_dir($from)) { foreach (scandir($from) as $c) { if ($c === '.' || $c === '..') continue; moveInto($from . '/' . $c, $to . '/' . $c); } @rmdir($from); return; }
    $alt = $to . '.' . date('His') . '-' . bin2hex(random_bytes(2));
    if (!@rename($from, $alt)) throw new RuntimeException('Gagal memindah ' . $from);
}

$found = [];   // [from, to, bytes, label]
$walk = function (string $dir) use (&$walk, &$found, $isBackupDir, $isBackupFile, $htdocs): void {
    foreach (scandir($dir) ?: [] as $name) {
        if ($name === '.' || $name === '..') continue;
        $path = $dir . '/' . $name;
        if (is_dir($path)) {
            if (in_array($name, SKIP_DIRS, true)) continue;
            if ($isBackupDir($name)) {
                $kind = DIR_KIND[$name] ?? null;
                $to = $kind ? cmsBackupPathFor($dir, $kind) : cmsBackupPathFor($dir, 'lain-' . preg_replace('~[^A-Za-z0-9-]~', '-', trim($name, '.')));
                $found[] = [$path, $to, bkSizeOf($path), substr($path, strlen($htdocs) + 1)];
                continue;   // jangan masuk ke dalam folder cadangan
            }
            $walk($path);
        } elseif ($isBackupFile($name)) {
            $to = cmsBackupPathFor($dir, 'file') . '/' . $name;
            $found[] = [$path, $to, (int) filesize($path), substr($path, strlen($htdocs) + 1)];
        }
    }
};
foreach ($regions as $r) {
    $root = is_dir($r) ? str_replace('\\', '/', realpath($r)) : $htdocs . '/' . trim($r, '/\\');
    if (!is_dir($root)) { fwrite(STDERR, "Folder tidak ada: $root\n"); continue; }
    $walk($root);
}

$total = array_sum(array_column($found, 2));
foreach ($found as [$from, $to, $b, $label]) printf("%s%8.1f MB  %s\n", $apply ? 'PINDAH ' : '', $b / 1048576, $label);
printf("\n%d item cadangan, %.1f MB.%s\n", count($found), $total / 1048576, $apply ? '' : ($found ? ' (dry run: tambahkan --apply untuk memindahkan ke ' . cmsBackupBase() . ')' : ' Bersih.'));
if ($check) exit($found ? 1 : 0);
if (!$apply || !$found) exit(0);

cmsBackupEnsure($htdocs . '/' . $regions[0], 'sweep');   // memastikan data/backups/.htaccess ada
$ok = 0; $fail = 0;
foreach ($found as [$from, $to, $b, $label]) {
    try { moveInto($from, $to); $ok++; } catch (Throwable $e) { $fail++; fwrite(STDERR, 'GAGAL ' . $label . ': ' . $e->getMessage() . "\n"); }
}
printf("Selesai: %d dipindah, %d gagal. Tujuan: %s\n", $ok, $fail, cmsBackupBase());
exit($fail ? 1 : 0);
