#!/usr/bin/env node
/* optimizer-bridge.js — jembatan Node antara CMS (PHP) dan mesin optimasi virtual tour (optimize-tour.js / optimize-video.js).
 *
 *   node optimizer-bridge.js list <req.json>             → JSON ke stdout: { tours:[{name,dir,status,backup}], ffmpeg:bool, engine:{...} }
 *   node optimizer-bridge.js job  <req.json> <job.json>   → menjalankan pekerjaan di latar belakang; kemajuan ditulis ke job.json
 *
 * req.json: { engine, base, dirs:[...], action:'check'|'optimize'|'restore'|'video', opts:{ sw, autoSkip }, ffmpegDir?, id? }
 * Keamanan: setiap folder harus berada di dalam `base` (akar project) dan dikenali mesin sebagai tur; selain itu ditolak.
 * Mesin tidak diubah; hanya di-require (modul sudah mengekspor optimize/restore/status/listTours dan compressVideos/hasFfmpeg).
 */
'use strict';
const fs = require('fs');
const path = require('path');

const [mode, reqFile, jobFile] = process.argv.slice(2);
const req = JSON.parse(fs.readFileSync(reqFile, 'utf8'));
if (req.ffmpegDir) process.env.PATH = req.ffmpegDir + path.delimiter + process.env.PATH;

function load() {
  const t = path.join(req.engine, 'optimize-tour.js');
  if (!fs.existsSync(t)) throw new Error('optimize-tour.js tidak ditemukan di ' + req.engine);
  const T = require(t);
  let V = null;
  try { V = require(path.join(req.engine, 'optimize-video.js')); } catch (e) { /* video opsional */ }
  return { T, V };
}
const inside = (d) => { const b = path.resolve(req.base), r = path.resolve(d); return r === b || r.startsWith(b + path.sep); };
const HIDE = /^lawang-sewu-(base|prev)$/;   // salinan pembanding benchmark, bukan tur sungguhan (sama dengan GUI asli)

function listAll(T) {
  const out = [];
  const seen = new Set();
  const add = (dir) => {
    dir = path.resolve(dir); if (seen.has(dir) || !inside(dir) || HIDE.test(path.basename(dir))) return; seen.add(dir);
    const st = T.status(dir); if (st === 'bukan') return;
    out.push({ name: path.relative(req.base, dir).replace(/\\/g, '/'), dir, status: st, backup: fs.existsSync(path.join(dir, 'index.html.pre-opt.bak')) });
  };
  for (const t of T.listTours(req.base)) add(t.dir);               // folder tingkat atas (cara mesin asli)
  for (const d of req.dirs || []) add(d);                           // tambahan dari CMS (tur bersarang)
  return out.sort((a, b) => a.name.localeCompare(b.name, 'id', { numeric: true }));
}

if (mode === 'list') {
  try {
    const { T, V } = load();
    process.stdout.write(JSON.stringify({ ok: true, tours: listAll(T), ffmpeg: V ? !!V.hasFfmpeg() : false, hasVideo: !!V, node: process.version }));
  } catch (e) { process.stdout.write(JSON.stringify({ ok: false, error: e.message })); }
  process.exit(0);
}

/* ---------------- pekerjaan latar belakang ---------------- */
const job = { id: req.id || '', state: 'running', action: req.action, pid: process.pid, started: Date.now(), updated: Date.now(), finished: null, total: 0, done: 0, current: '', lines: [], results: [], error: '' };
let writing = false;
function flush() {
  job.updated = Date.now();
  const json = JSON.stringify(job);
  try { fs.writeFileSync(jobFile + '.tmp', json); fs.renameSync(jobFile + '.tmp', jobFile); }
  catch (e) { try { fs.writeFileSync(jobFile, json); } catch (x) { /* dibaca PHP pada tick berikutnya */ } }
}
const log = (l) => { job.lines.push(String(l)); if (job.lines.length > 4000) job.lines.splice(0, job.lines.length - 4000); flush(); };
const beat = setInterval(flush, 2000);
const finish = (state, error) => { job.state = state; job.error = error || ''; job.finished = Date.now(); clearInterval(beat); flush(); process.exit(state === 'done' ? 0 : 1); };
process.on('SIGTERM', () => { log('Dihentikan.'); finish('cancelled'); });
process.on('uncaughtException', (e) => { log('GALAT: ' + e.message); finish('error', e.message); });

(async () => {
  try {
    const { T, V } = load();
    const known = new Map(listAll(T).map((t) => [path.resolve(t.dir), t]));
    const dirs = (req.dirs || []).map((d) => path.resolve(d)).filter((d) => { if (!inside(d) || !known.has(d)) { log('DILEWATI (bukan tur di project ini): ' + d); return false; } return true; });
    job.total = dirs.length; flush();
    if (req.action === 'video') {
      if (!V || !V.hasFfmpeg()) throw new Error('FFmpeg tidak ditemukan. Pasang FFmpeg (winget install Gyan.FFmpeg) atau isi "Folder FFmpeg" di pengaturan Optimasi.');
      for (const d of dirs) {
        job.current = path.basename(d); log('== ' + known.get(d).name);
        try { const r = await V.compressVideos(d, {}, log); job.results.push({ name: known.get(d).name, status: r.some((x) => x.status === 'OK') ? 'OK' : 'TETAP', notes: r.map((x) => `${x.file}: ${x.status}${x.before ? ' ' + (x.before / 1048576).toFixed(1) + 'MB' + (x.after ? ' → ' + (x.after / 1048576).toFixed(1) + 'MB' : '') : ''}`) }); }
        catch (e) { log('GAGAL: ' + e.message); job.results.push({ name: known.get(d).name, status: 'GAGAL', notes: [e.message] }); }
        job.done++; flush();
      }
    } else {
      for (const d of dirs) {
        const name = known.get(d).name; job.current = name; flush();
        let r;
        try { r = req.action === 'restore' ? T.restore(d) : T.optimize(d, { check: req.action === 'check', sw: !(req.opts && req.opts.sw === false), autoSkip: !(req.opts && req.opts.autoSkip === false) }); }
        catch (e) { r = { status: 'GAGAL', notes: [e.message] }; }
        job.results.push({ name, status: r.status, notes: r.notes || [] }); job.done++;
        log(`[${String(r.status).padEnd(8)}] ${name}`);
      }
    }
    log('Selesai.');
    finish('done');
  } catch (e) { log('GAGAL: ' + e.message); finish('error', e.message); }
})();
