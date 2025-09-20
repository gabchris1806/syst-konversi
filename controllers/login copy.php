<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../config/db.php';

if (isset($_POST['login'])) {
    $nip = $_POST['nip'];
    $password = $_POST['password'];

    $query = "SELECT * FROM pegawai WHERE nip='$nip'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    
        if (password_verify($password, $row['password'])) {
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['nip'] = $row['nip'];
            $_SESSION['role'] = $row['role'] ?? 'user'; // Default ke 'user' jika kolom belum ada
            
            // Redirect berdasarkan role
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Password salah!');</script>";
        }
    } else {
        echo "<script>alert('NIP tidak ditemukan!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #2E7D32, #D0F0C0) !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST">
            <input type="text" name="nip" placeholder="Masukkan NIP" required>
            <input type="password" name="password" placeholder="Masukkan Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <a href="register.php" class="link">Belum punya akun? Register di sini</a>
        
    </div>
</body>
</html>     