<?php
require_once 'config.php';
session_start();

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
    <style>
        /* ============================================
           Dashboard PPDB SMKN 4 Palembang
           Pure CSS — no glow, no JS
           ============================================ */

        :root {
            --navy:         #1b3f72;
            --navy-dark:    #112c54;
            --sky:          #bdd7f5;
            --sky-dark:     #8bb8e8;
            --accent-green: #0f766e;
            --accent-amber: #b45309;
            --bg:           #eef2f7;
            --bg-card:      #ffffff;
            --border:       #d1dce9;
            --border-light: #e8eef6;
            --text:         #1e2d40;
            --text-muted:   #5a7499;
            --sidebar-w:    256px;
            --radius:       8px;
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body { height: 100%; }

        body {
            background: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            font-size: 14px;
            color: var(--text);
            line-height: 1.6;
        }

        /* ---- Layout ---- */

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* ---- Sidebar ---- */

        .sidebar {
            width: var(--sidebar-w);
            min-width: var(--sidebar-w);
            background: var(--navy);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar::before {
            content: '';
            display: block;
            height: 4px;
            background: var(--sky);
            flex-shrink: 0;
        }

        .sidebar h1 {
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            padding: 22px 22px 0;
            line-height: 1.3;
        }

        .sidebar p {
            color: #93b4d8;
            font-size: 11.5px;
            padding: 6px 22px 22px;
            line-height: 1.5;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 12px 0;
        }

        .sidebar nav a {
            display: block;
            color: #b0cceb;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 10px 22px;
            border-left: 3px solid transparent;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
        }

        .sidebar nav a:hover {
            background: rgba(255,255,255,0.07);
            color: #ffffff;
            border-left-color: var(--sky);
        }

        .sidebar nav a.active {
            background: rgba(255,255,255,0.1);
            color: #ffffff;
            border-left-color: #ffffff;
            font-weight: 600;
        }

        .sidebar nav .logout-btn {
            color: #f4b8b8;
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 12px;
        }

        .sidebar nav .logout-btn:hover {
            background: rgba(185,28,28,0.2);
            color: #fca5a5;
            border-left-color: #f87171;
        }

        .sidebar::after {
            content: 'PPDB 2025 / 2026';
            display: block;
            font-size: 10px;
            color: #4a6a8a;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 16px 22px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        /* ---- Main Content ---- */

        .main {
            flex: 1;
            min-width: 0;
            padding: 32px 36px;
            overflow-y: auto;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            gap: 16px;
        }

        .header-actions > div:first-child {
            border-left: 3px solid var(--sky-dark);
            padding-left: 14px;
        }

        .header-actions h2 {
            font-size: 21px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.2;
        }

        .header-actions small {
            font-size: 12px;
            color: var(--text-muted);
        }

        .header-actions > div:last-child {
            flex-shrink: 0;
            background: var(--sky);
            color: #0f2040;
            padding: 9px 16px;
            border-radius: var(--radius);
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.03em;
            border: 1px solid var(--sky-dark);
            text-transform: uppercase;
        }

        /* ---- Stat Cards ---- */

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: border-color 0.2s;
        }

        .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--navy);
        }

        .card:hover { border-color: var(--sky-dark); }

        .card:nth-child(2)::after { background: var(--accent-amber); }
        .card:nth-child(3)::after { background: var(--accent-green); }

        .card h3 {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .card p {
            font-size: 40px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1;
            letter-spacing: -0.02em;
        }

        .card:nth-child(2) p { color: var(--accent-amber); }
        .card:nth-child(3) p { color: var(--accent-green); }

        /* ---- Overview Section ---- */

        .overview {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            align-items: start;
        }

        .chart-card,
        .table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .chart-card h3,
        .table-card h3 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
            background: #f7fafd;
        }

        /* ---- Progress Bar ---- */

        .progress-bar {
            display: flex;
            flex-direction: column;
        }

        .progress-item {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-light);
        }

        .progress-item:last-child { border-bottom: none; }

        .progress-item span {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .progress-item span strong {
            font-weight: 600;
            color: var(--text);
        }

        .progress-track {
            height: 8px;
            background: var(--border-light);
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 2px;
        }

        /* ---- Table ---- */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--text-muted);
            background: #f7fafd;
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }

        tbody td {
            padding: 12px 16px;
            font-size: 13px;
            color: var(--text);
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }

        tbody tr:hover td { background: #f3f8fe; }

        tbody tr:nth-child(even) td { background: #fafcff; }
        tbody tr:nth-child(even):hover td { background: #f0f6fc; }

        /* ---- Status Badges ---- */

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid transparent;
        }

        .status-pending {
            background: #fffbeb;
            color: #92400e;
            border-color: #fcd34d;
        }

        .status-approved {
            background: #f0fdf4;
            color: #14532d;
            border-color: #86efac;
        }

        .status-rejected {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        /* ---- Responsive ---- */

        @media (max-width: 1100px) {
            .overview { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .layout { flex-direction: column; }

            .sidebar {
                width: 100%;
                min-width: unset;
                height: auto;
                position: static;
            }

            .sidebar nav {
                flex-direction: row;
                flex-wrap: wrap;
                padding: 8px 0;
            }

            .sidebar nav a {
                border-left: none;
                border-bottom: 3px solid transparent;
                padding: 8px 14px;
                font-size: 12px;
            }

            .sidebar nav a:hover,
            .sidebar nav a.active {
                border-left-color: transparent;
                border-bottom-color: var(--sky);
            }

            .sidebar nav .logout-btn { border-top: none; margin-top: 0; }
            .sidebar::after { display: none; }

            .main { padding: 20px 16px; }

            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .cards { grid-template-columns: 1fr 1fr; }
            .cards .card:last-child { grid-column: 1 / -1; }

            .table-card { overflow-x: auto; }
            table { min-width: 560px; }
        }

        @media (max-width: 480px) {
            .cards { grid-template-columns: 1fr; }
            .cards .card:last-child { grid-column: unset; }
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
                <div>Status: Pendaftaran</div>
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
                            <div class="progress-track"><div class="progress-fill" style="width: 100%; background: var(--sky);"></div></div>
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
