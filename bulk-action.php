<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';   // sesi aman + login
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/project-manager.php';

function respond(int $status, bool $success, string $message, array $extra = []): never
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function cleanId(string $value): string
{
    return strtolower((string) preg_replace('/[^a-z0-9_-]/i', '', trim($value)));
}

function readDatabase(string $file): array
{
    $content = (string) file_get_contents($file);
    if (!preg_match('/window\.GLOBAL_DATABASE\s*=\s*(\{.*?\});/s', $content, $matches)) throw new RuntimeException('Database lokasi tidak dapat dibaca.');
    return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
}

function saveDatabase(string $file, array $database): void
{
    $content = (string) file_get_contents($file);
    $json = json_encode($database, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $updated = preg_replace('/window\.GLOBAL_DATABASE\s*=\s*\{.*?\};/s', 'window.GLOBAL_DATABASE = ' . $json . ';', $content, 1, $count);
    if ($updated === null || $count !== 1 || file_put_contents($file, $updated, LOCK_EX) === false) throw new RuntimeException('Database lokasi gagal disimpan.');
}

function bulkEditDatabase(string $file, array $ids, array $changes): array
{
    $lock = fopen($file . '.lock', 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) throw new RuntimeException('Lock database lokasi tidak dapat diperoleh.');
    try {
        $content = file_get_contents($file);
        if ($content === false || !preg_match('/window\.GLOBAL_DATABASE\s*=\s*(\{.*?\});/s', $content, $matches)) throw new RuntimeException('Database lokasi tidak dapat dibaca.');
        $database = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($database)) throw new RuntimeException('Struktur database lokasi tidak valid.');
        $results = []; $changedCount = 0;
        foreach ($ids as $id) {
            if (!array_key_exists($id, $database) || !is_array($database[$id])) { $results[] = ['id'=>$id,'success'=>false,'changed'=>false,'message'=>'Record lokasi tidak ditemukan dalam database.']; continue; }
            $location = $database[$id]; $changedFields = [];
            if ($changes['category']['mode'] === 'set' && ($location['category'] ?? null) !== $changes['category']['value']) { $location['category']=$changes['category']['value']; $changedFields[]='kategori'; }
            if ($changes['has_drone']['mode'] === 'set' && ($location['has_drone'] ?? null) !== $changes['has_drone']['value']) { $location['has_drone']=$changes['has_drone']['value']; $changedFields[]='status drone'; }
            if ($changes['drone']['mode'] === 'set' && ($location['drone'] ?? null) !== $changes['drone']['value']) { $location['drone']=$changes['drone']['value']; $changedFields[]='node drone'; }
            if ($changedFields === []) { $results[]=['id'=>$id,'success'=>true,'changed'=>false,'message'=>'Tidak ada nilai yang berubah.']; continue; }
            $database[$id]=$location; $changedCount++; $results[]=['id'=>$id,'success'=>true,'changed'=>true,'message'=>'Diubah: '.implode(', ', $changedFields).'.'];
        }
        if ($changedCount === 0) return [$results, 0, null];
        $json=json_encode($database, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        $updated=preg_replace('/window\.GLOBAL_DATABASE\s*=\s*\{.*?\};/s', 'window.GLOBAL_DATABASE = '.$json.';', $content, 1, $count);
        if ($updated === null || $count !== 1) throw new RuntimeException('Database lokasi gagal disiapkan.');
        $backupDirectory=dirname($file).'/.database-backups';
        if (!is_dir($backupDirectory) && !mkdir($backupDirectory,0775,true) && !is_dir($backupDirectory)) throw new RuntimeException('Folder backup database gagal dibuat.');
        $backupFile=$backupDirectory.'/'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'-global-database.js';
        if (!copy($file,$backupFile)) throw new RuntimeException('Backup global-database.js gagal dibuat; perubahan dibatalkan.');
        if (file_put_contents($file,$updated,LOCK_EX) === false) throw new RuntimeException('Database lokasi gagal disimpan.');
        return [$results,$changedCount,basename($backupFile)];
    } finally { flock($lock,LOCK_UN); fclose($lock); }
}

function translateText(string $text, string $target): string
{
    $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=id&tl=' . rawurlencode($target) . '&dt=t&q=' . rawurlencode($text);
    $curl = curl_init($url);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 45, CURLOPT_USERAGENT => 'JakartaTourCMS/1.0']);
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if ($response === false || $status < 200 || $status >= 300) throw new RuntimeException('Layanan terjemahan gagal: ' . ($error ?: 'HTTP ' . $status));
    $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    $translated = implode('', array_map(static fn(array $part): string => (string) ($part[0] ?? ''), (array) ($data[0] ?? [])));
    if ($translated === '') throw new RuntimeException('Hasil terjemahan kosong.');
    return $translated;
}

