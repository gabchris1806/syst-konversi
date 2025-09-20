<?php
session_start();
include __DIR__ . '/../config/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['nip'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$nip = $_SESSION['nip'];

try {
    // Get total data count for this user
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM konversi WHERE nip = ?");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalData = $result->fetch_assoc()['total'];

    // Get data count for current year
    $currentYear = date('Y');
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM konversi WHERE nip = ? AND tahun = ?");
    $stmt->bind_param("ss", $nip, $currentYear);
    $stmt->execute();
    $result = $stmt->get_result();
    $dataThisYear = $result->fetch_assoc()['total'];

    // Get total angka kredit for this user
    $stmt = $conn->prepare("SELECT SUM(angka_kredit) as total FROM konversi WHERE nip = ?");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalAngkaKredit = $result->fetch_assoc()['total'] ?? 0;

    // Format total angka kredit
    $totalAngkaKredit = number_format((float)$totalAngkaKredit, 2);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_data' => $totalData,
            'data_tahun_ini' => $dataThisYear,
            'total_angka_kredit' => $totalAngkaKredit
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching dashboard statistics: ' . $e->getMessage()
    ]);
}
?>