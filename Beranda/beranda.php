<?php
require_once '../config.php';

// Beranda bisa diakses siapa saja, tidak perlu redirect
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPDB 2026 - Penerimaan Peserta Didik Baru</title>
    <link rel="stylesheet" href="beranda.css">
</head>
<body>
    <div class="navbar">
        <div class="navbar-content">
            <h1>SMKN 4 Palembang</h1>
            <p>Penerimaan Peserta Didik Baru 2026</p>
        </div>
    </div>

    <div class="container">
        <div class="hero">
            <div class="hero-content">
                <h2>Selamat Datang di PPDB Online</h2>
                <p>Penerimaan Peserta Didik Baru Tahun Ajaran 2026/2027</p>
                <p class="subtitle">Daftar sekarang untuk menjadi bagian dari SMKN 4 Palembang</p>
            </div>

            <div class="button-group">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="../dashboard.php" class="btn btn-login">
                            <span>📊</span>
                            <div>
                                <h3>Dashboard Admin</h3>
                                <p>Kelola data pendaftaran</p>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="../Formulir/form.php" class="btn btn-login">
                            <span>📝</span>
                            <div>
                                <h3>Lanjutkan Form</h3>
                                <p>Isi data pendaftaran Anda</p>
                            </div>
                        </a>
                        <a href="../Formulir/status.php" class="btn btn-register">
                            <span>👁️</span>
                            <div>
                                <h3>Lihat Status</h3>
                                <p>Cek status pendaftaran Anda</p>
                            </div>
                        </a>
                    <?php endif; ?>
                    <a href="../logout.php" class="btn" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);">
                        <span>🚪</span>
                        <div>
                            <h3>Logout</h3>
                            <p>Keluar dari akun Anda</p>
                        </div>
                    </a>
                <?php else: ?>
                    <a href="../Login/login.php" class="btn btn-login">
                        <span>🔐</span>
                        <div>
                            <h3>Login</h3>
                            <p>Sudah punya akun? Masuk di sini</p>
                        </div>
                    </a>

                    <a href="../Register/register.php" class="btn btn-register">
                        <span>✏️</span>
                        <div>
                            <h3>Daftar</h3>
                            <p>Belum punya akun? Daftar sekarang</p>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-section">
            <h3>Alur Pendaftaran</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h4>Daftar Akun</h4>
                    <p>Buat akun dengan email dan data pribadi Anda</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h4>Login</h4>
                    <p>Masuk dengan email dan password Anda</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h4>Isi Data Pendaftaran</h4>
                    <p>Lengkapi data diri dan pilih jurusan</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h4>Verifikasi Admin</h4>
                    <p>Tunggu verifikasi dari panitia PPDB</p>
                </div>
                <div class="step">
                    <div class="step-number">5</div>
                    <h4>Hasil Seleksi</h4>
                    <p>Lihat hasil akhir penerimaan Anda</p>
                </div>
            </div>
        </div>

        <div class="requirements-section">
            <h3>Syarat & Ketentuan</h3>
            <ul>
                <li>Lulusan SMP/MTs tahun 2025 atau tahun sebelumnya</li>
                <li>Memiliki NISN (Nomor Induk Siswa Nasional)</li>
                <li>Menyiapkan dokumen pendukung sesuai kebutuhan</li>
                <li>Mengikuti seluruh tahap seleksi yang telah ditentukan</li>
            </ul>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 PPDB SMKN 4 Palembang. Semua hak cipta dilindungi.</p>
    </footer>
</body>
</html>
