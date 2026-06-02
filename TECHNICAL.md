# DOKUMENTASI TEKNIS PPDB SEKOLAH

## Ringkasan

Sistem PPDB Sekolah adalah aplikasi web untuk mengelola Penerimaan Peserta Didik Baru (PPDB) secara online. Sistem ini dibangun dengan:
- **Frontend**: HTML + CSS (tanpa JavaScript, hanya form HTML standar)
- **Backend**: PHP dengan MySQLi prepared statements
- **Database**: MySQL 8.0 dengan 11 tabel terstruktur
- **Security**: Password hashing, prepared statements, session management

## FILE STRUCTURE

```
ppdb_sekolah_2/
│
├── 📄 README.md                  # Dokumentasi umum
├── 📄 SETUP.md                   # Panduan setup lengkap
├── 📄 TECHNICAL.md               # File ini
├── 📄 config.php                 # Konfigurasi database
├── 📄 index.php                  # Entry point (auto-redirect)
├── 📄 dashboard.php              # Admin dashboard
├── 📄 verifikasi.php             # Admin verifikasi pendaftar
├── 📄 logout.php                 # Logout handler
├── 📄 seed.php                   # Database seeding
├── 📄 ppdb_sekolah.sql           # Database schema
│
├── 📁 Beranda/
│   ├── beranda.html             # Halaman beranda (info sekolah)
│   └── beranda.css              # CSS beranda
│
├── 📁 Login/
│   ├── login.php                # Form login (dengan backend)
│   └── login.css                # CSS login
│
├── 📁 Register/
│   ├── register.php             # Form registrasi (dengan backend)
│   └── register.css             # CSS registrasi
│
├── 📁 Formulir/
│   ├── form.php                 # Form pendaftaran (dengan backend)
│   ├── form.css                 # CSS form
│   └── status.php               # Status pendaftaran siswa
│
├── 📁 Dashboard/
│   └── dashboard.css            # CSS dashboard
│
└── 📁 Gambar/
    └── [assets folder]          # Tempat menyimpan gambar
```

## DAFTAR FILE PHP & FUNGSIONALITAS

### 1. **config.php**
**Fungsi:** Konfigurasi database dan session management

**Konten:**
```php
- DB_HOST, DB_USER, DB_PASS, DB_NAME (konfigurasi MySQL)
- mysqli connection
- Session initialization
```

**Digunakan oleh:** Semua file PHP

---

### 2. **index.php** 
**Fungsi:** Entry point aplikasi (redirect otomatis)

**Logika:**
```
Jika user sudah login:
  - Role admin → redirect ke dashboard.php
  - Role calon_siswa → redirect ke Formulir/form.php
Jika belum login:
  - Redirect ke Login/login.php
```

**URL:** http://localhost/ppdb_sekolah_2/

---

### 3. **Register/register.php**
**Fungsi:** Form registrasi akun baru untuk calon siswa

**Features:**
- Form HTML dengan method POST
- Validasi input (required, email format, password min 6 karakter)
- Check duplikasi email
- Password hashing dengan `password_hash()`
- Generate username otomatis dari email
- Insert ke tabel `users` dengan role `calon_siswa`
- Auto-redirect ke login setelah sukses

**Database Operations:**
```sql
INSERT INTO users (username, email, password, nama_lengkap, jenis_kelamin, alamat, role, status)
VALUES (...)
```

**Error Handling:**
- Tampil pesan error jika ada validasi gagal
- Tampil pesan sukses jika registrasi berhasil
- Populate ulang form dengan data yang sudah diisi (kecuali password)

**URL:** http://localhost/ppdb_sekolah_2/Register/register.php

---

### 4. **Login/login.php**
**Fungsi:** Form login untuk semua user

**Features:**
- Form HTML dengan method POST
- Login dengan email + password
- Verify password dengan `password_verify()`
- Create session jika login sukses
- Auto-redirect based on role:
  - Admin → dashboard.php
  - Calon siswa → Formulir/form.php
- Validasi status user (hanya bisa login jika status = 'aktif')

**Session Variables yang Dibuat:**
```php
$_SESSION['user_id']   // ID user
$_SESSION['username']  // Username
$_SESSION['email']     // Email
$_SESSION['role']      // Role (admin atau calon_siswa)
```

**Error Message:**
- "Email atau password salah!" - Jika email/password tidak cocok
- "Semua field harus diisi!" - Jika ada field kosong

**URL:** http://localhost/ppdb_sekolah_2/Login/login.php

---

### 5. **Formulir/form.php**
**Fungsi:** Form pendaftaran data siswa baru

