# PPDB Sekolah - Sistem Online Penerimaan Peserta Didik Baru

Sistem web aplikasi untuk mengelola penerimaan peserta didik baru (PPDB) secara online di SMKN 4 Palembang.

## Fitur Utama

✅ **Sistem Registrasi & Login** - Calon siswa dapat mendaftar dan login dengan email
✅ **Form Pendaftaran Lengkap** - Input data pribadi, NISN, asal sekolah, jurusan tujuan
✅ **Dashboard Admin** - Melihat statistik pendaftar, kuota jurusan, status pendaftaran
✅ **Verifikasi Pendaftar** - Admin dapat memverifikasi dan mengubah status pendaftar
✅ **Database Terpadu** - Semua data tersimpan di MySQL database
✅ **Responsive Design** - Interface dengan CSS untuk tampilan yang baik

## Persyaratan Sistem

- **Web Server**: Apache (Laragon/XAMPP)
- **Database**: MySQL 8.0 atau lebih tinggi
- **PHP**: 7.4 atau lebih tinggi
- **Browser**: Chrome, Firefox, Safari, atau Edge terbaru

## Instalasi & Setup

### 1. Import Database
```
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Buat database baru dengan nama: ppdb_sekolah
3. Import file: ppdb_sekolah.sql ke database tersebut
```

### 2. Seeding Data Awal
```
1. Buka browser dan akses: http://localhost/ppdb_sekolah_2/seed.php
2. Akan membuat admin user, jurusan, asal sekolah, dan fase pendaftaran
```

### 3. Konfigurasi Database
Edit file `config.php` dan sesuaikan dengan konfigurasi database Anda:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ppdb_sekolah');
```

## Cara Menggunakan

### Untuk Calon Siswa

1. **Registrasi Akun Baru**
   - Akses: http://localhost/ppdb_sekolah_2/Register/register.php
   - Isi form dengan nama, email, password, jenis kelamin, dan alamat
   - Klik "Daftar Sekarang"

2. **Login ke Sistem**
   - Akses: http://localhost/ppdb_sekolah_2/Login/login.php
   - Masukkan email dan password
   - Akan diarahkan ke form pendaftaran

3. **Isi Form Pendaftaran**
   - Lengkapi data: nama, NISN, tanggal lahir, no HP
   - Pilih asal sekolah dan jurusan tujuan
   - Klik "Daftar" untuk menyimpan data
   - Data akan masuk ke database dengan status "belum_verifikasi"

### Untuk Admin

1. **Login sebagai Admin**
   - Email: `admin@ppdb.com`
   - Password: `admin123`
   - Akses: http://localhost/ppdb_sekolah_2/Login/login.php

2. **Dashboard Admin**
   - Melihat total pendaftar
   - Melihat jumlah yang masih dalam verifikasi
   - Melihat kuota tersisa per jurusan
   - List lengkap semua pendaftar

3. **Verifikasi Pendaftar**
   - Akses: http://localhost/ppdb_sekolah_2/verifikasi.php
   - Klik tombol "Verifikasi" pada setiap pendaftar
   - Ubah status menjadi:
     - **Terdaftar** - Data diterima
     - **Lolos Administrasi** - Lulus tahap administrasi
     - **Lolos Tes** - Lulus tahap tes
     - **Diterima** - Diterima sebagai siswa
     - **Ditolak** - Ditolak
   - Tambahkan catatan jika diperlukan
   - Klik "Simpan Verifikasi"

## Struktur File

```
ppdb_sekolah_2/
├── config.php                 # Konfigurasi database
├── index.php                  # Halaman utama (auto redirect)
├── dashboard.php              # Dashboard admin
├── verifikasi.php             # Halaman verifikasi pendaftar
├── logout.php                 # Logout user
├── seed.php                   # Seeding data awal
├── ppdb_sekolah.sql          # Database schema
├── Beranda/
│   ├── beranda.html          # Halaman beranda
│   └── beranda.css           # CSS beranda
├── Login/
│   ├── login.php             # Form login (backend)
│   └── login.css             # CSS login
├── Register/
│   ├── register.php          # Form registrasi (backend)
│   └── register.css          # CSS registrasi
├── Formulir/
│   ├── form.php              # Form pendaftaran (backend)
│   └── form.css              # CSS form
├── Dashboard/
│   └── dashboard.css         # CSS dashboard
└── Gambar/                    # Folder untuk gambar/assets
```

## Alur Data

### Registrasi Calon Siswa
1. Calon siswa mengisi form registrasi
2. Data disimpan ke tabel `users`
3. User diarahkan ke halaman login

### Login & Pendaftaran
1. User login dengan email dan password
2. Session dibuat dan disimpan
3. User diarahkan ke form pendaftaran
4. Form pendaftaran diisi dan disimpan ke tabel `pendaftar`
5. Data tersimpan dengan status "belum_verifikasi"

### Verifikasi Admin
1. Admin login dan akses halaman verifikasi
2. Admin melihat list pendaftar
3. Admin klik "Verifikasi" untuk mengubah status
4. Status diperbarui ke database
5. Calon siswa dapat melihat status terbaru mereka

## Database Tables

### users
- id, username, email, password, nama_lengkap, jenis_kelamin, alamat, role, status, created_at, updated_at

### pendaftar
- id, user_id, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, alamat, no_hp, asal_sekolah_id, jurusan_tujuan_id, status_pendaftaran, nilai_tes, catatan_panitia, tanggal_daftar, tanggal_verifikasi, diverifikasi_oleh, updated_at

### asal_sekolah
- id, nama_sekolah, alamat, kota, kecamatan, created_at

### jurusan
- id, nama_jurusan, deskripsi, kuota, kuota_tersisa, created_at, updated_at

### fase_pendaftaran
- id, nomor_fase, nama_fase, deskripsi, tanggal_mulai, tanggal_selesai, status, created_at, updated_at

## Keamanan

✅ Password di-hash menggunakan bcrypt
✅ Input validation untuk semua form
✅ Session management untuk autentikasi
✅ SQL injection prevention menggunakan prepared statements
✅ Role-based access control (Admin vs Calon Siswa)

## Troubleshooting

**Error: Connection failed**
- Pastikan MySQL server running
- Cek username dan password di config.php
- Cek nama database sudah benar

**Error: Table doesn't exist**
- Import ppdb_sekolah.sql ke database
- Pastikan database name sesuai di config.php

**Tidak bisa login**
- Pastikan sudah menjalankan seed.php untuk membuat admin
- Cek email dan password (admin@ppdb.com / admin123)

**Data tidak masuk database**
- Cek koneksi database di config.php
- Periksa pesan error yang ditampilkan di form
- Cek permission folder untuk write

## Support

Untuk bantuan atau laporan bug, silakan hubungi admin sistem.

---
**Last Updated**: Juni 2026
**Version**: 1.0
