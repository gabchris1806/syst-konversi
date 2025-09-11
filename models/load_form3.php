<?php
session_start();
include __DIR__ . '/../config/db.php';

// Cek login user
if (!isset($_SESSION['nip'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$nip = $_SESSION['nip'];

// Ambil tahun dari POST
$tahun = isset($_POST['tahun_pilih']) ? intval($_POST['tahun_pilih']) : 0;

if ($tahun <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Tahun tidak valid']);
    exit();
}

// Query untuk mengambil data dari tahun yang dipilih DAN tahun berikutnya
$tahun_berikutnya = $tahun + 1;
$query = "SELECT * FROM nilai WHERE nip = '$nip' AND (tahun = '$tahun' OR tahun = '$tahun_berikutnya') ORDER BY tahun ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

$data = [];
$total_angka_kredit = 0;

// Ambil semua data
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
    $total_angka_kredit += floatval($row['angka_kredit']);
}

if (empty($data)) {
    // Tidak ada data
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'no_data',
        'message' => $tahun === 'all' ? 'Tidak ada data sama sekali' : 'Tidak ada data untuk tahun ' . $tahun
    ]);
    exit();
}

// Ada data, kirim response sukses dengan data yang diperlukan
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'data' => $data,
    'total_angka_kredit' => $total_angka_kredit,
    'tahun' => $tahun,
    'jumlah_data' => count($data)
]);
?>