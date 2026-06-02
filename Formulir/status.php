<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

// Get registration status
$pendaftar_sql = "SELECT p.*, a.nama_sekolah, j.nama_jurusan 
                 FROM pendaftar p 
                 LEFT JOIN asal_sekolah a ON p.asal_sekolah_id = a.id 
                 LEFT JOIN jurusan j ON p.jurusan_tujuan_id = j.id 
                 WHERE p.user_id = ?";
$pendaftar_stmt = $conn->prepare($pendaftar_sql);
$pendaftar_stmt->bind_param("i", $user_id);
$pendaftar_stmt->execute();
$pendaftar_result = $pendaftar_stmt->get_result();
$pendaftar = $pendaftar_result->num_rows > 0 ? $pendaftar_result->fetch_assoc() : null;
$pendaftar_stmt->close();

// Get status color and description
function getStatusInfo($status) {
    $info = [
        'belum_verifikasi' => [
            'label' => 'Belum Diverifikasi',
            'color' => '#ffc107',
            'description' => 'Data Anda masih dalam proses verifikasi oleh panitia PPDB'
        ],
        'terdaftar' => [
            'label' => 'Terdaftar',
            'color' => '#17a2b8',
            'description' => 'Data Anda telah terdaftar di sistem PPDB'
        ],
        'lolos_administrasi' => [
            'label' => 'Lolos Administrasi',
            'color' => '#20c997',
            'description' => 'Selamat! Anda lolos tahap seleksi administrasi'
        ],
        'lolos_tes' => [
            'label' => 'Lolos Tes',
            'color' => '#28a745',
            'description' => 'Selamat! Anda lolos tahap tes tulis dan wawancara'
        ],
        'diterima' => [
            'label' => 'Diterima',
            'color' => '#28a745',
            'description' => 'Selamat! Anda diterima sebagai siswa baru'
        ],
        'ditolak' => [
            'label' => 'Ditolak',
            'color' => '#dc3545',
            'description' => 'Maaf, Anda belum diterima. Cek catatan panitia untuk informasi lebih lanjut'
        ]
    ];
    
    return $info[$status] ?? $info['belum_verifikasi'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, royalblue, skyblue);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: royalblue;
            font-size: 24px;
        }
        .logout-link {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .logout-link:hover {
            background: #c0392b;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: royalblue;
            margin-bottom: 20px;
            border-bottom: 2px solid royalblue;
            padding-bottom: 10px;
        }
        .status-box {
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            color: white;
        }
        .status-label {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-description {
            font-size: 14px;
            line-height: 1.5;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .info-table tr {
            border-bottom: 1px solid #ddd;
        }
        .info-table td {
            padding: 12px;
        }
        .info-table td:first-child {
            font-weight: bold;
            color: royalblue;
            width: 30%;
        }
        .no-data {
            background: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            color: #856404;
            text-align: center;
        }
        .edit-link {
            display: inline-block;
            background: royalblue;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }
        .edit-link:hover {
            background: deepskyblue;
        }
        .timeline {
            margin-top: 20px;
        }
        .timeline-item {
            padding: 15px;
            border-left: 4px solid #ddd;
            margin-bottom: 10px;
            background: #f9f9f9;
        }
        .timeline-item.active {
            border-left-color: royalblue;
            background: #f0f8ff;
        }
        .timeline-item h4 {
            color: royalblue;
            margin-bottom: 5px;
        }
        .timeline-item p {
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Status Pendaftaran PPDB</h1>
            <div>
                <a href="./form.php" class="logout-link" style="margin-right: 10px; background: royalblue;">Edit Data</a>
                <a href="../logout.php" class="logout-link">Logout</a>
            </div>
    <div class="card">
        <h2>Informasi Akun</h2>
        <table class="info-table">
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
        </table>
    </div>

    <?php if ($pendaftar): ?>
        <?php $statusInfo = getStatusInfo($pendaftar['status_pendaftaran']); ?>
        
        <div class="card">
            <h2>Status Pendaftaran</h2>
            <div class="status-box" style="background-color: <?php echo $statusInfo['color']; ?>;">
                <div class="status-label"><?php echo $statusInfo['label']; ?></div>
                <div class="status-description"><?php echo $statusInfo['description']; ?></div>
            </div>

            <table class="info-table">
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
                    <td>Tanggal Pendaftaran</td>
                    <td><?php echo date('d/m/Y H:i', strtotime($pendaftar['tanggal_daftar'])); ?></td>
                </tr>
                <?php if ($pendaftar['catatan_panitia']): ?>
                <tr>
                    <td>Catatan Panitia</td>
                    <td><?php echo htmlspecialchars($pendaftar['catatan_panitia']); ?></td>
                </tr>
                <?php endif; ?>
            </table>

            <a href="./form.php" class="edit-link">Edit Data Pendaftaran</a>
        </div>

        <div class="card">
            <h2>Alur Penerimaan</h2>
            <div class="timeline">
                <div class="timeline-item <?php echo ($pendaftar['status_pendaftaran'] !== 'belum_verifikasi') ? 'active' : ''; ?>">
                    <h4>✓ Pendaftaran</h4>
                    <p>Data Anda telah terdaftar di sistem PPDB</p>
                </div>
                <div class="timeline-item <?php echo in_array($pendaftar['status_pendaftaran'], ['lolos_administrasi', 'lolos_tes', 'diterima']) ? 'active' : ''; ?>">
                    <h4>Seleksi Administrasi</h4>
                    <p>Verifikasi kelengkapan berkas administrasi</p>
                </div>
                <div class="timeline-item <?php echo in_array($pendaftar['status_pendaftaran'], ['lolos_tes', 'diterima']) ? 'active' : ''; ?>">
                    <h4>Tes Tulis & Wawancara</h4>
                    <p>Mengikuti tes tulis dan wawancara</p>
                </div>
                <div class="timeline-item <?php echo ($pendaftar['status_pendaftaran'] === 'diterima') ? 'active' : ''; ?>">
                    <h4>Penerimaan Final</h4>
                    <p>Pengumuman hasil penerimaan siswa baru</p>
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
