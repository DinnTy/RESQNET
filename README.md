# RESQNET
### Resilience & Emergency Support Queue Network

**RESQNET** merupakan aplikasi berbasis web yang dikembangkan untuk mendukung pengelolaan pelaporan bencana, permintaan bantuan, persediaan logistik, serta distribusi bantuan secara terintegrasi.

Aplikasi ini bertujuan membantu masyarakat dan petugas dalam mengelola informasi kebencanaan serta mendukung proses penyaluran bantuan agar lebih terstruktur, terpantau, dan terkoordinasi.

---

## 1. Fitur Utama

### Pelaporan Bencana
- Masyarakat dapat membuat laporan kejadian bencana.
- Menambahkan deskripsi, tingkat keparahan, waktu kejadian, dan bukti foto.
- Menentukan lokasi kejadian melalui koordinat.
- Melihat perkembangan status laporan.

### Verifikasi Laporan
- Petugas dapat memeriksa laporan yang masuk.
- Petugas dapat menerima atau menolak laporan.
- Masyarakat mendapatkan notifikasi perubahan status laporan.

### Peta GIS
- Menampilkan lokasi laporan bencana.
- Menampilkan lokasi gudang bantuan.
- Menyediakan informasi lokasi melalui peta interaktif.
- Mendukung visualisasi rute menuju lokasi.

### Permintaan Bantuan
- Masyarakat dapat mengajukan permintaan bantuan berdasarkan laporan yang telah terverifikasi.
- Menentukan jenis dan jumlah kebutuhan bantuan.
- Memantau perkembangan permintaan bantuan.

### Manajemen Logistik
- Mengelola data barang bantuan.
- Mengelola kategori barang.
- Mengelola gudang bantuan.
- Memantau jumlah stok, stok yang dialokasikan, dan stok tersedia.

### Distribusi Bantuan
- Mengalokasikan bantuan berdasarkan kebutuhan.
- Mengelola proses distribusi dari gudang menuju lokasi tujuan.
- Memantau status distribusi.
- Memperbarui informasi penyaluran bantuan.

### Notifikasi
- Memberikan informasi pembaruan status laporan.
- Memberikan pemberitahuan terkait proses permintaan dan distribusi bantuan.

---

## 2. Hak Akses Pengguna

RESQNET memiliki tiga jenis pengguna:

| Pengguna | Hak Akses |
|---|---|
| Masyarakat | Membuat laporan, mengajukan bantuan, dan memantau status laporan serta permintaan bantuan. |
| Petugas | Memverifikasi laporan, mengelola logistik, dan mengelola distribusi bantuan. |
| Admin | Mengelola kategori bencana dan administrasi sistem. |

---

## 3. Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| PHP 8.1 | Bahasa pemrograman backend |
| Laravel 10.50.3 | Framework aplikasi |
| PostgreSQL | Sistem manajemen basis data |
| HTML5 | Struktur halaman web |
| CSS3 | Pengaturan tampilan |
| JavaScript | Interaksi halaman |
| Bootstrap 5 | Framework antarmuka |
| Leaflet.js | Peta interaktif |
| OpenStreetMap | Sumber data peta |
| OSRM | Layanan perhitungan rute |

---

## 4. Persyaratan Sistem

Sebelum menjalankan RESQNET, pastikan perangkat telah memiliki:

- PHP versi 8.1 atau lebih baru yang kompatibel dengan project.
- Composer.
- Node.js dan NPM.
- PostgreSQL.
- Git.
- Browser modern seperti Google Chrome atau Mozilla Firefox.

Project ini dikembangkan menggunakan Laravel 10.50.3 dan PHP 8.1.10.

---

## 5. Instalasi Project

### A. Clone Repository

Unduh project dari repository GitHub:

```bash
git clone URL_REPOSITORY
```

Masuk ke direktori project:

```bash
cd resqnet
```

Ganti `URL_REPOSITORY` dengan alamat repository RESQNET yang sebenarnya.

### B. Install Dependency

Install dependency PHP:

```bash
composer install
```

Install dependency frontend:

```bash
npm install
```

### C. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`.

Pada Windows Command Prompt:

```cmd
copy .env.example .env
```

Pada macOS atau Linux:

```bash
cp .env.example .env
```

Buat application key:

```bash
php artisan key:generate
```

### D. Konfigurasi Database

1. Buka PostgreSQL melalui pgAdmin.
2. Buat database baru untuk RESQNET.
3. Buka file `.env`.
4. Sesuaikan konfigurasi database:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=resqnet_db
DB_USERNAME=postgres
DB_PASSWORD=
```

Sesuaikan nama database, username, dan password dengan konfigurasi PostgreSQL pada perangkat masing-masing.

### E. Jalankan Migration

Setelah konfigurasi database selesai, jalankan:

```bash
php artisan migrate
```

Perintah tersebut digunakan untuk membuat struktur tabel berdasarkan file migration yang tersedia.

### F. Hubungkan Penyimpanan File

Jalankan perintah:

```bash
php artisan storage:link
```

Perintah ini membuat symbolic link agar file yang disimpan pada disk publik dapat diakses melalui direktori publik aplikasi.

---

## 6. Cara Menjalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Kemudian buka terminal baru pada direktori project dan jalankan:

```bash
npm run dev
```

Buka browser dan akses:

```text
http://127.0.0.1:8000
```

Aplikasi RESQNET dapat digunakan melalui browser setelah server berhasil dijalankan.

Untuk membuat asset frontend versi produksi, jalankan:

```bash
npm run build
```

---

## 7. Dokumentasi Aplikasi

Dokumentasi aplikasi mencakup:

- Tampilan halaman utama.
- Halaman login dan registrasi.
- Dashboard masyarakat.
- Dashboard petugas dan admin.
- Formulir pelaporan bencana.
- Halaman peta GIS.
- Pengelolaan permintaan bantuan.
- Pengelolaan stok dan gudang.
- Proses distribusi bantuan.

Tangkapan layar dan dokumentasi penggunaan dapat ditambahkan pada bagian ini.

---

## 8. Keamanan dan Konfigurasi

Beberapa hal yang perlu diperhatikan:

- File `.env` tidak disertakan dalam repository.
- Gunakan `.env.example` sebagai template konfigurasi.
- Jangan membagikan password database atau kredensial layanan.
- Atur `APP_DEBUG=false` ketika aplikasi dijalankan di lingkungan produksi.
- Gunakan konfigurasi database dan email yang sesuai dengan lingkungan deployment.

---

## 9. Pengembangan Selanjutnya

Pengembangan RESQNET dapat dilanjutkan melalui beberapa peningkatan, seperti:

- Pengembangan fitur pemantauan kondisi bencana secara lebih luas.
- Peningkatan akurasi rekomendasi distribusi bantuan.
- Pengembangan sistem notifikasi secara real-time.
- Pengembangan tampilan yang lebih responsif untuk perangkat mobile.
- Integrasi dengan sumber informasi kebencanaan resmi.

---

## 10. Informasi Project

**Nama Aplikasi:** RESQNET  
**Kepanjangan:** Resilience & Emergency Support Queue Network  
**Platform:** Web Application  
**Framework:** Laravel 10  
**Database:** PostgreSQL  
**Tujuan:** Mendukung pengelolaan pelaporan bencana dan distribusi bantuan secara terintegrasi.

---

**RESQNET — Resilience & Emergency Support Queue Network**

*Supporting coordinated disaster reporting and emergency assistance management.*