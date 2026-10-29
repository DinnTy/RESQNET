# RESQNET — Catatan Audit & Implementasi Desain "Siaga"

Pembaruan: 29 Oktober 2026  
Persiapan UINIC 8.0 2026

Dokumen ini menjelaskan kondisi project, bug yang ditemukan, file yang diubah, cara memasang pembaruan, dan cara mengujinya.

---

## 1. Ringkasan Audit Project

| Bagian | Kondisi |
|---|---|
| Framework | Laravel 10.50, PHP ≥ 8.1, PostgreSQL (`resqnet_db`) |
| Controller | 12 controller (+1 baru: `HomeController`) |
| Model | 13 model dengan relasi lengkap (laporan → lokasi/kategori/user, permintaan → item, distribusi → detail/aktivitas, gudang → stok) |
| Migration | 18 migration lama (+1 baru pengaman `username`) |
| Role | `admin`, `petugas`, `masyarakat` melalui `RoleMiddleware` (`role:admin,petugas`) |
| GIS | Leaflet + OpenStreetMap, rute Leaflet Routing Machine (OSRM), jarak Haversine di `MapController` dan `AidRequestController` |
| Front-end | Bootstrap 5 dari CDN, CSS ditulis per halaman. Vite tidak dipakai (`resources/css/app.css` kosong) |

**Fitur yang sudah berjalan dan tidak diubah:**

Login dan registrasi, laporan bencana beserta foto bukti, verifikasi laporan beserta notifikasi, permintaan bantuan multi-barang, rekomendasi gudang berdasarkan jarak dan stok, distribusi multi-barang dan multi-gudang dengan reservasi stok, alur status distribusi (dialokasikan → dikirim → diterima / dibatalkan), CRUD kategori bencana, CRUD gudang, barang, dan stok, serta peta GIS.

**Bagian yang belum tersedia di kode:**

- Halaman kelola petugas/pengguna untuk admin. Akun petugas saat ini harus dibuat melalui pgAdmin atau Laravel Tinker.
- `resources/views/disaster-reports/index.blade.php` tidak digunakan oleh controller mana pun dan merupakan sisa file lama.

## 2. Bug yang Ditemukan dan Diperbaiki

Bug ditemukan melalui pemeriksaan project dan pengujian menggunakan PostgreSQL serta data uji.

| No. | Bug | Dampak | Perbaikan |
|---|---|---|---|
| 1 | `reports/show.blade.php` memakai `route('reports.updateStatus')`, padahal nama route adalah `reports.update-status` | Error 500 saat admin/petugas membuka detail laporan sehingga verifikasi tidak bisa dilakukan | Nama route pada view dibetulkan |
| 2 | `aid-requests/show.blade.php` memakai `route('aid-requests.updateStatus')` | Error 500 pada halaman detail permintaan bantuan | Nama route pada view dibetulkan |
| 3 | `distributions/show.blade.php` memakai `route('distributions.updateStatus')` | Error 500 saat form status distribusi ditampilkan | Nama route pada view dibetulkan |
| 4 | Migration tidak membuat kolom `users.username`, padahal login dan registrasi menggunakannya | Pada instalasi baru atau deployment, registrasi dapat mengalami error | Migration baru yang hanya menambahkan kolom jika belum tersedia |
| 5 | Notifikasi memakai tautan `<a href>` (GET) ke route `PATCH notifications.read` | Error 405 saat notifikasi diklik | Diganti dengan form `PATCH` dan tombol "Buka" menuju URL notifikasi |
| 6 | Pagination memakai markup Tailwind bawaan Laravel, sedangkan project menggunakan Bootstrap | Tombol halaman tampil tidak sesuai | Menggunakan `Paginator::useBootstrapFive()` |
| 7 | `reports/index` menampilkan pesan sukses dua kali, yaitu di layout dan halaman | Pesan sukses muncul ganda | Pesan cukup ditampilkan melalui layout |

Nama route, controller, model, tabel, dan kolom tidak diganti.

## 3. File yang Diubah dan Ditambahkan

### A. File Baru

