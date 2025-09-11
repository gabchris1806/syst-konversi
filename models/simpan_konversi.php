<?php
session_start();
include __DIR__ . '/../config/db.php';

// Cek login user
if (!isset($_SESSION['nip'])) {
    header("Location: ../controllers/login.php");
    exit();
}

$nip = $_SESSION['nip'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bulan_awal = trim($_POST['bulan_awal']);
    $bulan_akhir = trim($_POST['bulan_akhir']);
    $tahun = trim($_POST['tahun']);
    $predikat = trim($_POST['predikat']);
    $persentase = $_POST['persentase'];
    $koefisien = $_POST['koefisien'];
    $angka_kredit = $_POST['angka_kredit'];
    
    // ===== VALIDASI INPUT =====
    $errors = [];
    
    // Validasi tahun
    if (empty($tahun)) {
        $errors[] = "Tahun harus diisi.";
    } elseif (!ctype_digit($tahun)) {
        $errors[] = "Tahun harus berupa angka.";
    } elseif (strlen($tahun) != 4) {
        $errors[] = "Tahun harus 4 digit (contoh: 2025).";
    } elseif ((int)$tahun < 1990 || (int)$tahun > 2050) {
        $errors[] = "Tahun harus antara 1990 sampai 2050.";
    }
    
    // Validasi bulan
    $valid_months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    if (empty($bulan_awal) || !in_array($bulan_awal, $valid_months)) {
        $errors[] = "Bulan awal tidak valid.";
    }
    
    if (empty($bulan_akhir) || !in_array($bulan_akhir, $valid_months)) {
        $errors[] = "Bulan akhir tidak valid.";
    }
    
    // Validasi predikat
    if (empty($predikat)) {
        $errors[] = "Predikat harus diisi.";
    } elseif (strlen($predikat) > 50) {
        $errors[] = "Predikat maksimal 50 karakter.";
    }
    
    // Validasi persentase
    if (empty($persentase)) {
        $errors[] = "Persentase harus diisi.";
    } elseif (!is_numeric($persentase)) {
        $errors[] = "Persentase harus berupa angka.";
    } elseif ((int)$persentase < 1 || (int)$persentase > 12) {
        $errors[] = "Persentase harus antara 1 sampai 12.";
    }
    
    // Validasi koefisien
    if (empty($koefisien)) {
        $errors[] = "Koefisien harus diisi.";
    } elseif (!is_numeric($koefisien)) {
        $errors[] = "Koefisien harus berupa angka.";
    } elseif ((float)$koefisien <= 0) {
        $errors[] = "Koefisien harus lebih dari 0.";
    }
    
    // Validasi angka kredit
    if (empty($angka_kredit)) {
        $errors[] = "Angka kredit harus diisi.";
    } elseif (!is_numeric($angka_kredit)) {
        $errors[] = "Angka kredit harus berupa angka.";
    } elseif ((float)$angka_kredit < 0) {
        $errors[] = "Angka kredit tidak boleh negatif.";
    }
    
    // Jika ada error, kembalikan ke dashboard dengan pesan error
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        header("Location: ../controllers/dashboard.php");
        exit();
    }
    
    // Gabungkan bulan ke periode
    $periode = $bulan_awal . " - " . $bulan_akhir;
    
    // Validasi: Cek apakah periode sudah ada untuk user ini di tahun yang sama
    $stmt_check = $conn->prepare("SELECT nip FROM nilai WHERE nip = ? AND periode = ? AND tahun = ?");
    $stmt_check->bind_param("sss", $nip, $periode, $tahun);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        // Periode sudah ada, tampilkan pesan error
        $_SESSION['error_message'] = "Data untuk periode " . $periode . " tahun " . $tahun . " sudah ada. Silakan pilih periode yang berbeda atau edit data yang sudah ada.";
        header("Location: ../controllers/dashboard.php");
        exit();
    }
    
    // Jika tidak ada duplikasi, lanjutkan simpan data menggunakan prepared statement
    $stmt = $conn->prepare("INSERT INTO nilai (nip, predikat, persentase, koefisien, angka_kredit, periode, bulan, tahun) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddssss", $nip, $predikat, $persentase, $koefisien, $angka_kredit, $periode, $bulan_awal, $tahun);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data konversi berhasil disimpan!";
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat menyimpan data: " . $conn->error;
    }
    
    header("Location: ../controllers/dashboard.php");
    exit();
} else {
    // Jika bukan POST request, redirect ke dashboard
    header("Location: ../controllers/dashboard.php");
    exit();
}
?>