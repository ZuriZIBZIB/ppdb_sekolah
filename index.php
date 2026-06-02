<?php
require_once 'config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: Formulir/form.php");
    }
    exit();
} else {
    // Redirect to Beranda (landing page) if not logged in
    header("Location: Beranda/beranda.php");
    exit();
}
?>
