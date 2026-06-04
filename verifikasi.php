<?php
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login/login.php");
    exit();
}

$success = '';
$error = '';

// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $pendaftar_id = intval($_POST['pendaftar_id'] ?? 0);
    $new_status   = $_POST['new_status'] ?? '';
    $catatan      = trim($_POST['catatan'] ?? '');

    if ($pendaftar_id > 0 && in_array($new_status, ['terdaftar', 'lolos_administrasi', 'lolos_tes', 'diterima', 'ditolak'])) {
        $update_sql  = "UPDATE pendaftar SET status_pendaftaran = ?, catatan_panitia = ?,
                        tanggal_verifikasi = NOW(), diverifikasi_oleh = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssii", $new_status, $catatan, $_SESSION['user_id'], $pendaftar_id);

        if ($update_stmt->execute()) {
            $success = "Status pendaftar berhasil diperbarui!";
        } else {
            $error = "Error: " . $conn->error;
        }
        $update_stmt->close();
    }
}

// Get all registrants — buffered for table + modals
$sql = "SELECT p.id, p.nama_lengkap, p.nisn, p.jenis_kelamin,
               p.status_pendaftaran, p.catatan_panitia,
               a.nama_sekolah, j.nama_jurusan, p.tanggal_daftar
        FROM pendaftar p
        LEFT JOIN asal_sekolah a ON p.asal_sekolah_id = a.id
        LEFT JOIN jurusan j      ON p.jurusan_tujuan_id = j.id
        ORDER BY p.tanggal_daftar DESC";