**Features:**
- Require login (redirect ke login jika belum)
- Tampil form dengan field:
  - Nama Lengkap (text)
  - NISN (number)
  - Jenis Kelamin (select)
  - Tanggal Lahir (date)
  - Alamat (textarea)
  - No HP (tel)
  - Asal Sekolah (select dari database)
  - Jurusan Tujuan (select dari database)
- Jika data sudah ada, form di-populate dengan data lama (edit mode)
- Jika data baru, INSERT baru ke tabel `pendaftar`
- Jika data lama, UPDATE tabel `pendaftar`

**Database Operations:**

**INSERT (Pendaftaran baru):**
```sql
INSERT INTO pendaftar (user_id, nisn, nama_lengkap, jenis_kelamin, 
                      tanggal_lahir, alamat, no_hp, asal_sekolah_id, 
                      jurusan_tujuan_id, status_pendaftaran, tanggal_daftar)
VALUES (...)
```

**UPDATE (Edit data):**
```sql
UPDATE pendaftar SET nisn=?, nama_lengkap=?, ... WHERE user_id=?
```

**Features:**
- Dinamis load asal sekolah dari tabel `asal_sekolah`
- Dinamis load jurusan dari tabel `jurusan` (hanya yang masih punya kuota)
- Validasi semua field required
- Tampil pesan error/success
- Link ke status.php untuk lihat status
- Link logout

**URL:** http://localhost/ppdb_sekolah_2/Formulir/form.php

---

### 6. **Formulir/status.php**
**Fungsi:** Halaman status pendaftaran siswa

