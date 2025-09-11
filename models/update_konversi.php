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

// Ambil data JSON dari request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Debug log
error_log("Update Konversi - Input received: " . print_r($data, true));
error_log("Update Konversi - NIP: " . $nip);

// Validasi input
if (!$data || !isset($data['row_key']) || !isset($data['field']) || !isset($data['value'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data tidak lengkap'
    ]);
    exit;
}

$row_key = $data['row_key'];
$field = $data['field'];
$new_value = trim($data['value']);

// PERBAIKAN: Parse row_key untuk mendapatkan tahun dan periode
$key_parts = explode('_', $row_key);
if (count($key_parts) < 2) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Format key tidak valid: ' . $row_key
    ]);
    exit;
}

$tahun = $key_parts[0];
// Gabungkan kembali bagian periode dan bersihkan multiple underscore
$periode_raw = implode('_', array_slice($key_parts, 1));
$periode_raw = preg_replace('/_{2,}/', ' - ', $periode_raw); // Replace multiple underscore dengan " - "
$periode_raw = str_replace('_', ' ', $periode_raw); // Replace single underscore dengan spasi

error_log("Parsed row_key: {$row_key} -> tahun: {$tahun}, periode: {$periode_raw}");

// PERBAIKAN: Query pencarian yang sederhana berdasarkan kolom yang ada
$find_sql = "SELECT * FROM nilai 
             WHERE nip = ? AND tahun = ? 
             AND (bulan LIKE ? OR periode LIKE ?)
             LIMIT 1";

$find_stmt = mysqli_prepare($conn, $find_sql);
if (!$find_stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare statement gagal: ' . mysqli_error($conn)
    ]);
    exit;
}

// Prepare search patterns
$periode_pattern = "%{$periode_raw}%";
mysqli_stmt_bind_param($find_stmt, "siss", $nip, $tahun, $periode_pattern, $periode_pattern);

// Validasi field yang diizinkan untuk diedit
$allowed_fields = ['predikat', 'persentase', 'koefisien', 'tahun', 'periode', 'bulan'];
if (!in_array($field, $allowed_fields)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Field tidak diizinkan untuk diedit: ' . $field
    ]);
    exit;
}

// Validasi nilai berdasarkan field
switch ($field) {
    case 'persentase':
        if (!is_numeric($new_value) || $new_value < 1 || $new_value > 12) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Persentase harus berupa angka antara 1-12'
            ]);
            exit;
        }
        $new_value = (int)$new_value;
        break;
        
    case 'koefisien':
        if (!is_numeric($new_value) || $new_value <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Koefisien harus berupa angka positif'
            ]);
            exit;
        }
        $new_value = (float)$new_value;
        break;
        
    case 'tahun':
        if (!is_numeric($new_value) || $new_value < 1900 || $new_value > 2100) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Tahun harus berupa angka antara 1900-2100'
            ]);
            exit;
        }
        $new_value = (int)$new_value;
        break;
        
    case 'periode':
    case 'bulan':
        if (empty($new_value)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Periode tidak boleh kosong'
            ]);
            exit;
        }
        // Validasi format periode
        $valid_months = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 
                        'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
        
        $periode_parts = explode(' - ', strtolower($new_value));
        foreach ($periode_parts as $month) {
            $month = trim($month);
            if (!in_array($month, $valid_months)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Format periode tidak valid. Gunakan nama bulan atau range bulan'
                ]);
                exit;
            }
        }
        break;
        
    case 'predikat':
        if (empty($new_value)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Predikat tidak boleh kosong'
            ]);
            exit;
        }
        break;
}

// Mulai transaction
mysqli_autocommit($conn, false);

