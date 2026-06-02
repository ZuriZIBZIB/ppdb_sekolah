# PANDUAN SETUP PPDB SEKOLAH

Panduan lengkap untuk mengsetup dan menjalankan sistem PPDB Sekolah.

## STEP 1: Persiapan

### 1.1 Pastikan Laragon/XAMPP Aktif
```
- Buka Laragon/XAMPP Control Panel
- Start Apache
- Start MySQL
```

### 1.2 Lokasi Project
Project berada di: `C:\laragon\www\ppdb_sekolah_2`

## STEP 2: Import Database

### 2.1 Akses PhpMyAdmin
```
Buka Browser: http://localhost/phpmyadmin
```

### 2.2 Buat Database
```
1. Klik "New" atau "Buat"
2. Nama Database: ppdb_sekolah
3. Collation: utf8mb4_unicode_ci
4. Klik "Create"
```

### 2.3 Import SQL File
```
1. Pilih database: ppdb_sekolah
2. Klik tab "Import"
3. Browse File: ppdb_sekolah.sql
4. Klik "Import"
5. Tunggu hingga berhasil (akan muncul pesan sukses)
```

## STEP 3: Seeding Data Awal

### 3.1 Buka Browser
```
URL: http://localhost/ppdb_sekolah_2/seed.php
```

### 3.2 Verifikasi Output
Output akan menampilkan:
```
=== Seeding Data PPDB ===

1. Creating Admin User...
   ✓ Admin user created successfully
   Email: admin@ppdb.com
   Password: admin123

2. Creating Asal Sekolah (Schools)...
   ✓ 5 schools added

3. Creating Jurusan (Majors)...
   ✓ 5 majors added

4. Creating Fase Pendaftaran (Phases)...
   ✓ 4 phases added

5. Creating Informasi Sekolah (School Info)...
   ✓ School information added

=== Seeding Completed Successfully ===
```

**Jika ada error:**
- Periksa apakah database sudah ter-import dengan benar
- Pastikan database connection di config.php sudah benar
- Reload halaman seed.php

## STEP 4: Konfigurasi (Opsional)

Jika database Anda menggunakan user/password berbeda, edit file `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');           // Ganti dengan user database Anda
define('DB_PASS', '');               // Ganti dengan password database Anda
define('DB_NAME', 'ppdb_sekolah');   // Ganti jika nama database berbeda
```

## STEP 5: Testing Sistem

### 5.1 Login sebagai Admin

**URL:**
```
http://localhost/ppdb_sekolah_2/Login/login.php
```

**Credentials:**
```
Email: admin@ppdb.com
Password: admin123
```

**Hasil:**
Akan masuk ke Dashboard Admin

### 5.2 Registrasi Akun Baru (Calon Siswa)

**URL:**
```
http://localhost/ppdb_sekolah_2/Register/register.php
```

**Isi Form:**
```
Nama Lengkap: [Masukkan nama]
Email: [Email baru, contoh: siswa@test.com]
Password: [Min 6 karakter]
Jenis Kelamin: Pilih
Alamat: [Masukkan alamat]
```

**Klik:** Daftar Sekarang

**Hasil:** Akan redirect ke login page

### 5.3 Login sebagai Siswa Baru

**URL:**
```
http://localhost/ppdb_sekolah_2/Login/login.php
```

**Credentials:**
```
Email: [Email yang baru didaftarkan]
Password: [Password yang didaftarkan]
```

**Hasil:** Akan masuk ke Form Pendaftaran

### 5.4 Isi Form Pendaftaran

**Isi field:**
```
Nama Lengkap: [Nama siswa]
NISN: [Nomor NISN 10 digit]
Jenis Kelamin: Pilih
Tanggal Lahir: [Pilih tanggal]
Alamat: [Alamat lengkap]
No HP: [Nomor telepon]
Asal Sekolah: Pilih dari list
Jurusan Tujuan: Pilih dari list
```

**Klik:** Daftar

**Hasil:** 
- Pesan sukses "Data pendaftaran berhasil disimpan!"
- Data masuk ke database dengan status "belum_verifikasi"

### 5.5 Lihat Status Pendaftaran

**URL:**
```
http://localhost/ppdb_sekolah_2/Formulir/status.php
```

**Hasil:**
- Tampil status terkini pendaftaran
- Menampilkan informasi yang sudah diisi
- Timeline proses penerimaan

### 5.6 Admin Verifikasi Pendaftar

**Login:**
```
Email: admin@ppdb.com
Password: admin123
```

**Akses Verifikasi:**
```
URL: http://localhost/ppdb_sekolah_2/verifikasi.php
```

**Proses:**
1. Klik tombol "Verifikasi" pada pendaftar
2. Dialog akan muncul
3. Ubah status ke salah satu:
   - Terdaftar
   - Lolos Administrasi
   - Lolos Tes
   - Diterima
   - Ditolak
