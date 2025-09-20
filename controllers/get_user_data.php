<?php
session_start();
include __DIR__ . '/../config/db.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Cek jika user sudah login
if (!isset($_SESSION['nip'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User tidak terautentikasi'
    ]);
    exit();
}

// Cek jika NIP dikirim via POST
if (!isset($_POST['nip']) || empty($_POST['nip'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'NIP tidak ditemukan'
    ]);
    exit();
}

$nip = $_POST['nip'];

try {
    // Query untuk mengambil data lengkap user dari tabel pegawai
    $stmt = $conn->prepare("SELECT 
        nama,
        nip,
        no_seri_karpeg,
        tempat_tanggal_lahir,
        jenis_kelamin,
        pangkat_golongan_tmt,
        jabatan_tmt,
        unit_kerja,
        instansi
    FROM pegawai 
    WHERE nip = ?");
    
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        
        // Bersihkan data NULL dan ganti dengan string yang lebih user-friendly
        foreach ($userData as $key => $value) {
            if ($value === null || $value === 'NULL' || $value === '') {
                $userData[$key] = '-';
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $userData,
            'message' => 'Data user berhasil diambil'
        ]);
        
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Data user tidak ditemukan'
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Error in get_user_data.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan saat mengambil data user'
    ]);
}

$conn->close();
?>