try {
    // Eksekusi query pencarian
    mysqli_stmt_execute($find_stmt);
    $find_result = mysqli_stmt_get_result($find_stmt);
    
    if (mysqli_num_rows($find_result) === 0) {
        // Debug informasi
        error_log("Data tidak ditemukan untuk: NIP={$nip}, tahun={$tahun}, periode_pattern={$periode_pattern}");
        
        // Coba query debug
        $debug_sql = "SELECT nip, tahun, bulan, periode FROM nilai WHERE nip = ? AND tahun = ?";
        $debug_stmt = mysqli_prepare($conn, $debug_sql);
        mysqli_stmt_bind_param($debug_stmt, "si", $nip, $tahun);
        mysqli_stmt_execute($debug_stmt);
        $debug_result = mysqli_stmt_get_result($debug_stmt);
        
        $available_data = [];
        while ($row = mysqli_fetch_assoc($debug_result)) {
            $available_data[] = $row;
        }
        
        error_log("Data yang tersedia: " . print_r($available_data, true));
        
        throw new Exception('Data tidak ditemukan. Row key: ' . $row_key);
    }
    
    $existing_record = mysqli_fetch_assoc($find_result);
    error_log("Record ditemukan: " . print_r($existing_record, true));
    
    // Tentukan field database yang akan diupdate
    $update_fields = [];
    $params = [];
    $param_types = '';
    
    // Map field ke kolom database - SEDERHANA
    switch ($field) {
        case 'tahun':
            $update_fields[] = 'tahun = ?';
            $params[] = $new_value;
            $param_types .= 'i';
            break;
            
        case 'periode':
            // Update kolom periode dan bulan
            $update_fields[] = 'periode = ?';
            $update_fields[] = 'bulan = ?';
            $params[] = $new_value;
            $params[] = $new_value; // Set bulan sama dengan periode
            $param_types .= 'ss';
            break;
            
        case 'predikat':
            $update_fields[] = 'predikat = ?';
            $params[] = $new_value;
            $param_types .= 's';
            break;
            
        case 'persentase':
            $update_fields[] = 'persentase = ?';
            $params[] = $new_value;
            $param_types .= 'i';
            break;
            
        case 'koefisien':
            $update_fields[] = 'koefisien = ?';
            $params[] = $new_value;
            $param_types .= 'd';
            break;
    }
    
    // Hitung ulang angka_kredit jika persentase atau koefisien berubah
    $new_angka_kredit = null;
    if ($field === 'persentase' || $field === 'koefisien') {
        $current_persentase = ($field === 'persentase') ? $new_value : $existing_record['persentase'];
        $current_koefisien = ($field === 'koefisien') ? $new_value : $existing_record['koefisien'];
        
        $new_angka_kredit = ($current_persentase / 12) * $current_koefisien;
        
        $update_fields[] = 'angka_kredit = ?';
        $params[] = $new_angka_kredit;
        $param_types .= 'd';
    }
    
    // PERBAIKAN: Update berdasarkan kombinasi unik NIP, tahun, dan periode
    $update_sql = "UPDATE nilai SET " . implode(', ', $update_fields) . " 
                   WHERE nip = ? AND tahun = ? AND (bulan LIKE ? OR periode LIKE ?)";
    
    // Tambahkan parameter WHERE
    $params[] = $nip;
    $params[] = $tahun;
    $params[] = $periode_pattern;
    $params[] = $periode_pattern;
    $param_types .= 'siss';
    
    $update_stmt = mysqli_prepare($conn, $update_sql);
    if (!$update_stmt) {
        throw new Exception('Prepare statement gagal: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($update_stmt, $param_types, ...$params);
    
    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception('Update gagal: ' . mysqli_stmt_error($update_stmt));
    }
    
    $affected_rows = mysqli_stmt_affected_rows($update_stmt);
    
    if ($affected_rows === 0) {
        throw new Exception('Tidak ada data yang diupdate');
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    $response = [
        'status' => 'success',
        'message' => 'Data berhasil diupdate',
        'affected_rows' => $affected_rows
    ];
    
    if ($new_angka_kredit !== null) {
        $response['new_angka_kredit'] = number_format($new_angka_kredit, 3);
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    
    error_log("Update Konversi Error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    mysqli_autocommit($conn, true);
    
    if (isset($find_stmt)) mysqli_stmt_close($find_stmt);
    if (isset($update_stmt)) mysqli_stmt_close($update_stmt);
    if (isset($debug_stmt)) mysqli_stmt_close($debug_stmt);
}
?>