**Features:**
- Require login
- Tampil info akun user
- Tampil status pendaftaran lengkap
- Tampil semua data yang sudah diisi
- Timeline visualisasi proses penerimaan
- Status indicator dengan warna berbeda:
  - **Belum Diverifikasi** = Kuning (#ffc107)
  - **Terdaftar** = Biru (#17a2b8)
  - **Lolos Administrasi** = Hijau (#20c997)
  - **Lolos Tes** = Hijau gelap (#28a745)
  - **Diterima** = Hijau gelap (#28a745)
  - **Ditolak** = Merah (#dc3545)

**Database Query:**
```sql
SELECT p.*, a.nama_sekolah, j.nama_jurusan 
FROM pendaftar p 
LEFT JOIN asal_sekolah a ON p.asal_sekolah_id = a.id 
LEFT JOIN jurusan j ON p.jurusan_tujuan_id = j.id 
WHERE p.user_id = ?
```

**URL:** http://localhost/ppdb_sekolah_2/Formulir/status.php

---

### 7. **dashboard.php**
**Fungsi:** Admin dashboard dengan statistik PPDB

**Features:**
- Require login sebagai admin
- Tampil statistik:
  - Total Pendaftar
  - Proses Verifikasi (status = belum_verifikasi)
  - Kuota Tersisa (sum dari semua jurusan)
- Tampil progress bar verifikasi
- Tampil tabel lengkap semua pendaftar dengan:
  - Nama
  - Jenis Kelamin
  - Asal Sekolah
  - Jurusan Tujuan
  - Status
- Link ke verifikasi.php
- Link logout

**Database Queries:**
```sql
SELECT COUNT(*) FROM pendaftar                          -- Total pendaftar
SELECT COUNT(*) FROM pendaftar WHERE status_pendaftaran='belum_verifikasi'  -- Pending
SELECT SUM(kuota_tersisa) FROM jurusan                 -- Sisa kuota
SELECT p.*, a.nama_sekolah, j.nama_jurusan 
FROM pendaftar p 
LEFT JOIN asal_sekolah a 
LEFT JOIN jurusan j
```

**URL:** http://localhost/ppdb_sekolah_2/dashboard.php

---

### 8. **verifikasi.php**
**Fungsi:** Halaman admin untuk verifikasi dan ubah status pendaftar

**Features:**
- Require login sebagai admin
- Tampil tabel semua pendaftar
- Modal dialog untuk verifikasi
- Ubah status ke:
  - Terdaftar
  - Lolos Administrasi
  - Lolos Tes
  - Diterima
  - Ditolak
- Tambah catatan/keterangan
- Form submit update ke database

**Database Operations:**

**SELECT:**
```sql
SELECT p.id, p.nama_lengkap, p.nisn, p.jenis_kelamin, p.tanggal_lahir, 
       p.no_hp, p.alamat, p.status_pendaftaran, p.catatan_panitia, 
       a.nama_sekolah, j.nama_jurusan, p.tanggal_daftar
FROM pendaftar p 
LEFT JOIN asal_sekolah a ON p.asal_sekolah_id = a.id 
LEFT JOIN jurusan j ON p.jurusan_tujuan_id = j.id 
ORDER BY p.tanggal_daftar DESC
```

**UPDATE:**
```sql
UPDATE pendaftar 
SET status_pendaftaran = ?, catatan_panitia = ?, 
    tanggal_verifikasi = NOW(), diverifikasi_oleh = ? 
WHERE id = ?
```

**Features:**
- Status badge dengan warna berbeda untuk setiap status
- Modal dialog inline (CSS, no JavaScript required)
- Form validation
- Error handling
- Success message

**URL:** http://localhost/ppdb_sekolah_2/verifikasi.php

---

### 9. **logout.php**
**Fungsi:** Handler logout

**Logika:**
```php
1. Destroy session
2. Redirect ke Login/login.php
```

**URL:** http://localhost/ppdb_sekolah_2/logout.php

---

### 10. **seed.php**
**Fungsi:** Seeding data awal ke database

**Data yang Dibuat:**

1. **Admin User**
   - Email: admin@ppdb.com
   - Password: admin123
   - Role: admin

2. **Asal Sekolah (5 records)**
   - SMP Negeri 1 Palembang
   - SMP Negeri 2 Palembang
   - SMP Negeri 3 Palembang
   - SMP Swasta Al-Quran
   - SMP Negeri 29 Palembang

3. **Jurusan (5 records)**
   - Rekayasa Perangkat Lunak (RPL) - 60 kuota
   - Teknik Komputer dan Jaringan (TKJ) - 50 kuota
   - Teknik Mesin - 45 kuota
   - Desain Grafis Multimedia - 40 kuota
   - Administrasi Perkantoran - 35 kuota

4. **Fase Pendaftaran (4 records)**
   - Fase 1: Pendaftaran Online (01-15 Mei)
   - Fase 2: Seleksi Administrasi (16-22 Mei)
   - Fase 3: Tes Tulis & Wawancara (24-30 Mei)
   - Fase 4: Pengumuman Hasil (01-05 Juni)

5. **Informasi Sekolah**
   - SMKN 4 Palembang dengan visi, misi, deskripsi

**Features:**
- Check duplikasi sebelum insert
- Display progress dengan ✓ atau ✗
- Output admin credentials
- Idempotent (aman dijalankan berkali-kali)

**URL:** http://localhost/ppdb_sekolah_2/seed.php

---

## DATABASE SCHEMA

### Tabel: users
```
id              INT PRIMARY KEY AUTO_INCREMENT
username        VARCHAR(50) NOT NULL UNIQUE
email           VARCHAR(100) NOT NULL UNIQUE
password        VARCHAR(255) NOT NULL (hashed)
nama_lengkap    VARCHAR(100) NOT NULL
jenis_kelamin   ENUM('Laki-laki','Perempuan')
alamat          TEXT
role            ENUM('admin','panitia','calon_siswa') DEFAULT 'calon_siswa'
status          ENUM('aktif','nonaktif') DEFAULT 'aktif'
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
```

### Tabel: pendaftar
```
id                      INT PRIMARY KEY AUTO_INCREMENT
user_id                 INT NOT NULL (FK users.id)
nisn                    VARCHAR(20) NOT NULL
nama_lengkap            VARCHAR(100) NOT NULL
jenis_kelamin           ENUM('Laki-laki','Perempuan')
tanggal_lahir           DATE
alamat                  TEXT
no_hp                   VARCHAR(15)
asal_sekolah_id         INT (FK asal_sekolah.id)
jurusan_tujuan_id       INT (FK jurusan.id)
status_pendaftaran      ENUM(...) DEFAULT 'belum_verifikasi'
nilai_tes               DECIMAL(5,2)
catatan_panitia         TEXT
tanggal_daftar          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
tanggal_verifikasi      DATETIME
diverifikasi_oleh       INT (FK users.id)
updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
```

### Tabel: asal_sekolah
```
id          INT PRIMARY KEY AUTO_INCREMENT
nama_sekolah VARCHAR(150) NOT NULL UNIQUE
alamat      TEXT
kota        VARCHAR(100)
kecamatan   VARCHAR(100)
created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

### Tabel: jurusan
```
id              INT PRIMARY KEY AUTO_INCREMENT
nama_jurusan    VARCHAR(100) NOT NULL UNIQUE
deskripsi       TEXT
kuota           INT DEFAULT 0
kuota_tersisa   INT DEFAULT 0
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
```

### Tabel: fase_pendaftaran
```
id              INT PRIMARY KEY AUTO_INCREMENT
nomor_fase      INT NOT NULL
nama_fase       VARCHAR(100)
deskripsi       TEXT
tanggal_mulai   DATE
tanggal_selesai DATE
status          ENUM('belum_dimulai','berlangsung','selesai')
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
```

### Tabel Lainnya (supporting tables)
- informasi_sekolah
- hasil_tes
- dokumen_pendaftar
- jadwal_kegiatan
- log_aktivitas

---

## SECURITY FEATURES

### 1. Password Security
```php
// Hashing
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Verify
password_verify($input_password, $hashed_password)
```

### 2. SQL Injection Prevention
```php
// Menggunakan prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
```

### 3. Session Management
```php
session_start();
$_SESSION['user_id'] = $user['id'];
// Auto-check di setiap page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}
```

### 4. Role-Based Access Control
```php
// Admin only
if ($_SESSION['role'] !== 'admin') {
    header("Location: form.php");
}
```

### 5. Input Sanitization
```php
// Output escaping
echo htmlspecialchars($user_input);

// Database escaping
$escaped = $conn->real_escape_string($input);
```

---

## WORKFLOW DIAGRAM

```
┌──────────────────────────────────────────────────────────────────┐
│                      SYSTEM WORKFLOW                             │
└──────────────────────────────────────────────────────────────────┘

CALON SISWA WORKFLOW:
├─ Register/register.php
│  ├─ Input: nama, email, password, jenis_kelamin, alamat
│  ├─ Validation: email unique, password >= 6 karakter
│  ├─ Action: INSERT into users (role='calon_siswa')
│  └─ Result: auto redirect to Login
│
├─ Login/login.php
│  ├─ Input: email, password
│  ├─ Validation: email exist, password correct, status='aktif'
│  ├─ Action: Verify password, create SESSION
│  └─ Result: redirect to Formulir/form.php
│
├─ Formulir/form.php
│  ├─ Input: nisn, nama, tgl_lahir, alamat, no_hp, asal_sekolah, jurusan
│  ├─ Validation: all required, check database for choices
│  ├─ Action: INSERT/UPDATE pendaftar (status='belum_verifikasi')
│  └─ Result: success message
│
└─ Formulir/status.php
   ├─ Display: all registration data + timeline
   ├─ Show: current status with color indicator
   └─ Options: edit data, logout

ADMIN WORKFLOW:
├─ Login/login.php
│  ├─ Input: email (admin@ppdb.com), password (admin123)
│  ├─ Action: Verify, create SESSION (role='admin')
│  └─ Result: redirect to dashboard.php
│
├─ dashboard.php
│  ├─ Display: statistics, total pendaftar, kuota, pending
│  ├─ Show: table semua pendaftar
│  └─ Options: link ke verifikasi.php
│
└─ verifikasi.php
   ├─ Display: table all pendaftar dengan tombol "Verifikasi"
   ├─ Modal Dialog:
   │  ├─ Input: status baru, catatan
   │  ├─ Validation: status in enum values
   │  └─ Action: UPDATE pendaftar (status, catatan, tanggal_verifikasi, diverifikasi_oleh)
   └─ Result: table refresh dengan status baru

CALON SISWA SEES STATUS UPDATE:
└─ Formulir/status.php akan menampilkan status terbaru + catatan admin
```

---

## ERROR HANDLING

### Database Connection Error
```php
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
```

### Prepared Statement Error
```php
if (!$insert_stmt) {
    $error = "Error prepare: " . $conn->error;
}
```

### Form Validation Error
```php
if (empty($field) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Validation error message";
}
```

### Query Execution Error
```php
if (!$stmt->execute()) {
    $error = "Error: " . $conn->error;
}
```

---

## TESTING CHECKLIST

- [ ] Database import successful
- [ ] Seed.php created admin + data
- [ ] Register page works (create new account)
- [ ] Login page works (login with new account)
- [ ] Form page works (fill and save data)
- [ ] Data appears in database (check phpmyadmin)
- [ ] Status page shows correct data
- [ ] Admin login works (admin@ppdb.com / admin123)
- [ ] Admin dashboard shows stats
- [ ] Verifikasi page works
- [ ] Admin can change status
- [ ] Status update reflects in database
- [ ] Student sees updated status
- [ ] Logout works (session destroyed)
- [ ] Session redirect works (can't access without login)

---

**Sistem PPDB Sekolah v1.0 - Ready to Deploy**

Last Updated: Juni 2026