$result = $conn->query($sql);
$rows   = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pendaftar PPDB</title>
    <style>
        /* ============================================
           Verifikasi Pendaftar — PPDB SMKN 4 Palembang
           Pure CSS, no JS
           ============================================ */

        :root {
            --navy:         #1b3f72;
            --navy-dark:    #112c54;
            --sky:          #bdd7f5;
            --sky-dark:     #8bb8e8;
            --red:          #b91c1c;
            --red-hover:    #991b1b;
            --teal:         #0e7490;
            --teal-hover:   #0c6483;
            --green:        #0f766e;
            --bg:           #eef2f7;
            --bg-card:      #ffffff;
            --border:       #d1dce9;
            --border-light: #e8eef6;
            --text:         #1e2d40;
            --text-muted:   #5a7499;
            --text-light:   #8aa0bb;
            --radius:       8px;
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            font-size: 14px;
            color: var(--text);
            line-height: 1.6;
            padding: 32px 28px;
            min-height: 100vh;
        }

        .container {
            max-width: 1240px;
            margin: 0 auto;
        }

        /* ---- Header ---- */

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            gap: 20px;
        }

        .header-left {
            border-left: 3px solid var(--sky-dark);
            padding-left: 16px;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.2;
            letter-spacing: -0.01em;
        }

        .header p {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .logout-btn {
            display: inline-block;
            background: var(--red);
            color: #ffffff;
            padding: 9px 18px;
            border-radius: var(--radius);
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            transition: background 0.15s;
            flex-shrink: 0;
            border: 1px solid var(--red-hover);
        }

        .logout-btn:hover { background: var(--red-hover); }

        /* ---- Alert messages ---- */

        .message {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 16px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid transparent;
            border-left-width: 4px;
        }

        .success {
            background: #f0fdf4;
            color: #14532d;
            border-color: #86efac;
            border-left-color: var(--green);
        }

        .error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fca5a5;
            border-left-color: var(--red);
        }

        /* ---- Content card ---- */

        .content-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-top: 3px solid var(--navy);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .content-card-header {
            display: flex;
            align-items: center;
            padding: 15px 22px;
            background: #f7fafd;
            border-bottom: 1px solid var(--border-light);
        }

        .content-card-header h2 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
        }

        /* ---- Table ---- */

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            border-bottom: 2px solid var(--border);
        }

        thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            background: #f7fafd;
            white-space: nowrap;
        }

        thead th:first-child { padding-left: 22px; }
        thead th:last-child  { padding-right: 22px; text-align: center; }

        tbody td {
            padding: 13px 16px;
            font-size: 13px;
            color: var(--text);
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        tbody td:first-child {
            padding-left: 22px;
            color: var(--text-light);
            font-size: 12px;
            font-weight: 600;
            width: 48px;
        }

        tbody td:last-child {
            padding-right: 22px;
            text-align: center;
        }

        tbody tr:last-child td { border-bottom: none; }

        tbody tr:hover td { background: #f3f8fe; }

        tbody tr:nth-child(even) td { background: #fafcff; }
        tbody tr:nth-child(even):hover td { background: #eef5fc; }

        /* ---- Status badges ---- */

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .status-pending  { background: #fffbeb; color: #92400e; border-color: #fcd34d; }
        .status-approved { background: #f0fdf4; color: #14532d; border-color: #6ee7b7; }
        .status-rejected { background: #fef2f2; color: #991b1b; border-color: #fca5a5; }

        /* ---- Buttons ---- */

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: none;
            border-radius: var(--radius);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s;
            white-space: nowrap;
        }

        .btn-sm {
            padding: 5px 13px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .btn-teal  { background: var(--teal); color: #fff; border: 1px solid var(--teal-hover); }
        .btn-teal:hover  { background: var(--teal-hover); }

        .btn-navy  { background: var(--navy); color: #fff; border: 1px solid var(--navy-dark); }
        .btn-navy:hover  { background: var(--navy-dark); }

        .btn-ghost { background: #f1f5f9; color: var(--text-muted); border: 1px solid var(--border); }
        .btn-ghost:hover { background: #e2e8f0; color: var(--text); }

        /* ---- Back link ---- */

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 22px;
            color: var(--navy);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding-bottom: 2px;
            border-bottom: 1px solid transparent;
            transition: border-color 0.15s;
        }

        .back-link:hover { border-bottom-color: var(--navy); }

        /* ============================================
           CSS-ONLY MODAL  (:target)
           Open:  <a href="#modal-{id}">
           Close: <a href="#"> pada .modal-overlay atau .modal-close
           ============================================ */

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal:target { display: flex; }

        .modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(10, 24, 50, 0.5);
        }

        .modal-box {
            position: relative;
            z-index: 1;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-top: 3px solid var(--teal);
            border-radius: var(--radius);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-light);
        }

        .modal-header h2 {
            font-size: 15px;
            font-weight: 700;
            color: var(--navy);
            letter-spacing: -0.01em;
        }

        .modal-close {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            font-size: 18px;
            line-height: 1;
            color: var(--text-muted);
            text-decoration: none;
            border: 1px solid var(--border-light);
            transition: background 0.15s, color 0.15s;
        }

        .modal-close:hover { background: #f1f5f9; color: var(--text); }

        .modal-body { padding: 22px 24px; }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--text-muted);
            margin-bottom: 7px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            font-family: inherit;
            color: var(--text);
            background: #ffffff;
            transition: border-color 0.15s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--teal);
            border-left-width: 3px;
        }

        .form-group input[readonly] {
            background: #f7fafd;
            color: var(--text-muted);
            cursor: default;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 88px;
            line-height: 1.5;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 16px 24px;
            background: #f7fafd;
            border-top: 1px solid var(--border-light);
        }

        /* ---- Responsive ---- */

        @media (max-width: 768px) {
            body { padding: 18px 14px; }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            table { min-width: 660px; }

            .modal {
                align-items: flex-end;
                padding: 0;
            }

            .modal-box {
                width: 100%;
                max-width: 100%;
                border-radius: var(--radius) var(--radius) 0 0;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                max-height: 85vh;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <div class="header-left">
            <h1>Verifikasi Pendaftar PPDB</h1>
            <p>SMKN 4 Palembang &mdash; Kelola status pendaftaran siswa baru.</p>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="content-card">
        <div class="content-card-header">
            <h2>Daftar Pendaftar</h2>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NISN</th>
                        <th>Jenis Kelamin</th>
                        <th>Asal Sekolah</th>
                        <th>Jurusan Tujuan</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $row):
                        $status = $row['status_pendaftaran'];
                        if (strpos($status, 'lolos') !== false || $status === 'diterima') {
                            $badge = 'status-approved';
                        } elseif ($status === 'ditolak') {
                            $badge = 'status-rejected';
                        } else {
                            $badge = 'status-pending';
                        }
                    ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($row['nisn']); ?></td>
                        <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_sekolah'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_jurusan'] ?? '-'); ?></td>
                        <td>
                            <span class="status-badge <?php echo $badge; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></td>
                        <td>
                            <a href="#modal-<?php echo $row['id']; ?>" class="btn btn-sm btn-teal">Verifikasi</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>

</div>

<?php foreach ($rows as $row): ?>
<div id="modal-<?php echo $row['id']; ?>" class="modal">
    <a href="#" class="modal-overlay"></a>
    <div class="modal-box">
        <div class="modal-header">
            <h2>Verifikasi Pendaftar</h2>
            <a href="#" class="modal-close">&times;</a>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Pendaftar</label>
                    <input type="text" value="<?php echo htmlspecialchars($row['nama_lengkap']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="new_status_<?php echo $row['id']; ?>">Status</label>
                    <select name="new_status" id="new_status_<?php echo $row['id']; ?>" required>
                        <option value="">Pilih Status</option>
                        <?php
                        $statuses = [
                            'terdaftar'          => 'Terdaftar',
                            'lolos_administrasi' => 'Lolos Administrasi',
                            'lolos_tes'          => 'Lolos Tes',
                            'diterima'           => 'Diterima',
                            'ditolak'            => 'Ditolak',
                        ];
                        foreach ($statuses as $val => $label):
                        ?>
                            <option value="<?php echo $val; ?>" <?php echo $row['status_pendaftaran'] === $val ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="catatan_<?php echo $row['id']; ?>">Catatan</label>
                    <textarea name="catatan" id="catatan_<?php echo $row['id']; ?>" rows="4"
                              placeholder="Masukkan catatan verifikasi..."><?php echo htmlspecialchars($row['catatan_panitia'] ?? ''); ?></textarea>
                </div>
                <input type="hidden" name="action"       value="verify">
                <input type="hidden" name="pendaftar_id" value="<?php echo $row['id']; ?>">
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-navy">Simpan Verifikasi</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

</body>
</html>
