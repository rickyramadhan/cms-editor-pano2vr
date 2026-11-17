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
   - `loader` — pemuat yang harus ada di `index.html` lokasi agar skin tampil lengkap (opsional). Contoh Badung: video intro & pilihan audio dipasang oleh `../assets/template-skin/template-skin-loader.js`, **bukan** oleh `skin.js`. Hanya jalur relatif ke berkas `.js` di server yang sama yang diterima.
4. Buka CMS › Template Skin, pilih template, lalu simpan pilihan. CMS tidak menebak template dari ID project.

## Yang diubah di lokasi saat diterapkan
1. `skin.js` diganti dengan milik template.
2. Bila template punya `loader` dan `index.html` lokasi belum memuatnya: **satu baris** `<script src="…loader…"></script>` disisipkan tepat setelah tag `skin.js`. Selain baris itu, isi `index.html` tidak berubah. Bila sudah ada, `index.html` tidak disentuh (menerapkan ulang tidak membuat tag ganda).

File lama (`skin.js`, dan `index.html` bila disisipi) dicadangkan dulu ke `.skin-backups/<stempel>/` di folder lokasi; bila salah satu langkah gagal, keduanya dikembalikan otomatis. `pano2vr_player.js`, `pano.xml`, peta, tiles, video, aset, dan data lokasi tidak disentuh.

Status di CMS: lokasi dianggap **Sesuai** bila `skin.js` sama dengan template **dan** pemuatnya (bila ada) sudah terpasang; selain itu **Perlu** (dengan keterangan "pemuat skin belum terpasang").

Video intro tetap milik masing-masing lokasi: unggah lewat tab **Video** (`<lokasi>/video/desktop.*` dan `mobile.*`). Tanpa berkas video, pemuat melewati intro dan langsung membuka tur.

Mengubah template di sini **tidak** otomatis mengubah lokasi — terapkan ulang dari CMS.
