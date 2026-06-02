<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit();
}

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];

// Get user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Get existing registration data if any
$pendaftar_sql = "SELECT * FROM pendaftar WHERE user_id = ?";
$pendaftar_stmt = $conn->prepare($pendaftar_sql);
$pendaftar_stmt->bind_param("i", $user_id);
$pendaftar_stmt->execute();
$pendaftar_result = $pendaftar_stmt->get_result();
$pendaftar_data = $pendaftar_result->num_rows > 0 ? $pendaftar_result->fetch_assoc() : null;

// Get list of schools (asal_sekolah)
$schools_sql = "SELECT id, nama_sekolah FROM asal_sekolah ORDER BY nama_sekolah ASC";
$schools_result = $conn->query($schools_sql);

// Get list of majors (jurusan)
$majors_sql = "SELECT id, nama_jurusan FROM jurusan WHERE kuota_tersisa > 0 ORDER BY nama_jurusan ASC";
$majors_result = $conn->query($majors_sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nisn = trim($_POST['nisn'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $asal_sekolah_id = intval($_POST['asal_sekolah_id'] ?? 0);
    $jurusan_tujuan_id = intval($_POST['jurusan_tujuan_id'] ?? 0);

    // Validation
    if (empty($nisn) || empty($nama_lengkap) || empty($jenis_kelamin) || empty($tanggal_lahir) || 
        empty($alamat) || empty($no_hp) || $asal_sekolah_id == 0 || $jurusan_tujuan_id == 0) {
        $error = "Semua field harus diisi!";
    } else {
        if ($pendaftar_data) {
            // Update existing registration
            $update_sql = "UPDATE pendaftar SET nisn = ?, nama_lengkap = ?, jenis_kelamin = ?, 
                          tanggal_lahir = ?, alamat = ?, no_hp = ?, asal_sekolah_id = ?, 
                          jurusan_tujuan_id = ?, updated_at = NOW() WHERE user_id = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssssii", $nisn, $nama_lengkap, $jenis_kelamin, $tanggal_lahir, 
                                     $alamat, $no_hp, $asal_sekolah_id, $jurusan_tujuan_id, $user_id);
            
            if ($update_stmt->execute()) {
                $success = "Data pendaftaran berhasil diperbarui!";
                header("refresh:2;url=./status.php");
                $pendaftar_data = [
                    'nisn' => $nisn,
                    'nama_lengkap' => $nama_lengkap,
                    'jenis_kelamin' => $jenis_kelamin,
                    'tanggal_lahir' => $tanggal_lahir,
                    'alamat' => $alamat,
                    'no_hp' => $no_hp,
                    'asal_sekolah_id' => $asal_sekolah_id,
                    'jurusan_tujuan_id' => $jurusan_tujuan_id
                ];
            } else {
                $error = "Error: " . $conn->error;
            }
            $update_stmt->close();
        } else {
            // Create new registration
            $insert_sql = "INSERT INTO pendaftar (user_id, nisn, nama_lengkap, jenis_kelamin, tanggal_lahir, 
                          alamat, no_hp, asal_sekolah_id, jurusan_tujuan_id, status_pendaftaran, tanggal_daftar) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'belum_verifikasi', NOW())";
            
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("issssssii", $user_id, $nisn, $nama_lengkap, $jenis_kelamin, $tanggal_lahir, 
                                     $alamat, $no_hp, $asal_sekolah_id, $jurusan_tujuan_id);
            
            if ($insert_stmt->execute()) {
                $success = "Data pendaftaran berhasil disimpan!";
                header("refresh:2;url=./status.php");
                $pendaftar_data = [
                    'nisn' => $nisn,
                    'nama_lengkap' => $nama_lengkap,
                    'jenis_kelamin' => $jenis_kelamin,
                    'tanggal_lahir' => $tanggal_lahir,
                    'alamat' => $alamat,
                    'no_hp' => $no_hp,
                    'asal_sekolah_id' => $asal_sekolah_id,
                    'jurusan_tujuan_id' => $jurusan_tujuan_id
                ];
            } else {
                $error = "Error: " . $conn->error;
            }
            $insert_stmt->close();
        }
    }
}

$user_stmt->close();
$pendaftar_stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran Siswa</title>
    <link rel="stylesheet" href="form.css">
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }
        .logout-link {
            text-align: right;
            margin-bottom: 20px;
        }
        .logout-link a {
            color: royalblue;
            text-decoration: none;
            font-weight: bold;
            margin-left: 15px;
        }
        .logout-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logout-link">
            <a href="./status.php">Lihat Status</a>
            <a href="../logout.php">Logout</a>
        </div>

        <h2>Form Pendaftaran Siswa</h2>
        <p style="text-align: center; color: #666; margin-bottom: 20px;">Selamat datang, <?php echo htmlspecialchars($user_data['email']); ?></p>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-box">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" required
                    value="<?php echo htmlspecialchars($pendaftar_data['nama_lengkap'] ?? ''); ?>">
            </div>

            <div class="input-box">
                <label>NISN</label>
                <input type="number" name="nisn" placeholder="Masukkan NISN" required
                    value="<?php echo htmlspecialchars($pendaftar_data['nisn'] ?? ''); ?>">
            </div>

            <div class="input-box">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" required>
                    <option value="">Pilih</option>
                    <option value="Laki-laki" <?php echo (($pendaftar_data['jenis_kelamin'] ?? '') === 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php echo (($pendaftar_data['jenis_kelamin'] ?? '') === 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>

            <div class="input-box">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" required
                    value="<?php echo htmlspecialchars($pendaftar_data['tanggal_lahir'] ?? ''); ?>">
            </div>

            <div class="input-box">
                <label>Alamat</label>
                <textarea name="alamat" placeholder="Masukkan alamat" required><?php echo htmlspecialchars($pendaftar_data['alamat'] ?? ''); ?></textarea>
            </div>

            <div class="input-box">
                <label>No HP</label>
                <input type="tel" name="no_hp" placeholder="Masukkan nomor HP" required
                    value="<?php echo htmlspecialchars($pendaftar_data['no_hp'] ?? ''); ?>">
            </div>

            <div class="input-box">
                <label>Asal Sekolah</label>
                <select name="asal_sekolah_id" required>
                    <option value="">Pilih Asal Sekolah</option>
                    <?php while ($school = $schools_result->fetch_assoc()): ?>
                        <option value="<?php echo $school['id']; ?>" 
                            <?php echo (($pendaftar_data['asal_sekolah_id'] ?? 0) == $school['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($school['nama_sekolah']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-box">
                <label>Jurusan Tujuan</label>
                <select name="jurusan_tujuan_id" required>
                    <option value="">Pilih Jurusan Tujuan</option>
                    <?php while ($major = $majors_result->fetch_assoc()): ?>
                        <option value="<?php echo $major['id']; ?>" 
                            <?php echo (($pendaftar_data['jurusan_tujuan_id'] ?? 0) == $major['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($major['nama_jurusan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit">Daftar</button>
        </form>
    </div>

</body>
</html>
