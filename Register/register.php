<?php
require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    // Validation
    if (empty($nama) || empty($email) || empty($password) || empty($jenis_kelamin) || empty($alamat)) {
        $error = "Semua field harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password harus minimal 6 karakter!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Generate username from email
            $username = explode('@', $email)[0] . '_' . time();

            // Insert user
            $insert_sql = "INSERT INTO users (username, email, password, nama_lengkap, jenis_kelamin, alamat, role, status, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, 'calon_siswa', 'aktif', NOW())";
            
            $insert_stmt = $conn->prepare($insert_sql);
            if (!$insert_stmt) {
                $error = "Error prepare: " . $conn->error;
            } else {
                $insert_stmt->bind_param("ssssss", $username, $email, $hashed_password, $nama, $jenis_kelamin, $alamat);
                
                if ($insert_stmt->execute()) {
                    $user_id = $insert_stmt->insert_id;
                    $success = "Pendaftaran berhasil! Silakan login untuk melanjutkan.";
                    
                    // Redirect ke login setelah 2 detik
                    header("refresh:2;url=../Login/login.php");
                } else {
                    $error = "Error: " . $conn->error;
                }
            }
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register PPDB 2026</title>
    <link rel="stylesheet" href="register.css">
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
    </style>
</head>

<body>

    <div class="container">
        <h2>Register PPDB 2026</h2>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="input-group">
                <label for="nama">Nama Lengkap</label>
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    placeholder="Masukkan nama lengkap" 
                    required
                    value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>"
                >
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Masukkan email" 
                    required
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                >
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Masukkan password (min 6 karakter)" 
                    required
                >
            </div>

            <div class="input-group">
                <label for="jk">Jenis Kelamin</label>
                <select id="jk" name="jenis_kelamin" required>
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="Laki-laki" <?php echo (($_POST['jenis_kelamin'] ?? '') === 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php echo (($_POST['jenis_kelamin'] ?? '') === 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>

            <div class="input-group">
                <label for="alamat">Alamat</label>
                <textarea 
                    id="alamat" 
                    name="alamat" 
                    rows="3" 
                    placeholder="Masukkan alamat"
                    required
                ><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn">
                Daftar Sekarang
            </button>

            <div class="login-link">
                Sudah punya akun?
                <a href="../Login/login.php">Login</a>
            </div>

            <div style="text-align: center; margin-top: 10px;">
                <a href="../Beranda/beranda.php" style="color: royalblue; text-decoration: none; font-size: 13px;">← Kembali ke Beranda</a>
            </div>

        </form>
    </div>

</body>
</html>
