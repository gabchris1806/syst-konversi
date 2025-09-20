<?php
session_start();
include __DIR__ . '/../config/db.php';

// Set header JSON
header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['nip'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Anda harus login terlebih dahulu'
    ]);
    exit;
}

$nip = $_SESSION['nip'];

// Ambil row_key dari POST
$row_key = $_POST['row_key'] ?? '';

if (empty($row_key)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Row key tidak ditemukan'
    ]);
    exit;
}

// Debug: log row_key yang diterima
error_log("Received row_key: " . $row_key);

// Parse row_key untuk mendapatkan tahun dan periode/bulan
$key_parts = explode('_', $row_key);
if (count($key_parts) < 2) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Format key tidak valid: ' . $row_key
    ]);
    exit;
}

$tahun = $key_parts[0];
$periode_raw = implode('_', array_slice($key_parts, 1));

// Debug: log parsed values
error_log("Parsed tahun: " . $tahun);
error_log("Parsed periode_raw: " . $periode_raw);

// Kemungkinan format periode yang perlu dicoba berdasarkan data yang terlihat
$periode_formats = [
    $periode_raw,                                    // Format asli
    str_replace('_', ' - ', $periode_raw),          // Format dengan " - " (misal: Januari_Maret menjadi "Januari - Maret")
    str_replace('_', ' ', $periode_raw),            // Format dengan spasi biasa
    str_replace('_', '-', $periode_raw),            // Format dengan tanda "-"
    ucwords(str_replace('_', ' - ', strtolower($periode_raw))), // Kapitalisasi proper
];

// Mulai transaction
mysqli_autocommit($conn, false);

try {
    $data_found = false;
    $periode_matched = '';
    $found_record = null;
    
    // Debug: Cek semua data yang ada untuk NIP dan tahun ini
    $debug_sql = "SELECT * FROM nilai WHERE nip = ? AND tahun = ?";
    $debug_stmt = mysqli_prepare($conn, $debug_sql);
    mysqli_stmt_bind_param($debug_stmt, "ss", $nip, $tahun);
    mysqli_stmt_execute($debug_stmt);
    $debug_result = mysqli_stmt_get_result($debug_stmt);
    
    error_log("=== Debug: Data yang ada untuk NIP $nip, Tahun $tahun ===");
    while ($debug_row = mysqli_fetch_assoc($debug_result)) {
        error_log("Found record: " . json_encode($debug_row));
    }
    mysqli_stmt_close($debug_stmt);
    
    // Cari data dengan berbagai format periode
    foreach ($periode_formats as $index => $periode_test) {
        error_log("Trying periode format $index: '$periode_test'");
        
        // Coba cari di kolom bulan dulu, kemudian periode
        $check_sql = "SELECT * FROM nilai WHERE nip = ? AND tahun = ? AND (bulan = ? OR periode = ?)";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        
        if (!$check_stmt) {
            throw new Exception('Prepare check statement gagal: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($check_stmt, "ssss", $nip, $tahun, $periode_test, $periode_test);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $data_found = true;
            $periode_matched = $periode_test;
            $found_record = mysqli_fetch_assoc($check_result);
            error_log("Match found with periode: '$periode_matched'");
            error_log("Found record: " . json_encode($found_record));
            mysqli_stmt_close($check_stmt);
            break;
        }
        
        mysqli_stmt_close($check_stmt);
    }
    
    // Jika tidak ditemukan dengan format di atas, coba dengan LIKE untuk pencarian yang lebih fleksibel
    if (!$data_found) {
        error_log("Tidak ditemukan dengan format standar, mencoba dengan LIKE...");
        
        $like_searches = [
            '%' . str_replace('_', '%', $periode_raw) . '%',
            '%' . str_replace('_', ' ', $periode_raw) . '%',
            '%' . str_replace('_', '-', $periode_raw) . '%',
        ];
        
        foreach ($like_searches as $like_pattern) {
            error_log("Trying LIKE pattern: '$like_pattern'");
            
            $like_sql = "SELECT * FROM nilai WHERE nip = ? AND tahun = ? AND (bulan LIKE ? OR periode LIKE ?)";
            $like_stmt = mysqli_prepare($conn, $like_sql);
            
            if (!$like_stmt) {
                throw new Exception('Prepare LIKE statement gagal: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($like_stmt, "ssss", $nip, $tahun, $like_pattern, $like_pattern);
            mysqli_stmt_execute($like_stmt);
            $like_result = mysqli_stmt_get_result($like_stmt);
            
            if (mysqli_num_rows($like_result) > 0) {
                $data_found = true;
                $found_record = mysqli_fetch_assoc($like_result);
                $periode_matched = $found_record['bulan'] ?: $found_record['periode'];
                error_log("Match found with LIKE pattern: '$like_pattern'");
                error_log("Matched periode: '$periode_matched'");
                mysqli_stmt_close($like_stmt);
                break;
            }
            
            mysqli_stmt_close($like_stmt);
        }
    }
    
    if (!$data_found) {
        throw new Exception("Data tidak ditemukan untuk NIP: $nip, Tahun: $tahun, Row Key: $row_key. Periode yang dicoba: " . implode(' / ', $periode_formats));
    }
    
    // Delete record berdasarkan ID (lebih aman) atau kombinasi unik
    if (isset($found_record['id'])) {
        // Jika ada kolom ID, gunakan itu
        $delete_sql = "DELETE FROM nilai WHERE id = ? LIMIT 1";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "i", $found_record['id']);
    } else {
        // Jika tidak ada ID, gunakan kombinasi NIP, tahun, dan periode yang cocok
        $delete_sql = "DELETE FROM nilai WHERE nip = ? AND tahun = ? AND (bulan = ? OR periode = ?) LIMIT 1";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "ssss", $nip, $tahun, $periode_matched, $periode_matched);
    }
    
    if (!$delete_stmt) {
        throw new Exception('Prepare delete statement gagal: ' . mysqli_error($conn));
    }
    
    if (!mysqli_stmt_execute($delete_stmt)) {
        throw new Exception('Delete gagal: ' . mysqli_stmt_error($delete_stmt));
    }
    
    $affected_rows = mysqli_stmt_affected_rows($delete_stmt);
    
    if ($affected_rows === 0) {
        throw new Exception('Gagal menghapus data - tidak ada baris yang terpengaruh');
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    error_log("Data berhasil dihapus: " . json_encode($found_record));
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Data berhasil dihapus',
        'deleted_record' => [
            'nip' => $nip,
            'tahun' => $tahun,
            'periode' => $periode_matched
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);
    
    error_log("Error in delete_data.php: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    // Reset autocommit
    mysqli_autocommit($conn, true);
    
    // Close statements
    if (isset($delete_stmt)) mysqli_stmt_close($delete_stmt);
}
?>