| File | Fungsi |
|---|---|
| `public/css/resqnet.css` | Sistem desain Siaga: warna, huruf, komponen, layout aplikasi, dan penyesuaian Bootstrap untuk halaman CRUD |
| `public/js/resqnet.js` | Motion bersama: transisi antarhalaman, kursor, tombol magnetik, odometer angka, pita hazard, menu HP, dan jam WIB |
| `public/js/resqnet-home.js` | Animasi landing page: preloader, hero sinematik, dan kartu sorotan |
| `app/Http/Controllers/HomeController.php` | Landing page yang mengambil angka ringkasan dari database tanpa menampilkan data pribadi |
| `resources/views/auth/_card.blade.php` | Kartu masuk dan daftar dengan panel geser |
| `database/migrations/2026_10_29_000001_add_username_to_users_table.php` | Migration pengaman untuk kolom `username` |
| `database/seeders/DemoDataSeeder.php` | Data demo opsional yang tidak dijalankan secara otomatis |

### B. File yang Ditulis Ulang (Tampilan Saja, Data dan Form Tetap)

| File | Catatan |
|---|---|
| `resources/views/layouts/app.blade.php` | Sidebar berdasarkan role, topbar, notifikasi, dan pesan flash. `@yield('content')`, `@stack('styles')`, serta `@stack('scripts')` tetap dipertahankan |
| `resources/views/home.blade.php` | Landing page Siaga. Tombol menggunakan `route()` dan menyesuaikan status login |
| `resources/views/auth/login.blade.php` dan `register.blade.php` | Menggunakan `_card`; form tetap mengarah ke `login.process` dan `register.store` dengan nama field yang sama |
| `resources/views/dashboard.blade.php` | Menggunakan variabel dari `DashboardController` |
| `resources/views/dashboard-masyarakat.blade.php` | Menggunakan variabel dari `DashboardController` |
| `resources/views/reports/index.blade.php` | Menggunakan data paginator `$reports` yang sama |

### C. File yang Diubah Sebagian

| File | Perubahan |
|---|---|
| `routes/web.php` | Route `/` diarahkan ke `HomeController@index`, dengan nama route tetap `home` |
| `app/Providers/AppServiceProvider.php` | Pengaturan bahasa tanggal Indonesia dan pagination Bootstrap |
| `map/index`, `reports/create`, `distributions/show` | Perubahan isi `<style>` serta warna marker/rute; logika Leaflet dan OSRM tidak disentuh |
| `reports/show`, `aid-requests/show`, `distributions/show` | Perbaikan nama route untuk bug nomor 1–3 |
| `distributions/index`, `warehouses/create`, `warehouses/edit`, `aid-stocks/create` | Penambahan `@section('title')` |

Halaman CRUD lain di luar daftar tersebut tidak diubah secara langsung. Tampilannya mengikuti tema melalui `public/css/resqnet.css`.

## 4. Cara Memasang di Laragon (Windows)

### 1. Cadangkan project dan database

Sebelum memasang pembaruan, buat salinan folder project dan database.

- Salin folder project lama sebagai cadangan.
- Jika folder `C:\laragon\www\resqnet_backup` sudah tersedia, jangan menimpanya. Gunakan nama cadangan lain jika perlu membuat salinan tambahan.
- Cadangkan database melalui pgAdmin: klik kanan database `resqnet_db`, lalu pilih **Backup**.

### 2. Siapkan file project

Ekstrak ZIP pembaruan ke folder:

`C:\laragon\www\resqnet`

Pastikan file project berada pada folder yang benar dan tidak berada di dalam folder bertingkat yang tidak diperlukan.

### 3. Periksa konfigurasi `.env`

Pastikan file `.env` berisi pengaturan database yang sesuai dengan konfigurasi PostgreSQL lokal.

Jangan mengunggah file `.env` ke repository publik karena dapat berisi informasi rahasia seperti password database dan konfigurasi aplikasi.

### 4. Jalankan migration dan perintah pembaruan

Buka Terminal Laragon, kemudian jalankan:

```bash
cd C:\laragon\www\resqnet
php artisan migrate
php artisan optimize:clear
php artisan storage:link
```

Keterangan:

- `php artisan migrate` menjalankan migration yang belum diterapkan.
- Migration `add_username_to_users_table` dirancang untuk menambahkan kolom `username` jika kolom tersebut belum tersedia.
- `php artisan optimize:clear` membersihkan cache konfigurasi, route, dan view.
- `php artisan storage:link` membuat symbolic link agar foto bukti laporan dapat diakses melalui aplikasi. Jika muncul pesan bahwa link sudah tersedia, periksa terlebih dahulu kondisi link tersebut.

### 5. Data demo (opsional)

