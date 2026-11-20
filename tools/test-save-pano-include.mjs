// Regresi HTTP terisolasi: jalankan source endpoint asli dengan tur sintetis di folder sementara.
// Bootstrap dan project-manager diganti stub; autentikasi produksi tidak digunakan atau diuji.
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { once } from 'node:events';
import { mkdtemp, mkdir, readFile, readdir, rm, writeFile } from 'node:fs/promises';
import { createServer } from 'node:net';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const cmsRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const php = process.env.PHP_BINARY || 'D:/xampp/php/php.exe';
const originalTarget = '-132.6/-0.8/108.2';
const savedTarget = '-48.30/65.00/140.00';
const xml = `<?xml version="1.0" encoding="UTF-8"?>
<tour><panorama id="node1"><userdata title="Asal"/><hotspots>
<hotspot id="Point01" title="Warung uji" url="{node2}" target="${originalTarget}"/>
</hotspots></panorama><panorama id="node2"><userdata title="Tujuan"/></panorama></tour>`;
const html = `<script src="pano2vr_player.js?ts=1"></script>
<script src="skin.js?ts=1"></script>
<script>pano.setQueryParameter("ts=1");pano.readConfigUrlAsync("pano.xml?ts=1");</script>`;
const skin = '// Skin sintetis; tidak ada player atau aset tur pengguna.\n';

const bootstrap = `<?php
declare(strict_types=1);
// Stub konteks pengujian, bukan sesi atau kredensial CMS produksi.
$_SESSION = ['cms_csrf' => 'token-uji-terisolasi'];
function cmsJsonPayload(): array { return json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR); }
function cmsAssertCsrf(string $token): void {
    if (!hash_equals($_SESSION['cms_csrf'], $token)) throw new RuntimeException('CSRF tidak valid.');
}
function cmsJsonResponse(array $payload, int $status = 200): never {
    http_response_code($status); header('Content-Type: application/json'); echo json_encode($payload); exit;
}
function cmsCleanKey(string $value): string { return strtolower(preg_replace('/[^a-z0-9_-]/i', '', $value)); }
function cmsIsAdmin(): bool { return true; }
`;
const projectManager = `<?php
declare(strict_types=1);
function cmsBaseDirectory(): string { return __DIR__; }
function activeProjectRoot(): string { return __DIR__ . '/project'; }
function activeProject(): array {
    return ['id' => 'uji', 'name' => 'Project uji', 'path' => activeProjectRoot(),
        'smartkit' => ['wilayah' => 'uji', 'path' => __DIR__ . '/kit-tidak-terpasang', 'url' => '/kit-uji/']];
}
function projectAssetsDirectory(array $project): string { return $project['path'] . '/assets'; }
`;

const endpoint = await readFile(path.join(cmsRoot, 'save-pano.php'), 'utf8');
const hub = await readFile(path.join(cmsRoot, 'smart-hub.php'), 'utf8');
const guard = /^\s*if \(!defined\('SMART_HUB_INCLUDE_ONLY'\)\) define\('SMART_HUB_INCLUDE_ONLY', true\);\r?\n/gm;
assert.equal([...endpoint.matchAll(guard)].length, 1, 'Guard endpoint yang diuji harus ada tepat sekali.');
const legacy = endpoint.replace(guard, '\n');
const fixtureRoot = await mkdtemp(path.join(tmpdir(), 'cms-save-pano-include-'));
let server;
let serverLog = '';

async function makeCase(name, source) {
  const caseRoot = path.join(fixtureRoot, name);
  const tour = path.join(caseRoot, 'project', 'uji');
  await mkdir(path.join(caseRoot, 'includes'), { recursive: true });
  await mkdir(tour, { recursive: true });
  await Promise.all([
    writeFile(path.join(caseRoot, 'save-pano.php'), source),
    writeFile(path.join(caseRoot, 'smart-hub.php'), hub),
    writeFile(path.join(caseRoot, 'includes', 'bootstrap.php'), bootstrap),
    writeFile(path.join(caseRoot, 'project-manager.php'), projectManager),
    writeFile(path.join(tour, 'pano.xml'), xml),
    writeFile(path.join(tour, 'index.html'), html),
    writeFile(path.join(tour, 'skin.js'), skin),
  ]);
  return tour;
}

async function unusedPort() {
  const socket = createServer();
  socket.listen(0, '127.0.0.1');
  await once(socket, 'listening');
  const port = socket.address().port;
  await new Promise((resolve, reject) => socket.close(error => error ? reject(error) : resolve()));
  return port;
}

async function unchanged(tour) {
  assert.equal(await readFile(path.join(tour, 'pano.xml'), 'utf8'), xml);
  assert.equal(await readFile(path.join(tour, 'index.html'), 'utf8'), html);
  assert.equal(await readFile(path.join(tour, 'skin.js'), 'utf8'), skin);
  assert.deepEqual((await readdir(tour)).sort(), ['index.html', 'pano.xml', 'skin.js']);
}

