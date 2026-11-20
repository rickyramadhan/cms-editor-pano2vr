# Template Skin

Satu folder = satu template skin. CMS (tab **Template Skin**) membaca folder ini, menampilkan daftar template, lalu menerapkan template yang dipilih ke lokasi di project aktif.

```
skin-templates/
├── jakarta/
│   ├── template.json        # nama, keterangan, pratinjau, pemuat (opsional, tapi disarankan)
│   └── skin.js              # WAJIB — hasil export Pano2VR; satu-satunya berkas yang disalin
└── badung/
    └── ...
```

## Menambah template baru
1. Buat folder baru, nama huruf kecil/angka/`-`/`_` (mis. `bali-utara`).
2. Taruh `skin.js` hasil export Pano2VR di dalamnya. Video, peta, player, dan aset lokasi tidak disimpan sebagai bagian template.
3. Buat `template.json` (simpan sebagai UTF-8; BOM tetap terbaca):
   ```json
   {
     "name": "Bali Utara",
     "description": "Skin bali-utara.ggsk",
     "preview": "/badung/pantai-kuta/",
     "loader": "../assets/template-skin/template-skin-loader.js?v=3"
   }
   ```
   - `preview` — alamat tur untuk pratinjau (opsional, relatif ke `htdocs`).
   - `loader` — pemuat yang harus ada di `index.html` lokasi agar skin tampil lengkap (opsional). Boleh satu string atau daftar (maks. 5, urutan = urutan di `index.html`), mis. `["../assets/template-skin/template-skin-loader.js?v=3", "../assets/x.js"]`. Contoh Badung: video intro & pilihan audio dipasang oleh `../assets/template-skin/template-skin-loader.js`, **bukan** oleh `skin.js`. Hanya jalur relatif ke berkas `.js` di server yang sama yang diterima.
   - `remove` — daftar tag lama yang **dihapus** dari `index.html` saat diterapkan, mis. `["../assets/main-loader.js"]` (jalur yang dulu salah dan 404).
   - `replace` — tag yang **alamatnya diganti**, mis. `{ "../assets/custom.css": "../assets/main-loader/custom.css" }`. Posisi tag tetap; bila alamat baru sudah ada, tag lama cukup dihapus agar tidak ganda. Jenis berkas harus sama (.css → .css).
   - `remove` & `replace` hanya mengenai tag `<script src>` / `<link href>` yang nilainya **persis** sama (versi `?v=` boleh beda); mis. `../assets/main-loader.js` tidak pernah mengenai `../assets/main-loader/main-loader.js`.
   - Uji dulu setiap pemuat di satu lokasi sebelum dimasukkan ke template. Badung memakai `../assets/main-loader/main-loader.js` **versi Badung** (gyro, musik latar, bahasa, data CMS, Video 360; tanpa layar hitam & tanpa berkas `jkt/`). Jangan menyalin `main-loader.js` versi Jakarta ke Badung: ia memasang layar hitam yang hanya dilepas skin Jakarta, sehingga tur menjadi hitam.
4. Buka CMS › Template Skin, pilih template, lalu simpan pilihan. CMS tidak menebak template dari ID project.

## Yang diubah di lokasi saat diterapkan
1. `skin.js` diganti dengan milik template.
2. Tag di `remove` dihapus dan alamat di `replace` diganti (hanya tag yang cocok persis).
3. Untuk setiap pemuat di `loader` yang belum ada di `index.html`: **satu baris** `<script src="…"></script>` disisipkan setelah tag `skin.js` (dan setelah pemuat template yang sudah ada tepat di bawahnya). Tag lain tidak dihapus atau diubah. Pemuat yang sudah ada dilewati (menerapkan ulang tidak membuat tag ganda).

File lama (`skin.js`, dan `index.html` bila disisipi) dicadangkan dulu ke `.skin-backups/<stempel>/` di folder lokasi; bila salah satu langkah gagal, keduanya dikembalikan otomatis. `pano2vr_player.js`, `pano.xml`, peta, tiles, video, aset, dan data lokasi tidak disentuh.

Status di CMS: lokasi dianggap **Sesuai** bila `skin.js` sama dengan template **dan** pemuatnya (bila ada) sudah terpasang; selain itu **Perlu** (dengan keterangan "pemuat skin belum terpasang").

Video intro tetap milik masing-masing lokasi: unggah lewat tab **Video** (`<lokasi>/video/desktop.*` dan `mobile.*`). Tanpa berkas video, pemuat melewati intro dan langsung membuka tur.

Mengubah template di sini **tidak** otomatis mengubah lokasi — terapkan ulang dari CMS.

## Setup opsional (`setup.json`)
Bila template punya `setup.json`, tombol **Setup** (merah muda) muncul di samping **Atur arah** pada editor hotspot (Audit) untuk project yang memakai template itu. Template tanpa file ini tidak punya tombolnya.
```json
{ "label": "Setup", "title": "Keterangan tooltip", "actions": ["cache_bust"] }
```
Aksi yang tersedia:
- `cache_bust` — mengganti versi `pano.xml?ts=…` di `index.html` lokasi supaya browser memuat `pano.xml` terbaru setelah arah pandang/hotspot diubah.

Menambah aksi baru: daftarkan di `SKIN_SETUP_ACTIONS` dan `skinRunSetup()` di `skin-templates.php`. `setup.json` tidak ikut disalin ke lokasi.
