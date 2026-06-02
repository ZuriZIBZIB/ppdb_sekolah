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
    $action = $_POST['action'];
    $pendaftar_id = intval($_POST['pendaftar_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';
    $catatan = trim($_POST['catatan'] ?? '');

    if ($pendaftar_id > 0 && in_array($new_status, ['terdaftar', 'lolos_administrasi', 'lolos_tes', 'diterima', 'ditolak'])) {
        $update_sql = "UPDATE pendaftar SET status_pendaftaran = ?, catatan_panitia = ?, 
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

// Get pending registrants
$sql = "SELECT p.id, p.nama_lengkap, p.nisn, p.jenis_kelamin, p.tanggal_lahir, p.no_hp, 
               p.alamat, p.status_pendaftaran, p.catatan_panitia, a.nama_sekolah, j.nama_jurusan, p.tanggal_daftar
        FROM pendaftar p 
        LEFT JOIN asal_sekolah a ON p.asal_sekolah_id = a.id 
        LEFT JOIN jurusan j ON p.jurusan_tujuan_id = j.id 
        ORDER BY p.tanggal_daftar DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pendaftar PPDB</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid royalblue;
            padding-bottom: 20px;
        }
        .header h1 {
            color: royalblue;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: royalblue;
            color: white;
            padding: 15px;
            text-align: left;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .action-form {
            display: inline-block;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal.show {
            display: block;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 500px;
            border-radius: 10px;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .close-btn {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        .close-btn:hover {
            color: #000;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            background: royalblue;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn:hover {
            background: deepskyblue;
        }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
        }
        .view-btn {
            background: #17a2b8;
        }
        .view-btn:hover {
            background: #138496;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Verifikasi Pendaftar PPDB</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

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
                <?php 
                $no = 1;
                while ($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($row['nisn']); ?></td>
                    <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_sekolah'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_jurusan'] ?? '-'); ?></td>
                    <td>
                        <?php
                        $status = $row['status_pendaftaran'];
                        $status_class = 'status-pending';
                        
                        if (strpos($status, 'lolos') !== false || $status === 'diterima') {
                            $status_class = 'status-approved';
                        } elseif ($status === 'ditolak') {
                            $status_class = 'status-rejected';
                        }
                        ?>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></td>
                    <td>
                        <button class="btn btn-small view-btn" onclick="openModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_lengkap']); ?>', '<?php echo htmlspecialchars($row['status_pendaftaran']); ?>', '<?php echo htmlspecialchars($row['catatan_panitia']); ?>')">
                            Verifikasi
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="verifikasiModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Verifikasi Pendaftar</h2>
            <span class="close-btn" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <div class="form-group">
                <label>Nama Pendaftar:</label>
                <input type="text" id="nama_pendaftar" readonly>
            </div>

            <div class="form-group">
                <label for="new_status">Status:</label>
                <select name="new_status" id="new_status" required>
                    <option value="">Pilih Status</option>
                    <option value="terdaftar">Terdaftar</option>
                    <option value="lolos_administrasi">Lolos Administrasi</option>
                    <option value="lolos_tes">Lolos Tes</option>
                    <option value="diterima">Diterima</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>

            <div class="form-group">
                <label for="catatan">Catatan:</label>
                <textarea name="catatan" id="catatan" rows="4" placeholder="Masukkan catatan verifikasi..."></textarea>
            </div>

            <input type="hidden" name="action" value="verify">
            <input type="hidden" name="pendaftar_id" id="pendaftar_id">

            <button type="submit" class="btn">Simpan Verifikasi</button>
            <button type="button" class="btn" onclick="closeModal()" style="background: #6c757d;">Batal</button>
        </form>
    </div>
</div>

<script>
function openModal(id, nama, status, catatan) {
    document.getElementById('pendaftar_id').value = id;
    document.getElementById('nama_pendaftar').value = nama;
    document.getElementById('new_status').value = status;
    document.getElementById('catatan').value = catatan;
    document.getElementById('verifikasiModal').classList.add('show');
}

function closeModal() {
    document.getElementById('verifikasiModal').classList.remove('show');
}

window.onclick = function(event) {
    const modal = document.getElementById('verifikasiModal');
    if (event.target == modal) {
        modal.classList.remove('show');
    }
}
</script>

</body>
</html>
