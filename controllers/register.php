<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../config/db.php';

if (isset($_POST['register'])) {
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if NIP already exists
    $check_query = "SELECT * FROM pegawai WHERE nip='$nip'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('NIP sudah terdaftar! Gunakan NIP lain.');</script>";
    } else {
        // Insert new user with default role 'user'
        $query = "INSERT INTO pegawai (nip, nama, password, role) VALUES ('$nip', '$nama', '$password', 'user')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #2E7D32, #D0F0C0) !important;
        }
        .form-help {
            margin-bottom: 20px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #28a745;
            font-size: 14px;
            color: #495057;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST">
            <input type="text" name="nip" placeholder="Masukkan NIP" required maxlength="20">
            <input type="text" name="nama" placeholder="Masukkan Nama Lengkap" required maxlength="100">
            <input type="password" name="password" placeholder="Masukkan Password" required minlength="6">
            <button type="submit" name="register">Register</button>
        </form>
        <a href="login.php" class="link">Sudah punya akun? Login di sini</a>
    </div>
</body>
</html>