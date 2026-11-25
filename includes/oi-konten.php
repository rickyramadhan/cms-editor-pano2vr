<?php
declare(strict_types=1);

/**
 * Daftar Konten Objek Info (tab Object Informasi › Daftar Konten): memeriksa, per lokasi, apakah halaman webview tiap objek punya data.
 *
 * Alurnya: pin di tur membuka  webview/objinfo/<info-id>  (webview/objinfo.html). Halaman itu membaca  webview/json/objinfo/<info-id>.json
 * ({gambar, translations:{id:{judul,deskripsi}, en:…}} atau bentuk datar {gambar, judul, deskripsi}; lihat komentar di objinfo.html).
 * Bila datanya tidak ada atau kosong, halaman menampilkan layar "sedang pemeliharaan". Daftar ini hanya membaca berkas itu.
 */

/** Nilai satu bahasa dari bidang yang bisa berupa teks atau objek {id,en,zh,ar}. */
function oiKontenPick(mixed $value, string $lang): string
{
    if (is_string($value)) return trim($value);
    if (is_array($value)) {
        $v = $value[$lang] ?? $value['id'] ?? (reset($value) ?: '');
        return is_string($v) ? trim($v) : '';
    }
    return '';
}

/**
 * Baca dan normalkan data satu objek.
 * @param array<int,string> $langCodes
 * @return ?array{gambar:string,texts:array<string,array{title:string,desc:string}>}
 */
function oiKontenRead(string $file, array $langCodes): ?array
{
    if (!is_file($file)) return null;
    $raw = json_decode((string) @file_get_contents($file), true);
    if (!is_array($raw)) return null;
    $texts = [];
    foreach ($langCodes as $code) {
        $t = is_array($raw['translations'] ?? null) ? ($raw['translations'][$code] ?? null) : null;
        if (is_array($t)) {
            $title = trim((string) ($t['judul'] ?? $t['title'] ?? $t['name'] ?? ''));
            $desc = trim((string) ($t['deskripsi'] ?? $t['desc'] ?? $t['description'] ?? ''));
        } elseif (!isset($raw['translations'])) {
            $title = oiKontenPick($raw['judul'] ?? $raw['title'] ?? $raw['name'] ?? '', $code);
            $desc = oiKontenPick($raw['deskripsi'] ?? $raw['desc'] ?? $raw['description'] ?? '', $code);
            // bentuk datar tanpa objek bahasa: teks yang sama dipakai semua bahasa; hanya hitung terisi untuk bahasa yang benar-benar ada
            if (!is_array($raw['judul'] ?? $raw['title'] ?? null) && $code !== 'id') { $title = ''; $desc = ''; }
        } else {
            $title = ''; $desc = '';
        }
        if ($title !== '' && $desc !== '') $texts[$code] = ['title' => $title, 'desc' => $desc];
    }
    return ['gambar' => trim((string) ($raw['gambar'] ?? $raw['image'] ?? $raw['img'] ?? '')), 'texts' => $texts];
}

/**
 * @param array<int,array<string,mixed>> $records     record oi-database satu lokasi
 * @param array<int,array<string,mixed>> $panoramas   panorama lokasi (nodeId, value, name)
 * @param array<string,string>           $languages   kode => label (urutan tampil)
 * @param string                         $webviewRoot folder webview di disk (…/badung/webview)
 * @return array{items:array<int,array<string,mixed>>,summary:array<string,int>}
 */
