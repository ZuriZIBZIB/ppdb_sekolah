<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_sql  = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data   = $user_result->fetch_assoc();
$user_stmt->close();

$pendaftar_sql  = "SELECT p.*, a.nama_sekolah, j.nama_jurusan 
                   FROM pendaftar p 
                   LEFT JOIN asal_sekolah a ON p.asal_sekolah_id = a.id 
                   LEFT JOIN jurusan j ON p.jurusan_tujuan_id = j.id 
                   WHERE p.user_id = ?";
$pendaftar_stmt = $conn->prepare($pendaftar_sql);
$pendaftar_stmt->bind_param("i", $user_id);
$pendaftar_stmt->execute();
$pendaftar_result = $pendaftar_stmt->get_result();
$pendaftar        = $pendaftar_result->num_rows > 0 ? $pendaftar_result->fetch_assoc() : null;
$pendaftar_stmt->close();

function getStatusInfo($status) {
    $info = [
        'belum_verifikasi' => [
            'label'       => 'Belum Diverifikasi',
            'class'       => 'status-belum-verifikasi',
            'description' => 'Data Anda masih dalam proses verifikasi oleh panitia PPDB.',
        ],
        'terdaftar' => [
            'label'       => 'Terdaftar',
            'class'       => 'status-terdaftar',
            'description' => 'Data Anda telah terdaftar di sistem PPDB.',
        ],
        'lolos_administrasi' => [
            'label'       => 'Lolos Administrasi',
            'class'       => 'status-lolos-administrasi',
            'description' => 'Selamat! Anda lolos tahap seleksi administrasi.',
        ],
        'lolos_tes' => [
            'label'       => 'Lolos Tes',
            'class'       => 'status-lolos-tes',
            'description' => 'Selamat! Anda lolos tahap tes tulis dan wawancara.',
        ],
        'diterima' => [
            'label'       => 'Diterima',
            'class'       => 'status-diterima',
            'description' => 'Selamat! Anda diterima sebagai siswa baru SMKN 4 Palembang.',
        ],
        'ditolak' => [
            'label'       => 'Tidak Diterima',
            'class'       => 'status-ditolak',
            'description' => 'Maaf, Anda belum diterima. Cek catatan panitia untuk informasi lebih lanjut.',
        ],
    ];

    return $info[$status] ?? $info['belum_verifikasi'];
}

function isStepActive($current, $steps) {
    return in_array($current, $steps) ? 'active' : '';
}

function isStepDone($current, $steps) {
    return in_array($current, $steps) ? 'done' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran — PPDB SMKN 4 Palembang</title>
    <link rel="stylesheet" href="./status.css">
</head>
<body>

<div class="container">

    <div class="header">
        <h1>Status Pendaftaran PPDB</h1>
        <div>
            <a href="./form.php"    class="header-btn header-btn-edit">Edit Data</a>
            <a href="../logout.php" class="header-btn header-btn-logout">Logout</a>
        </div>
    </div>

    <!-- Informasi Akun -->
    <div class="card">
        <h2>Informasi Akun</h2>
        <table class="info-table">
            <tbody>
                <tr>
                    <td>Nama</td>
                    <td><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td><?php echo htmlspecialchars($user_data['email']); ?></td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td><?php echo htmlspecialchars($user_data['jenis_kelamin']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if ($pendaftar): ?>
        <?php $statusInfo = getStatusInfo($pendaftar['status_pendaftaran']); ?>

        <!-- Status Pendaftaran -->
        <div class="card">
            <h2>Status Pendaftaran</h2>

            <div class="status-box <?php echo $statusInfo['class']; ?>">
                <div>
                    <div class="status-label"><?php echo $statusInfo['label']; ?></div>
                    <div class="status-description"><?php echo $statusInfo['description']; ?></div>
                </div>
            </div>

            <table class="info-table">
                <tbody>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></td>
                    </tr>
                    <tr>
                        <td>NISN</td>
                        <td><?php echo htmlspecialchars($pendaftar['nisn']); ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal Lahir</td>
                        <td><?php echo date('d/m/Y', strtotime($pendaftar['tanggal_lahir'])); ?></td>
                    </tr>
                    <tr>
                        <td>Nomor HP</td>
                        <td><?php echo htmlspecialchars($pendaftar['no_hp']); ?></td>
                    </tr>
                    <tr>
                        <td>Asal Sekolah</td>
                        <td><?php echo htmlspecialchars($pendaftar['nama_sekolah'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td>Jurusan Tujuan</td>
                        <td><?php echo htmlspecialchars($pendaftar['nama_jurusan'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal Daftar</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($pendaftar['tanggal_daftar'])); ?></td>
                    </tr>
                    <?php if (!empty($pendaftar['catatan_panitia'])): ?>
                    <tr>
                        <td>Catatan Panitia</td>
                        <td><?php echo htmlspecialchars($pendaftar['catatan_panitia']); ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="./form.php" class="edit-link">Edit Data Pendaftaran</a>
        </div>

        <!-- Alur Penerimaan -->
        <div class="card">
            <h2>Alur Penerimaan</h2>
            <div class="timeline">

                <div class="timeline-item
                    <?php echo isStepActive($pendaftar['status_pendaftaran'], ['terdaftar','lolos_administrasi','lolos_tes','diterima','ditolak']); ?>
                    <?php echo isStepDone($pendaftar['status_pendaftaran'],   ['lolos_administrasi','lolos_tes','diterima']); ?>"
                    data-step="1">
                    <div class="timeline-content">
                        <h4>Pendaftaran</h4>
                        <p>Data Anda telah terdaftar di sistem PPDB</p>
                    </div>
                </div>

                <div class="timeline-item
                    <?php echo isStepActive($pendaftar['status_pendaftaran'], ['lolos_administrasi','lolos_tes','diterima']); ?>
                    <?php echo isStepDone($pendaftar['status_pendaftaran'],   ['lolos_tes','diterima']); ?>"
                    data-step="2">
                    <div class="timeline-content">
                        <h4>Seleksi Administrasi</h4>
                        <p>Verifikasi kelengkapan berkas administrasi</p>
                    </div>
                </div>

                <div class="timeline-item
                    <?php echo isStepActive($pendaftar['status_pendaftaran'], ['lolos_tes','diterima']); ?>
                    <?php echo isStepDone($pendaftar['status_pendaftaran'],   ['diterima']); ?>"
                    data-step="3">
                    <div class="timeline-content">
                        <h4>Tes Tulis &amp; Wawancara</h4>
                        <p>Mengikuti tes tulis dan wawancara</p>
                    </div>
                </div>

                <div class="timeline-item
                    <?php echo isStepActive($pendaftar['status_pendaftaran'], ['diterima']); ?>
                    <?php echo isStepDone($pendaftar['status_pendaftaran'],   ['diterima']); ?>"
                    data-step="4">
                    <div class="timeline-content">
                        <h4>Penerimaan Final</h4>
                        <p>Pengumuman hasil penerimaan siswa baru</p>
                    </div>
                </div>

            </div>
        </div>

    <?php else: ?>
        <div class="card">
            <div class="no-data">
                <p>Anda belum mengisi form pendaftaran.</p>
                <a href="./form.php" class="edit-link">Isi Form Pendaftaran</a>
            </div>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
        