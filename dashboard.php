<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: Login/login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: Formulir/form.php");
    exit();
}

// Get total registrants
$total_pendaftar_sql = "SELECT COUNT(*) as total FROM pendaftar";
$total_pendaftar_result = $conn->query($total_pendaftar_sql);
$total_pendaftar = $total_pendaftar_result->fetch_assoc()['total'];

// Get pending verifications
$pending_verifikasi_sql = "SELECT COUNT(*) as total FROM pendaftar WHERE status_pendaftaran = 'belum_verifikasi'";
$pending_verifikasi_result = $conn->query($pending_verifikasi_sql);
$pending_verifikasi = $pending_verifikasi_result->fetch_assoc()['total'];

// Get total remaining quota
$kuota_tersisa_sql = "SELECT SUM(kuota_tersisa) as total FROM jurusan";
$kuota_tersisa_result = $conn->query($kuota_tersisa_sql);
$kuota_tersisa = $kuota_tersisa_result->fetch_assoc()['total'] ?? 0;

// Get list of all registrants with details
$pendaftar_sql = "SELECT p.id, p.nama_lengkap, p.jenis_kelamin, p.status_pendaftaran, 
                  a.nama_sekolah, j.nama_jurusan 
                  FROM pendaftar p 
                  LEFT JOIN asal_sekolah a ON p.asal_sekolah_id = a.id 
                  LEFT JOIN jurusan j ON p.jurusan_tujuan_id = j.id 
                  ORDER BY p.tanggal_daftar DESC";
$pendaftar_result = $conn->query($pendaftar_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard PPDB SMKN 4 Palembang</title>
    <link rel="stylesheet" href="Dashboard/dashboard.css">
    <style>
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <h1>Dashboard PPDB</h1>
            <p>SMKN 4 Palembang - Pendaftaran PPDB siswa baru.</p>
            <nav>
                <a href="#overview">Ikhtisar</a>
                <a href="#statistik">Statistik</a>
                <a href="#daftar-siswa">Daftar Pendaftar</a>
                <a href="verifikasi.php">Verifikasi Pendaftar</a>
                <a href="logout.php" class="logout-btn">Logout</a>
            </nav>
        </aside>
        <main class="main">
            <div class="header-actions">
                <div>
                    <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                    <small>Data pendaftaran PPDB SMKN 4 Palembang terbaru.</small>
                </div>
                <div style="background: var(--sky-blue); color: #0f172a; padding: 14px 18px; border-radius: 16px; font-weight: 700;">Status: Pendaftaran</div>
            </div>

            <section class="cards">
                <div class="card">
                    <h3>Total Pendaftar</h3>
                    <p><?php echo $total_pendaftar; ?></p>
                </div>
                <div class="card">
                    <h3>Proses Verifikasi</h3>
                    <p><?php echo $pending_verifikasi; ?></p>
                </div>
                <div class="card">
                    <h3>Kuota Tersisa</h3>
                    <p><?php echo $kuota_tersisa; ?></p>
                </div>
            </section>

            <section class="overview" id="overview">
                <div class="chart-card">
                    <h3>Keadaan Pendaftaran</h3>
                    <div class="progress-bar">
                        <div class="progress-item">
                            <span><strong>Total Pendaftar</strong><strong><?php echo $total_pendaftar; ?></strong></span>
                            <div class="progress-track"><div class="progress-fill" style="width: 100%; background: var(--sky-blue);"></div></div>
                        </div>
                    </div>
                </div>
                <div class="table-card" id="daftar-siswa">
                    <h3>Daftar Pendaftar PPDB</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Asal Sekolah</th>
                                <th>Jurusan Tujuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $pendaftar_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_sekolah'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_jurusan'] ?? '-'); ?></td>
                                    <td>
                                        <?php
                                        $status = $row['status_pendaftaran'];
                                        $status_class = 'status-pending';
                                        $status_text = $status;
                                        
                                        if ($status === 'terdaftar') $status_class = 'status-pending';
                                        elseif ($status === 'lolos_administrasi') $status_class = 'status-approved';
                                        elseif ($status === 'diterima') $status_class = 'status-approved';
                                        elseif ($status === 'ditolak') $status_class = 'status-rejected';
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $status_text)); ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
