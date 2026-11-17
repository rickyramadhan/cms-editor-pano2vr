# Template Skin

Satu folder = satu template skin. CMS (tab **Template Skin**) membaca folder ini, menampilkan daftar template, lalu menerapkan template yang dipilih ke lokasi di project aktif.

```
skin-templates/
├── jakarta/
│   ├── template.json        # nama & keterangan (opsional, tapi disarankan)
│   ├── skin.js              # WAJIB — hasil export Pano2VR
│   ├── pano2vr_player.js    # opsional — ikut disalin bila ada
│   ├── images/              # opsional — gambar yang dipakai skin.js
│   └── 3rdparty/            # opsional — library (mis. leaflet untuk peta)
└── badung/
    └── ...
```

## Menambah template baru
1. Buat folder baru, nama huruf kecil/angka/`-`/`_` (mis. `bali-utara`).
2. Taruh `skin.js` hasil export Pano2VR di dalamnya (beserta `pano2vr_player.js` dari export yang sama).
3. Bila skin memakai peta, salin `3rdparty/leaflet` (bisa dari template lain).
4. Buat `template.json`:
   ```json
   { "name": "Bali Utara", "description": "Skin bali-utara.ggsk", "preview": "" }
   ```
   `preview` = alamat tur untuk pratinjau (opsional, relatif ke `htdocs`, mis. `/badung/pantai-kuta/`).
5. Buka CMS › Template Skin; template langsung muncul.

## Yang disalin ke lokasi saat diterapkan
`skin.js`, `pano2vr_player.js`, isi `images/` dan `3rdparty/` dari template. File lama dicadangkan dulu ke `.skin-backups/` di folder lokasi; bila penyalinan gagal, file dikembalikan otomatis. Panorama, tiles, video, dan data lokasi tidak disentuh.

Mengubah template di sini **tidak** otomatis mengubah lokasi — terapkan ulang dari CMS.
