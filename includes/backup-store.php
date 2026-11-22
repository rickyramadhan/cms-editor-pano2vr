<?php
/**
 * Folder cadangan PUSAT (aturan: tidak ada cadangan di dalam folder tur / wilayah).
 *
 * Semua cadangan yang dibuat CMS (skin, bahasa, pano.xml, database, index.html, dll.) ditulis ke
 *   <CMS>/data/backups/<jalur relatif terhadap htdocs>/<jenis>-backups/
 * contoh: C:/xampp/htdocs/badung/pantai-kuta  +  'skin'  =>  <CMS>/data/backups/badung/pantai-kuta/skin-backups
 * Folder data/ dilindungi (.htaccess "Require all denied") dan tidak ikut git, sehingga folder tur/wilayah tetap bersih
 * saat di-commit atau di-deploy. Memulihkan: salin balik berkas dari jalur itu ke lokasi aslinya.
 *
 * Jenis yang dipakai: skin, language, media, database, history, deploy.
 */
if (function_exists('cmsBackupPathFor')) return;

function cmsBackupBase(): string
{
    return str_replace('\\', '/', dirname(__DIR__)) . '/data/backups';
}

/** Folder cadangan pusat untuk satu folder sumber (folder tur, folder assets wilayah, dsb.) dan satu jenis cadangan. Belum dibuat; pemanggil membuat sesuai kebutuhan. */
function cmsBackupPathFor(string $sourceDir, string $kind): string
{
    $src = rtrim(str_replace('\\', '/', $sourceDir), '/');
    $real = @realpath($src);
    if ($real !== false) $src = rtrim(str_replace('\\', '/', $real), '/');
    $htdocs = str_replace('\\', '/', dirname(dirname(__DIR__)));   // folder induk CMS = htdocs
    if (stripos($src . '/', $htdocs . '/') === 0) $rel = trim(substr($src, strlen($htdocs)), '/');
    else $rel = 'luar-htdocs/' . substr(sha1($src), 0, 10) . '-' . basename($src);
    $rel = preg_replace('~[^A-Za-z0-9._/-]~', '_', $rel);
    $kind = preg_replace('~[^a-z0-9-]~i', '', $kind) ?: 'lain';
    return cmsBackupBase() . '/' . $rel . '/' . $kind . '-backups';
}

/** Pastikan folder ada dan dilindungi dari akses web; kembalikan jalurnya. */
function cmsBackupEnsure(string $sourceDir, string $kind): string
{
    $dir = cmsBackupPathFor($sourceDir, $kind);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Folder cadangan pusat gagal dibuat: ' . $dir);
    $guard = cmsBackupBase() . '/.htaccess';
    if (!is_file($guard)) @file_put_contents($guard, "Require all denied\nDeny from all\n");
    return $dir;
}
