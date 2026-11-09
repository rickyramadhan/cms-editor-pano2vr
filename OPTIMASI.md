# Menu Optimasi Loading (CMS)

Menjalankan mesin optimasi virtual tour (`optimize-tour.js`, `optimize-video.js`, yang sebelumnya dibuka lewat `optimize-tour.bat`) langsung dari CMS. Mesinnya sendiri **tidak diubah**; CMS memanggilnya lewat `tools/optimizer-bridge.js`.

## Memakai
1. Daftarkan folder induk tur sebagai project (mis. `C:/xampp/htdocs/semarang`) di halaman **Project** → *Tambah project baru*. Project tanpa assets CMS tetap bisa dibuka: klik tombol **⚡ Optimasi** pada kartunya, atau menu kiri **Media & Tools › Optimasi Loading**.
2. Centang lokasi (atau **Pilih yang belum**), atur opsi (service worker, lewati video di jaringan lambat).
3. **Cek saja** (tidak menulis apa pun) → **Optimasi yang dipilih** → (opsional) **Kompres video** → **Kembalikan (undo)** bila perlu.
4. Kemajuan tampil langsung (log + bar). Pekerjaan berjalan di latar belakang: halaman boleh ditutup dan dibuka lagi; **Hentikan** tersedia selama berjalan (berkas sementara ffmpeg dibersihkan; video asli tidak disentuh).

## Syarat & pengaturan
- Node.js 14+ di server (terdeteksi otomatis; bila CMS dijalankan sebagai layanan yang tidak melihat PATH Anda, isi jalur di **Pengaturan mesin**).
- FFmpeg + ffprobe untuk *Kompres video* (opsional; bisa diisi folder FFmpeg di Pengaturan).
- Mesin dicari berurutan: pengaturan CMS → `cms-virtual-tour/tools/optimizer/` → akar project → `<htdocs>/semarang`. Tombol **Salin mesin ke CMS** menyalin kedua berkas mesin ke `tools/optimizer/` agar CMS mandiri dari folder sumber (berguna di server tanpa folder semarang; perbarui salinan bila mesin asli berubah).
- Hanya **admin** yang dapat menjalankan, menghentikan, dan mengubah pengaturan (alat ini mengubah `index.html` dan `skin.js` tur). Editor hanya melihat status.

## Yang perlu diperhatikan
- Mesin hanya untuk tur ber-skin GoVirtual dengan video teaser (`ggVideoSource` di `skin.js`). Tur lain berstatus GAGAL dan tidak ada file yang diubah.
- **Kembalikan (undo)** menimpa `index.html` dan `skin.js` dengan backup sebelum optimasi: perubahan sesudahnya (Smart Kit, skin template) ikut hilang dan perlu diterapkan ulang. Jalankan ulang optimasi setelah export ulang Pano2VR atau "Terapkan skin".
- Upload ke server: `index.html`, `skin.js`, `sw.js` (+ video baru bila dikompres). Jangan upload `*.pre-opt.bak` dan `media/_original/`.
- Satu pekerjaan per project pada satu waktu; maksimal 300 lokasi per pekerjaan. Folder di luar akar project ditolak (divalidasi di PHP dan di bridge).
- Folder `lawang-sewu-base` dan `lawang-sewu-prev` (salinan pembanding benchmark) disembunyikan, sama seperti GUI asli.

## Berkas
`optimizer.php` (backend) · `tools/optimizer-bridge.js` (jembatan Node) · `views/optimizer.php`, `assets/js/optimizer.js` (antarmuka) · data pekerjaan sementara di `data/optimizer-jobs/` (dihapus otomatis setelah 3 hari).