try {
  const oldTour = await makeCase('sebelum', legacy);
  const fixedTour = await makeCase('sesudah', endpoint);
  const invalidTour = await makeCase('csrf-invalid', endpoint);
  const port = await unusedPort();
  const origin = `http://127.0.0.1:${port}`;
  server = spawn(php, ['-S', `127.0.0.1:${port}`, '-t', fixtureRoot], { windowsHide: true, stdio: ['ignore', 'pipe', 'pipe'] });
  const exited = once(server, 'exit');
  let spawnError;
  server.on('error', error => { spawnError = error; });
  server.stdout.on('data', chunk => { serverLog += chunk; });
  server.stderr.on('data', chunk => { serverLog += chunk; });
  let ready = false;
  for (let attempt = 0; attempt < 50; attempt++) {
    if (spawnError) throw spawnError;
    if (server.exitCode !== null) throw new Error(`Server PHP berhenti: ${serverLog}`);
    try { await fetch(origin, { signal: AbortSignal.timeout(500) }); ready = true; break; } catch {}
    await new Promise(resolve => setTimeout(resolve, 100));
  }
  assert.ok(ready, 'Server pengujian harus siap.');
  const post = async (name, csrf = 'token-uji-terisolasi') => {
    const response = await fetch(`${origin}/${name}/save-pano.php`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, signal: AbortSignal.timeout(5000),
      body: JSON.stringify({ csrf, id_lokasi: 'uji', hotspots: [
        { nodeId: 'node1', id: 'Point01', title: 'Warung uji', url: '{node2}', target: savedTarget },
      ] }),
    });
    const raw = await response.text();
    try { return { status: response.status, data: JSON.parse(raw) }; }
    catch { throw new Error(`Respons bukan JSON: ${raw}`); }
  };

  const before = await post('sebelum');
  assert.equal(before.status, 200);
  assert.equal(before.data.success, true);
  assert.ok(before.data.state, 'Versi lama keluar dari pengendali state Smart Hub.');
  assert.equal(before.data.message, undefined);
  await unchanged(oldTour);
  console.log('LULUS sebelum perbaikan: HTTP 200 success=true + state, message kosong; XML/ts tetap lama, backup tidak dibuat.');

  const after = await post('sesudah');
  assert.equal(after.status, 200);
  assert.equal(after.data.success, true);
  assert.match(after.data.message, /berhasil disimpan/);
  assert.equal(after.data.state, undefined);
  const savedXml = await readFile(path.join(fixedTour, 'pano.xml'), 'utf8');
  assert.ok(savedXml.includes(`target="${savedTarget}"`));
  const savedHtml = await readFile(path.join(fixedTour, 'index.html'), 'utf8');
  const versions = [...savedHtml.matchAll(/ts=(\d+)/g)].map(match => match[1]);
  assert.equal(versions.length, 4);
  assert.ok(versions.every(value => value !== '1' && value === versions[0]));
  const panoBackups = await readdir(path.join(fixedTour, '.media-backups'));
  const indexBackups = await readdir(path.join(fixedTour, '.skin-backups'));
  assert.equal(panoBackups.length, 1);
  assert.equal(indexBackups.length, 1);
  assert.equal(await readFile(path.join(fixedTour, '.media-backups', panoBackups[0]), 'utf8'), xml);
  assert.equal(await readFile(path.join(fixedTour, '.skin-backups', indexBackups[0]), 'utf8'), html);
  assert.equal(await readFile(path.join(fixedTour, 'skin.js'), 'utf8'), skin);
  console.log(`LULUS sesudah perbaikan: target=${savedTarget}; pesan simpan benar; 4 ts diperbarui; backup XML/index terverifikasi; skin tetap.`);

  const invalid = await post('csrf-invalid', 'token-salah');
  assert.equal(invalid.status, 403);
  assert.equal(invalid.data.success, false);
  await unchanged(invalidTour);
  console.log('LULUS CSRF invalid: HTTP 403, XML/index/skin tetap, backup tidak dibuat.');
  server.kill();
  await exited;
  server = null;
} catch (error) {
  console.error(serverLog);
  throw error;
} finally {
  if (server && server.exitCode === null) {
    const exited = once(server, 'exit');
    server.kill();
    await exited;
  }
  // Hapus hanya direktori unik milik pengujian, bukan folder sumber atau tur pengguna.
  assert.equal(path.dirname(path.resolve(fixtureRoot)), path.resolve(tmpdir()));
  assert.ok(path.basename(fixtureRoot).startsWith('cms-save-pano-include-'));
  await rm(fixtureRoot, { recursive: true, force: true });
}
console.log('LULUS: server PHP dihentikan dan fixture sementara dibersihkan.');
