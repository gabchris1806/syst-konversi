<?php
session_start();
include __DIR__ . '/../config/db.php';

// Pastikan user sudah login
if (!isset($_SESSION['nip'])) {
    header("Location: login.php");
    exit();
}

$nip = $_SESSION['nip'];

// Ambil data user dari DB
$query = mysqli_query($conn, "SELECT * FROM pegawai WHERE nip='$nip'");
$user = mysqli_fetch_assoc($query);

// Update data jika form disubmit
if (isset($_POST['update'])) {
    $no_seri_karpeg = $_POST['no_seri_karpeg'];
    $tempat_tanggal_lahir = $_POST['tempat_tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $pangkat_golongan_tmt = $_POST['pangkat_golongan_tmt'];
    $jabatan_tmt = $_POST['jabatan_tmt'];
    $unit_kerja = $_POST['unit_kerja'];
    $instansi = $_POST['instansi'];

    $update = "UPDATE pegawai SET 
                no_seri_karpeg='$no_seri_karpeg',
                tempat_tanggal_lahir='$tempat_tanggal_lahir',
                jenis_kelamin='$jenis_kelamin',
                pangkat_golongan_tmt='$pangkat_golongan_tmt',
                jabatan_tmt='$jabatan_tmt',
                unit_kerja='$unit_kerja',
                instansi='$instansi'
                WHERE nip='$nip'";

    if (mysqli_query($conn, $update)) {
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* sebelumnya center, ganti flex-start agar mulai dari atas */
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 450px;
            margin: 10px auto;
        }
        label {
            display: block;
            text-align: left;
            margin-top: 10px;
            font-weight: bold;
        }
        input, select {
            width: 90%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .btn-submit {
            width: 95%;
            padding: 10px;
            margin-top: 15px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-submit:hover { background: #0069d9; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Profile</h2>
        <form method="POST">
            <label>NIP</label>
            <input type="text" value="<?= $user['nip']; ?>" readonly>

            <label>Nama</label>
            <input type="text" value="<?= $user['nama']; ?>" readonly>

            <label>Nomor Seri Karpeg</label>
            <input type="text" name="no_seri_karpeg" value="<?= $user['no_seri_karpeg']; ?>">

            <label>Tempat/Tanggal Lahir</label>
            <input type="text" name="tempat_tanggal_lahir" value="<?= $user['tempat_tanggal_lahir']; ?>">

            <label>Jenis Kelamin</label>
            <select name="jenis_kelamin">
                <option value="Laki-laki" <?= ($user['jenis_kelamin']=="Laki-laki")?'selected':''; ?>>Laki-laki</option>
                <option value="Perempuan" <?= ($user['jenis_kelamin']=="Perempuan")?'selected':''; ?>>Perempuan</option>
            </select>

            <label>Pangkat/Golongan Ruang/TMT</label>
            <input type="text" name="pangkat_golongan_tmt" value="<?= $user['pangkat_golongan_tmt']; ?>">

            <label>Jabatan/TMT</label>
            <input type="text" name="jabatan_tmt" value="<?= $user['jabatan_tmt']; ?>">

            <label>Unit Kerja</label>
            <input type="text" name="unit_kerja" value="<?= $user['unit_kerja']; ?>">

            <label>Instansi</label>
            <input type="text" name="instansi" value="<?= $user['instansi']; ?>">

            <button type="submit" name="update" class="btn-submit">Simpan Perubahan</button>
        </form>
    </div>
</body>
</html>