4. Tambahkan catatan (opsional)
5. Klik "Simpan Verifikasi"

**Hasil:**
- Status berubah di database
- Catatan tersimpan
- Siswa dapat melihat update status di halaman status

### 5.7 Admin Dashboard

**URL:**
```
http://localhost/ppdb_sekolah_2/dashboard.php
```

**Menampilkan:**
- Total Pendaftar
- Pendaftar dalam Proses Verifikasi
- Kuota Tersisa per Jurusan
- Tabel Lengkap Semua Pendaftar
- Link ke halaman Verifikasi

## STEP 6: Troubleshooting

### Error: "Connection failed: Connection refused"

**Penyebab:** MySQL server tidak running

**Solusi:**
1. Buka Laragon/XAMPP Control Panel
2. Start MySQL
3. Refresh halaman browser

### Error: "Table 'ppdb_sekolah.users' doesn't exist"

**Penyebab:** Database belum ter-import

**Solusi:**
1. Buka phpmyadmin
2. Pilih database ppdb_sekolah
3. Klik Import
4. Pilih file ppdb_sekolah.sql
5. Klik Import

### Error "Undefined variable" atau "Syntax Error"

**Penyebab:** PHP version lama atau error dalam script

**Solusi:**
1. Pastikan PHP versi 7.4 atau lebih tinggi
2. Periksa error log di browser
3. Cek file config.php sudah benar

### Tidak bisa login dengan admin

**Penyebab:** Admin belum dibuat atau password salah

**Solusi:**
1. Jalankan seed.php dulu
2. Cek database users table apakah ada record admin
3. Login dengan: admin@ppdb.com / admin123

### Data tidak masuk database

**Penyebab:** Koneksi database gagal atau validation error

**Solusi:**
1. Periksa pesan error di halaman
2. Pastikan semua field diisi dengan benar
3. Cek file config.php
4. Lihat error log MySQL

## STRUKTUR ALUR APLIKASI

```
┌─────────────────────┐
│   Calon Siswa      │
└──────────┬──────────┘
           │
           ▼
    ┌─────────────────────────────┐
    │  Register/register.php      │
    │  - Buat akun baru          │
    │  - Simpan ke tabel users   │
    └─────────────────────────────┘
           │
           ▼
    ┌─────────────────────────────┐
    │   Login/login.php          │
    │  - Login dengan email      │
    │  - Session created         │
    └─────────────────────────────┘
           │
           ▼
    ┌─────────────────────────────┐
    │   Formulir/form.php        │
    │  - Isi data pendaftaran    │
    │  - Simpan ke tabel pendaftar
    │  - Status: belum_verifikasi│
    └─────────────────────────────┘
           │
           ▼
    ┌─────────────────────────────┐
    │   Formulir/status.php      │
    │  - Lihat status            │
    │  - Lihat timeline          │
    └─────────────────────────────┘
           │
           ▼
    ┌──────────────────────────────┐
    │  Admin verifikasi.php       │
    │  - Login admin              │
    │  - Verifikasi pendaftar     │
    │  - Update status            │
    │  - Tambah catatan           │
    └──────────────────────────────┘
           │
           ▼
    ┌──────────────────────────────┐
    │  Status Update Siswa        │
    │  - Lihat status terbaru     │
    │  - Lihat catatan admin      │
    └──────────────────────────────┘
```

## FITUR UTAMA YANG SUDAH BERJALAN

✅ **Sistem Registrasi**
   - Form registrasi akun baru
   - Validasi input
   - Password hashing
   - Check duplikasi email

✅ **Sistem Login**
   - Login dengan email & password
   - Session management
   - Auto-redirect based on role

✅ **Form Pendaftaran**
   - Input data siswa lengkap
   - Pilih asal sekolah
   - Pilih jurusan tujuan
   - Edit data yang sudah ada

✅ **Status Tracking**
   - Lihat status pendaftaran
   - Lihat timeline alur
   - Lihat catatan admin

✅ **Admin Dashboard**
   - Statistik pendaftar
   - Kuota tersisa
   - Tabel semua pendaftar

✅ **Verifikasi Admin**
   - Ubah status pendaftar
   - Tambah catatan
   - Update database

✅ **Security**
   - Password hashing (bcrypt)
   - Prepared statements (prevent SQL injection)
   - Session-based authentication
   - Role-based access control

## NEXT STEPS (Optional Enhancements)

Fitur tambahan yang bisa ditambahkan:

- [ ] Upload dokumen/file
- [ ] Email notification
- [ ] Penilaian/scoring system
- [ ] Rekap laporan Excel/PDF
- [ ] Notifikasi SMS
- [ ] Multi-language support
- [ ] Two-factor authentication
- [ ] Backup database otomatis

---

**Selesai! Sistem PPDB sudah siap digunakan.**

Jika ada pertanyaan atau masalah, silakan cek kembali langkah-langkah di atas atau review README.md