function copyDirectory(string $source, string $destination): void
{
    if (!is_dir($source)) return;
    if (!is_dir($destination)) mkdir($destination, 0775, true);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $item) {
        $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        if ($item->isDir()) { if (!is_dir($target)) mkdir($target, 0775, true); }
        elseif (!copy($item->getPathname(), $target)) throw new RuntimeException('Dependency gagal disalin.');
    }
}

function findTourDirectory(string $root, string $id): ?string
{
    foreach (new DirectoryIterator($root) as $entry) {
        if ($entry->isDot() || !$entry->isDir() || in_array($entry->getFilename(), ['assets', 'cms', '.git'], true)) continue;
        $candidates = [$entry->getPathname()];
        foreach (new DirectoryIterator($entry->getPathname()) as $child) if (!$child->isDot() && $child->isDir()) $candidates[] = $child->getPathname();
        foreach ($candidates as $candidate) if (cleanId(basename($candidate)) === $id && is_file($candidate . '/pano.xml') && is_file($candidate . '/skin.js')) return $candidate;
    }
    return null;
}

function syncSkin(string $root, string $id, string $templateDirectory = ''): void
{
    $targetDirectory = findTourDirectory($root, $id);
    if ($targetDirectory === null) throw new RuntimeException('Folder tur ' . $id . ' tidak ditemukan.');
    $backup = $targetDirectory . '/.skin-backups';
    if (!is_dir($backup) && !mkdir($backup, 0775, true) && !is_dir($backup)) throw new RuntimeException('Folder backup gagal dibuat untuk ' . $id . '.');
    $stamp = date('Ymd-His');
    $indexFile = $targetDirectory . '/index.html';
    if (is_file($indexFile)) {
        if (!copy($indexFile, $backup . '/' . $stamp . '-index.html')) throw new RuntimeException('Backup index.html gagal untuk ' . $id . '.');
        $html = (string) file_get_contents($indexFile);
        $version = (string) time();
        $html = preg_replace('/src=(["\'])(pano2vr_player\.js|skin\.js)(?:\?[^"\']*)?\1/i', 'src=$1$2?v=' . $version . '$1', $html) ?? $html;
        $html = preg_replace('/pano\.readConfigUrlAsync\((["\'])pano\.xml(?:\?[^"\']*)?\1\)/i', 'pano.readConfigUrlAsync($1pano.xml?v=' . $version . '$1)', $html) ?? $html;
        $assetName = basename(activeProjectAssetBaseRoot());
        $runtimeName = basename(activeProjectAssetsRoot());
        $runtimePrefix = $runtimeName === $assetName ? '' : $runtimeName . '/';
        if (!str_contains($html, '../' . $assetName . '/' . $runtimePrefix . 'main-loader.js')) {
            $tags = '<link rel="stylesheet" href="../' . $assetName . '/' . $runtimePrefix . 'custom.css"/>\n\t\t<script src="../' . $assetName . '/' . $runtimePrefix . 'main-loader.js"></script>';
            $html = preg_replace('/(<script[^>]+src=["\']skin\.js[^>]*>\s*<\/script>)/i', '$1' . "\n\t\t" . $tags, $html, 1, $inserted) ?? $html;
            if (($inserted ?? 0) !== 1) throw new RuntimeException('Bootstrap skin modular gagal dipasang untuk ' . $id . '.');
        }
        if (file_put_contents($indexFile, $html, LOCK_EX) === false) throw new RuntimeException('index.html gagal diperbarui untuk ' . $id . '.');
    }

    $panoFile = $targetDirectory . '/pano.xml';
    if (is_file($panoFile)) {
        if (!copy($panoFile, $backup . '/' . $stamp . '-pano.xml')) throw new RuntimeException('Backup pano.xml gagal untuk ' . $id . '.');
        $xml = (string) file_get_contents($panoFile);
        $xml = preg_replace('/(<tour\b[^>]*\bhassounds=")[01](")/i', '${1}1$2', $xml, 1, $soundCount) ?? $xml;
        if (($soundCount ?? 0) === 0) $xml = preg_replace('/<tour\b/i', '<tour hassounds="1"', $xml, 1) ?? $xml;
        if (!preg_match('/<map\b[^>]*\bid="Map01"[^>]*\/?\s*>/i', $xml)) {
            $map = '  <map mapurltemplate="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" mapmaxzoom="18" type="web" index="0" mapprovider="openstreetmap" mapstyle="streets" title="" googlecustomstylecode="" id="Map01"/>';
            $xml = preg_replace('/\s*<\/tour>\s*$/i', "\n" . $map . "\n</tour>\n", $xml, 1, $mapCount) ?? $xml;
            if (($mapCount ?? 0) !== 1) throw new RuntimeException('Konfigurasi map gagal dipasang untuk ' . $id . '.');
        }
        if (file_put_contents($panoFile, $xml, LOCK_EX) === false) throw new RuntimeException('pano.xml gagal diperbarui untuk ' . $id . '.');
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405, false, 'Metode tidak diizinkan.');
    $payload = json_decode((string) file_get_contents('php://input'), true, 64, JSON_THROW_ON_ERROR);
    if (!hash_equals((string) ($_SESSION['cms_csrf'] ?? ''), (string) ($payload['csrf'] ?? ''))) respond(403, false, 'Sesi CMS tidak valid.');
    $action = (string) ($payload['action'] ?? '');
    $rawIds=$payload['locations']??null;
    if (!is_array($rawIds)) respond(422,false,'Daftar lokasi tidak valid.');
    $ids=[];
    foreach ($rawIds as $rawId) { if (!is_string($rawId)||$rawId===''||cleanId($rawId)!==$rawId) respond(422,false,'ID lokasi tidak valid.'); if (!in_array($rawId,$ids,true)) $ids[]=$rawId; }
    if ($ids === []) respond(422, false, 'Pilih minimal satu lokasi.');
    if (count($ids) > 25) respond(422, false, 'Maksimal 25 lokasi per operasi massal.');
    $root = activeProjectRoot();
    $assetsRoot = activeProjectAssetsRoot();
    $results = [];

    if ($action === 'bulk_edit') {
        $databaseFile=$assetsRoot.'/global-database.js'; $project=activeProject();
        $allowedCategories=array_keys((array)($project['categories']??[]));
        if ($allowedCategories===[] && ($project['id']??'')==='jakarta-output') $allowedCategories=['heritage','maritim','urban'];
        $allowedCategories[]='uncategorized';
        $edit=$payload['changes']??null; if (!is_array($edit)) respond(422,false,'Payload perubahan tidak valid.'); $validated=[];
        foreach (['category','has_drone','drone'] as $field) { if (!isset($edit[$field])||!is_array($edit[$field])) respond(422,false,'Mode field '.$field.' wajib dikirim.'); $mode=$edit[$field]['mode']??null; if (!is_string($mode)||!in_array($mode,['keep','set'],true)) respond(422,false,'Mode field '.$field.' tidak valid.'); $validated[$field]=['mode'=>$mode,'value'=>null]; }
        if ($validated['category']['mode']==='set') { $value=$edit['category']['value']??null; if (!is_string($value)||!in_array($value,$allowedCategories,true)) respond(422,false,'Kategori yang dipilih tidak diperbolehkan.'); $validated['category']['value']=$value; }
        if ($validated['has_drone']['mode']==='set') { if (!array_key_exists('value',$edit['has_drone'])||!is_bool($edit['has_drone']['value'])) respond(422,false,'Status has_drone harus berupa aktif atau nonaktif.'); $validated['has_drone']['value']=$edit['has_drone']['value']; }
        if ($validated['drone']['mode']==='set') { if (!array_key_exists('value',$edit['drone'])||!is_string($edit['drone']['value'])) respond(422,false,'Nilai node drone tidak valid.'); $value=trim($edit['drone']['value']); if (strlen($value)>200||str_contains($value,"\0")||preg_match('/[\r\n]/',$value)) respond(422,false,'Nilai node drone tidak valid.'); $validated['drone']['value']=$value; }
        if (!array_filter($validated,static fn(array $field):bool=>$field['mode']==='set')) respond(422,false,'Pilih minimal satu field untuk diubah.');
        [$results,$changedCount,$backup]=bulkEditDatabase($databaseFile,$ids,$validated);
        $successCount=count(array_filter($results,static fn(array $result):bool=>$result['success']));
        respond(200,true,$changedCount>0?$changedCount.' lokasi diperbarui.':'Tidak ada perubahan yang perlu ditulis.',['results'=>$results,'changed'=>$changedCount,'processed'=>$successCount,'backup'=>$backup,'reload'=>$changedCount>0]);
    } elseif ($action === 'translate') {
        $databaseFile = $assetsRoot . '/global-database.js';
        $database = readDatabase($databaseFile);
        $project = activeProject();
        $sourceId = cleanId((string) ($project['template_tour'] ?? ''));
        foreach ($ids as $id) {
            try {
                if ($id === $sourceId) throw new RuntimeException('Lokasi sumber template tidak boleh diproses sebagai target.');
                if (!isset($database[$id])) throw new RuntimeException('Lokasi tidak ditemukan dalam database.');
                $source = $database[$id]['bahasa']['id'] ?? [];
                $registry = json_decode((string) file_get_contents($assetsRoot . '/languages.json'), true, 32, JSON_THROW_ON_ERROR);
                foreach ($registry as $language => $settings) {
                    if ($language === 'id') continue;
                    $target = (string) $settings['translate'];
                    foreach (['lokasi', 'welcomeText'] as $field) {
                        $text = trim((string) ($source[$field] ?? ''));
                        if ($text !== '') $database[$id]['bahasa'][$language][$field] = translateText($text, $target);
                    }
                }
                $results[] = ['id' => $id, 'success' => true, 'location' => $database[$id]];
            } catch (Throwable $error) { $results[] = ['id' => $id, 'success' => false, 'message' => $error->getMessage()]; }
        }
        saveDatabase($databaseFile, $database);
    } elseif ($action === 'skin') {
        cmsRequireAdmin('Menerapkan skin');
        $skinPackage = activeProjectAssetBaseRoot() . '/jkt';
        if (!is_file($skinPackage . '/jakarta-skin.html') || !is_file($skinPackage . '/jakarta-skin-loader.js')) throw new RuntimeException('Paket skin modular assets/jkt tidak ditemukan.');
        foreach ($ids as $id) {
            try { syncSkin($root, $id); $results[] = ['id' => $id, 'success' => true]; }
            catch (Throwable $error) { $results[] = ['id' => $id, 'success' => false, 'message' => $error->getMessage()]; }
        }
    } else respond(422, false, 'Aksi massal tidak dikenali.');

    $successCount = count(array_filter($results, static fn(array $result): bool => $result['success']));
    respond(200, true, $successCount . ' dari ' . count($results) . ' lokasi berhasil diproses.', ['results' => $results]);
} catch (Throwable $error) {
    respond(500, false, $error->getMessage());
}
