<?php
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi!";
    } else {
        // Get user by email
        $sql = "SELECT id, username, email, password, role, status FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] === 'aktif' && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../dashboard.php");
                } else {
                    header("Location: ../Formulir/form.php");
                }
                exit();
            } else {
                $error = "Email atau password salah!";
            }
        } else {
            $error = "Email atau password salah!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login PPDB Sekolah</title>

    <link rel="stylesheet" href="login.css">
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h2>PPDB SEKOLAH</h2>
        <p>Silakan login untuk melanjutkan</p>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-box">
                <label>Email</label>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Masukkan email"
                    required
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                >
            </div>

            <div class="input-box">
                <label>Password</label>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="register-link" style="text-align: center; margin-top: 15px;">
            Belum punya akun? <a href="../Register/register.php">Daftar disini</a>
        </div>

        <div style="text-align: center; margin-top: 10px;">
            <a href="../Beranda/beranda.php" style="color: royalblue; text-decoration: none; font-size: 13px;">← Kembali ke Beranda</a>
        </div>

        <div class="footer">
            © 2026 PPDB Online Sekolah
        </div>
    </div>

</body>

</html>
