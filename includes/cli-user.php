<?php
declare(strict_types=1);
// cli-user.php — kelola akun CMS dari terminal server (berguna untuk admin pertama di server tanpa akses localhost, atau lupa sandi admin).
//   php includes/cli-user.php list
//   php includes/cli-user.php add <nama> <admin|editor> [project1,project2|*]     (kata sandi ditanyakan, tidak tampil)
//   php includes/cli-user.php passwd <nama>
//   php includes/cli-user.php disable <nama> | enable <nama>
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/auth.php';

function ask(string $label): string
{
    fwrite(STDOUT, $label);
    if (DIRECTORY_SEPARATOR === '/') { @shell_exec('stty -echo'); $v = trim((string) fgets(STDIN)); @shell_exec('stty echo'); fwrite(STDOUT, "\n"); return $v; }
    return trim((string) fgets(STDIN));   // Windows: input terlihat — jalankan di layar yang aman
}

$cmd = $argv[1] ?? 'help'; $name = strtolower((string) ($argv[2] ?? ''));
try {
    switch ($cmd) {
        case 'list':
            foreach (cmsUsers() as $n => $u) printf("%-22s %-7s %-6s %s\n", $n, $u['role'] ?? '?', ($u['active'] ?? true) ? 'aktif' : 'mati', implode(',', (array) ($u['projects'] ?? [])));
            break;
        case 'add':
            $role = (string) ($argv[3] ?? 'editor');
            $projects = explode(',', (string) ($argv[4] ?? ($role === 'admin' ? '*' : '')));
            if (isset(cmsUsers()[$name])) throw new RuntimeException('Pengguna sudah ada.');
            $p = ask('Kata sandi: '); if ($p !== ask('Ulangi: ')) throw new RuntimeException('Tidak sama.');
            cmsUpsertUser($name, ['name' => $name, 'role' => $role, 'projects' => $projects, 'active' => true], $p);
            echo "Akun $name ($role) dibuat.\n"; break;
        case 'passwd':
            if (!isset(cmsUsers()[$name])) throw new RuntimeException('Pengguna tidak ditemukan.');
            $p = ask('Kata sandi baru: '); if ($p !== ask('Ulangi: ')) throw new RuntimeException('Tidak sama.');
            cmsUpsertUser($name, [], $p); echo "Kata sandi $name diganti.\n"; break;
        case 'disable': case 'enable':
            if (!isset(cmsUsers()[$name])) throw new RuntimeException('Pengguna tidak ditemukan.');
            cmsUpsertUser($name, ['active' => $cmd === 'enable']); echo "Akun $name " . ($cmd === 'enable' ? 'diaktifkan' : 'dinonaktifkan') . ".\n"; break;
        default:
            echo "Pakai: list | add <nama> <admin|editor> [project,..|*] | passwd <nama> | disable <nama> | enable <nama>\nData: " . cmsUsersFile() . "\n";
    }
} catch (Throwable $e) { fwrite(STDERR, 'Gagal: ' . $e->getMessage() . "\n"); exit(1); }
