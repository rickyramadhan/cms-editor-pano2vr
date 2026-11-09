# Login & hak akses CMS

Semua halaman dan endpoint CMS sekarang wajib login. Gerbang dipasang di `project-manager.php`, yang dimuat oleh setiap endpoint. Endpoint tanpa sesi menjawab JSON `401`, sedangkan halaman dialihkan ke `login.php`.

## Mulai pertama kali
1. Buka CMS dari komputer server, misalnya `http://localhost/cms-virtual-tour/`. Halaman **Buat admin pertama** akan tampil.
   - Bila CMS hanya bisa diakses dari jarak jauh: set variabel lingkungan `CMS_SETUP_TOKEN`, lalu isi token itu di formulir.
   - Alternatif: jalankan `php includes/cli-user.php add <nama> admin` di terminal server.
2. Masuk, lalu buka **Pengguna** (menu kiri bawah) untuk menambah akun tim.

## Peran
| Peran | Boleh |
|---|---|
| **Admin** | Semua: pengguna, tambah/hapus project, pasang asset, Konfigurasi Global, Custom CSS, Template Skin, tambah/hapus bahasa, hapus lokasi. Di Smart Hub: Pengaturan project, Terapkan/Lepas, origin API, dan gaya HUD |
| **Editor** | Data lokasi, terjemahan, media, voice, Object Informasi, audit. Di Smart Hub: Bangun paket, aksi, menu, dan feed. Origin API dan gaya HUD tetap memakai nilai admin |

**Akses project** diatur per akun, misalnya tim Badung hanya dicentang `badung`. Admin selalu bisa membuka semua project.

## Keamanan yang sudah ada
- **Kata sandi:** hash `password_hash`, minimal 10 karakter dan harus memuat huruf serta angka.
- **Sesi:** cookie `HttpOnly` dan `SameSite=Lax` (`Secure` bila HTTPS). ID sesi diganti setiap login, dan sesi berakhir setelah 8 jam tidak aktif.
- **Batas percobaan:** 5 kali salah per akun, atau 20 kali per IP, membuat login terkunci 15 menit.
- **Status akun:** akun yang dinonaktifkan langsung kehilangan akses. Sistem selalu menyisakan minimal satu admin aktif.
- **Log audit:** login, logout, perubahan pengguna, dan penolakan akses dicatat di `data/audit.log`.
- **Keluar:** hanya lewat POST bertoken. Halaman CMS tidak bisa dibingkai situs lain (`X-Frame-Options`).

## Data & server
- **Lokasi data:** akun disimpan di `data/users.json`. Untuk produksi, set `CMS_DATA_DIR` ke folder **di luar web root**.
- **Apache:** `data/`, `includes/`, `views/`, `templates/`, `projects.json`, dan `cms-settings.json` sudah diblokir lewat `.htaccess`.
- **nginx:** tambahkan aturan yang setara:
  ```nginx
  location ~ ^/cms-virtual-tour/(data|includes|views|templates)/ { deny all; }
  location ~ ^/cms-virtual-tour/(projects|cms-settings)\.json$ { deny all; }
  ```
- **`php -S` (dev):** server ini tidak membaca `.htaccess`, jadi pemblokiran di atas tidak berlaku.
- **Lupa sandi admin:** jalankan `php includes/cli-user.php passwd <nama>` di server.

## Belum ada
Login dua langkah (2FA), SSO, reset sandi lewat email, dan kunci sunting bersamaan (dua orang menyunting lokasi yang sama tetap bisa saling menimpa).