function oiKontenReport(array $records, array $panoramas, array $languages, string $webviewRoot): array
{
    $sceneNames = [];
    foreach ($panoramas as $p) {
        $name = trim((string) ($p['name'] ?? '')) !== '' ? trim((string) $p['name']) : (string) ($p['value'] ?? '');
        foreach ([(string) ($p['nodeId'] ?? ''), (string) ($p['value'] ?? '')] as $key) {
            if ($key !== '') $sceneNames[strtolower($key)] = $name;
        }
    }
    $langCodes = array_keys($languages);
    $items = [];
    $summary = ['total' => 0, 'aktif' => 0, 'nonaktif' => 0, 'lengkap' => 0, 'sebagian' => 0, 'kosong' => 0];

    foreach ($records as $record) {
        if (!is_array($record)) continue;
        $active = ($record['isActive'] ?? true) !== false;
        $infoId = oiCleanInfoId((string) ($record['infoID'] ?? ''));
        $title = oiKontenLabelName($record);
        $data = $infoId !== '' ? oiKontenRead($webviewRoot . '/json/objinfo/' . $infoId . '.json', $langCodes) : null;

        $langs = [];
        $texts = [];
        $missing = [];
        foreach ($langCodes as $code) {
            $ok = isset($data['texts'][$code]);
            $langs[$code] = $ok;
            if ($ok) $texts[$code] = $data['texts'][$code];
            elseif ($data !== null) $missing[] = strtoupper($code);
        }

        $gambar = $data['gambar'] ?? '';
        $photo = ['set' => $gambar !== '', 'exists' => false, 'url' => ''];
        if ($gambar !== '') {
            if (preg_match('#^(https?:)?//|^data:#i', $gambar)) { $photo['exists'] = true; $photo['url'] = $gambar; }
            elseif (!str_contains($gambar, '..') && is_file($webviewRoot . '/' . ltrim($gambar, '/'))) { $photo['exists'] = true; $photo['url'] = projectUrl('webview/' . ltrim($gambar, '/')); }
        }

        $notes = [];
        if ($infoId === '') {
            $status = 'kosong';
            $notes[] = 'Info ID belum diisi; tur menampilkan layar pemeliharaan';
        } elseif ($data === null) {
            $status = 'kosong';
            $notes[] = 'Data belum ada di webview/json/objinfo/' . $infoId . '.json; tur menampilkan layar pemeliharaan';
        } elseif (!isset($texts['id'])) {
            $status = 'kosong';
            $notes[] = 'Judul atau deskripsi bahasa Indonesia kosong; tur menampilkan layar pemeliharaan bila bahasa lain juga kosong';
        } else {
            if ($missing !== []) $notes[] = 'Terjemahan belum ada: ' . implode(', ', $missing) . ' (memakai bahasa Indonesia)';
            if (!$photo['set']) $notes[] = 'Foto belum diisi (kartu tampil satu kolom)';
            elseif (!$photo['exists']) $notes[] = 'Berkas foto tidak ditemukan: ' . $gambar;
            $status = ($missing === [] && $photo['exists']) ? 'lengkap' : 'sebagian';
        }

        $nodeKey = strtolower(trim((string) ($record['nodeID'] ?? ''), '{} '));
        $summary['total']++;
        $summary[$active ? 'aktif' : 'nonaktif']++;
        if ($active) $summary[$status]++;

        $items[] = [
            'route_key' => (string) ($record['route_key'] ?? ''),
            'title' => $title,
            'active' => $active,
            'status' => $status,
            'infoId' => $infoId,
            'pageUrl' => $infoId !== '' ? projectUrl('webview/objinfo/' . $infoId) . '?lang=id&tutup=0' : '',
            'target' => (string) ($record['targetID'] ?? ''),
            'scene' => $sceneNames[$nodeKey] ?? ($nodeKey !== '' ? $nodeKey : ''),
            'langs' => $langs,
            'texts' => $texts,
            'photo' => $photo,
            'notes' => $notes,
        ];
    }

    // urutan: aktif dulu, lalu yang perlu perhatian di atas yang sudah lengkap
    $rank = ['kosong' => 0, 'sebagian' => 1, 'lengkap' => 2];
    usort($items, static function (array $a, array $b) use ($rank): int {
        return [$a['active'] ? 0 : 1, $rank[$a['status']] ?? 9, $a['title']] <=> [$b['active'] ? 0 : 1, $rank[$b['status']] ?? 9, $b['title']];
    });

    return ['items' => $items, 'summary' => $summary];
}

/** Nama objek dari label papan ("Klik untuk Informasi </br>Nama"). */
function oiKontenLabelName(array $record): string
{
    $label = $record['labelTeks'] ?? '';
    $text = is_array($label) ? (string) ($label['id'] ?? '') : (string) $label;
    $parts = array_values(array_filter(array_map('trim', preg_split('#</?br\s*/?>#i', $text) ?: []), static fn($p) => $p !== ''));
    if (count($parts) > 1) return implode(' ', array_slice($parts, 1));
    return trim((string) ($record['title'] ?? '')) !== '' ? trim((string) $record['title']) : ($parts[0] ?? '');
}

/** Info ID = bagian alamat webview/objinfo/<info-id>: huruf kecil, angka, dan strip (underscore dan spasi menjadi strip). */
function oiCleanInfoId(string $value): string
{
    $value = mb_strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}