Perintah berikut hanya digunakan untuk database kosong atau lingkungan pengujian:

```bash
php artisan db:seed --class=DemoDataSeeder
```

Seeder demo bersifat opsional dan tidak berjalan otomatis.

**Catatan Keamanan Akun Demo:**
Akun demo hanya digunakan untuk keperluan pengujian pada lingkungan lokal. Sebelum aplikasi dipublikasikan, seluruh kredensial default wajib diganti dengan kredensial yang aman. Jangan menggunakan akun atau password bawaan pada lingkungan produksi.
### 6. Jalankan aplikasi

Buka aplikasi melalui:

`http://resqnet.test`

Atau jalankan:

```bash
php artisan serve
```

Kemudian buka:

`http://127.0.0.1:8000`

Setelah pembaruan, tekan **Ctrl + F5** pada browser agar CSS dan JavaScript terbaru dimuat.

> Font, Bootstrap, Bootstrap Icons, Chart.js, dan Leaflet dimuat dari CDN. Pastikan perangkat terhubung ke internet saat demonstrasi.

## 5. Daftar Uji Manual

| Uji | Langkah | Hasil yang Diharapkan |
|---|---|---|
| Intro | Buka `/` di tab baru | Tulisan RESQNET putih polos (#FFFFFF), progress 0–100%, lalu hero tampil |
| Landing | Tunggu atau tekan "Lewati" | Angka pada mockup dan kartu sorotan sesuai dengan data ringkasan dashboard |
| Registrasi | Buka `/register`, lalu isi form | Kembali ke halaman masuk dengan pesan sukses |
| Validasi | Daftar menggunakan email yang tidak valid | Panel daftar tetap terbuka dan pesan error ditampilkan |
| Login | Masuk menggunakan username atau email | Masuk ke dashboard sesuai role |
| Masyarakat | Buat laporan bencana dan pilih titik pada peta | Laporan berstatus Menunggu Verifikasi |
| Admin | Buka detail laporan lalu pilih Verifikasi | Status berubah dan pelapor menerima notifikasi |
| Notifikasi | Klik ikon lonceng lalu pilih "Tandai dibaca" | Jumlah notifikasi berkurang tanpa error 405 |
| Permintaan | Ajukan bantuan multi-barang lalu admin memprosesnya | Status permintaan berubah sesuai proses |
| Distribusi | Buat distribusi multi-gudang, ubah status menjadi Dikirim lalu Diterima | Stok terreservasi, kemudian berkurang sesuai proses; permintaan diperbarui menjadi Terpenuhi jika seluruh kebutuhan sudah terpenuhi |
| Peta GIS | Admin/petugas membuka Peta GIS | Marker, popup rekomendasi, dan rute tampil |
| Hak akses | Masyarakat mencoba membuka `/warehouses` | Akses ditolak dengan respons 403 |
| Tampilan HP | Buka aplikasi pada layar di bawah 960px | Menu dapat dibuka melalui tombol ☰ dan tabel dapat digeser |

**Catatan hasil pengujian:**

Jumlah pemeriksaan otomatis yang tercatat selama pengembangan adalah 63 pemeriksaan halaman dan 32 uji alur form. Angka tersebut perlu disertai hasil pengujian atau catatan pengujian yang dapat diperiksa kembali sebagai bukti.

Tampilan peta membutuhkan koneksi internet karena menggunakan layanan peta dan routing eksternal.

## 6. Rekomendasi Berikutnya (Belum Dikerjakan)

1. **Zona waktu:** `config/app.php` masih menggunakan `'timezone' => 'UTC'`, sehingga waktu yang ditampilkan dapat berbeda 7 jam dari WIB. Pengaturan ke `Asia/Jakarta` perlu dipertimbangkan sebelum pengumpulan data asli. Perhatikan juga penyesuaian data waktu yang sudah tersimpan.
2. **Manajemen akun:** menambahkan halaman kelola akun petugas/pengguna untuk admin.
3. **Aset lokal:** menyimpan salinan Leaflet, Chart.js, dan Bootstrap di `public/vendor` agar tampilan demo tidak sepenuhnya bergantung pada CDN.
4. **Konfigurasi deployment:** menggunakan `APP_DEBUG=false` dan `APP_ENV=production` pada lingkungan produksi.

---

**RESQNET — Resilience & Emergency Support Queue Network**  
Sistem informasi pelaporan bencana dan koordinasi bantuan berbasis GIS.