# CMS Editor Pano2VR

CMS web (PHP, tanpa database) untuk mengelola banyak virtual tour hasil export **Pano2VR** dalam satu dashboard. Satu *project* = satu wilayah/kumpulan tur; skin, bahasa, media, dan konten dikelola sekali lalu diterapkan ke semua lokasi.

## Fitur
- **Project & lokasi**: deteksi tur otomatis (folder berisi `pano.xml` + `skin.js`), dashboard kelengkapan, kategori, pencarian.
- **Data lokasi & bahasa**: nama dan teks sambutan per bahasa, manajemen bahasa, Translation Center, terjemah otomatis.
- **Skin template**: terapkan satu skin ke semua lokasi (dengan cadangan `.skin-backups`).
- **Media**: video mobile/desktop, video 360 (HLS), voice over (ElevenLabs), optimizer gambar WebP, audit `pano.xml`.
- **Smart Hub**: bangun paket konten per lokasi, isi konten lewat formulir/CSV (Tourism Guide, fakta, kalender, dll.), atur aksi tombol, menu, sumber data, identitas (logo), dan **front-end kustom** dari developer lain. Membutuhkan folder **Smart Kit** (repo/folder terpisah).
- **Optimasi Loading**: menjalankan mesin optimasi loading tur (`optimize-tour.js`) dari CMS, dengan progres, hentikan, dan undo. Lihat [OPTIMASI.md](OPTIMASI.md).
- **Login & peran**: admin/editor, akses per project, pembatasan percobaan login, log audit. Lihat [LOGIN.md](LOGIN.md).

## Kebutuhan
- PHP 8.1+ (disarankan 8.2) dengan `curl`, `mbstring`, `fileinfo`, `dom`; Apache (XAMPP) atau server lain yang menjalankan PHP.
- Opsional: Node.js 14+ (Optimasi Loading), FFmpeg (kompres video), Python 3 + Pillow (Smart Hub › Bangun paket), ekstensi `zip` PHP (hanya untuk fitur "zip lokasi").

## Pasang
1. Letakkan folder ini di web root, mis. `C:/xampp/htdocs/cms-virtual-tour/`.
2. Salin `cms-settings.sample.json` menjadi `cms-settings.json` dan sesuaikan `projects_root` (folder induk semua project/tur).
3. Buka `http://localhost/cms-virtual-tour/` dari komputer server. Halaman **Buat admin pertama** muncul (hanya dari localhost, atau dengan variabel lingkungan `CMS_SETUP_TOKEN`).
4. Tambahkan project di halaman **Project** (folder berisi tur Pano2VR). Untuk project baru, pilih *Pakai assets yang sudah ada* atau terapkan template assets dari project acuan.
5. Lokasi akun dan data runtime: folder `data/` (otomatis dibuat, diblokir dari web, tidak di-commit). Untuk produksi set `CMS_DATA_DIR` ke folder **di luar web root**.

## Rahasia & konfigurasi (tidak ada di repo)
| Apa | Di mana |
|---|---|
| Akun & log audit | `data/users.json`, `data/audit.log` (jangan di-commit) |
| Daftar project | `projects.json` (lihat `projects.sample.json`) |
| Pengaturan mesin | `cms-settings.json` (lihat `cms-settings.sample.json`) |
| Kunci ElevenLabs (voice over) | variabel lingkungan `ELEVENLABS_API_KEY`, atau `data/secrets.json` → `{"elevenlabs_api_key":"..."}` |

## Keamanan
- Semua halaman dan endpoint wajib login; tindakan yang mengubah banyak lokasi, skin, atau menjalankan program (Optimasi, Smart Kit, unggah front-end) khusus admin.
- `.htaccess` memblokir `data/`, `includes/`, `views/`, `tools/`. **Di nginx** tambahkan aturan setara (lihat LOGIN.md); server dev `php -S` tidak membaca `.htaccess`.
- Jangan menaruh kunci API di kode. Bila kunci pernah terlanjur ter-commit di tempat lain, putar (rotate) kunci itu.

## Status
Dikembangkan untuk pipeline virtual tour GoVirtual. Belum ada: login dua langkah, kunci sunting bersamaan, pengujian otomatis.
