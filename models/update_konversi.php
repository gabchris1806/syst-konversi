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

// PERBAIKAN: Parse row_key yang menggunakan format dari load_form2.php
// Format row_key: tahun_periode_with_triple_underscore_timestamp
// Contoh: 2024_April___Desember_1757651534

// Split berdasarkan triple underscore untuk memisahkan periode range
$triple_underscore_parts = explode('___', $row_key);
if (count($triple_underscore_parts) < 2) {
    // Fallback ke parsing underscore biasa
    $key_parts = explode('_', $row_key);
    if (count($key_parts) < 2) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Format key tidak valid: ' . $row_key
        ]);
        exit;
    }
    $tahun = $key_parts[0];
    $periode_formatted = str_replace('_', ' ', implode('_', array_slice($key_parts, 1, -1)));
} else {
    // Parse format dengan triple underscore
    $first_part = $triple_underscore_parts[0]; // tahun_bulan_pertama
    $second_part = $triple_underscore_parts[1]; // bulan_kedua_timestamp
    
    // Extract tahun dari bagian pertama
    $first_parts = explode('_', $first_part);
    $tahun = $first_parts[0];
    $bulan_pertama = implode(' ', array_slice($first_parts, 1));
    
    // Extract bulan kedua dari bagian kedua (hapus timestamp dari akhir)
    $second_parts = explode('_', $second_part);
    // Hapus timestamp (angka) dari akhir
    while (count($second_parts) > 0 && is_numeric(end($second_parts))) {
        array_pop($second_parts);
    }
    $bulan_kedua = implode(' ', $second_parts);
    
    // Format periode
    if (!empty($bulan_kedua)) {
        $periode_formatted = $bulan_pertama . ' - ' . $bulan_kedua;
    } else {
        $periode_formatted = $bulan_pertama;
    }
}

error_log("Parsed row_key: {$row_key} -> tahun: {$tahun}, periode: {$periode_formatted}");

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
        
        $periode_parts_validate = explode(' - ', strtolower($new_value));
        foreach ($periode_parts_validate as $month) {
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
    // PERBAIKAN: Query pencarian yang lebih spesifik berdasarkan struktur row_key
    // Coba beberapa kemungkinan pencarian
    $find_queries = [];
    
    // Query 1: Berdasarkan periode yang diformat
    $find_queries[] = [
        'sql' => "SELECT * FROM nilai WHERE nip = ? AND tahun = ? AND (bulan = ? OR periode = ? OR bulan LIKE ? OR periode LIKE ?) LIMIT 1",
        'params' => [$nip, $tahun, $periode_formatted, $periode_formatted, "%{$periode_formatted}%", "%{$periode_formatted}%"],
        'types' => 'sissss'
    ];
    
    // Query 2: Berdasarkan timestamp jika ada kolom untuk itu
    if (is_numeric($tahun)) {
        $find_queries[] = [
            'sql' => "SELECT * FROM nilai WHERE nip = ? AND tahun = ? AND id = ? LIMIT 1",
            'params' => [$nip, $tahun],
            'types' => 'sii'
        ];
    }
    
    // Query 3: Fallback dengan pattern matching yang lebih luas
    $periode_parts_search = explode(' - ', $periode_formatted);
    if (count($periode_parts_search) >= 2) {
        $start_month = trim($periode_parts_search[0]);
        $end_month = trim($periode_parts_search[count($periode_parts_search) - 1]);
        
        $find_queries[] = [
            'sql' => "SELECT * FROM nilai WHERE nip = ? AND tahun = ? AND (bulan LIKE ? OR periode LIKE ?) LIMIT 1",
            'params' => [$nip, $tahun, "%{$start_month}%", "%{$end_month}%"],
            'types' => 'ssss'
        ];
    }
    
    $existing_record = null;
    $successful_query = null;
    
    foreach ($find_queries as $query_info) {
        $find_stmt = mysqli_prepare($conn, $query_info['sql']);
        if (!$find_stmt) {
            continue;
        }
        
        mysqli_stmt_bind_param($find_stmt, $query_info['types'], ...$query_info['params']);
        mysqli_stmt_execute($find_stmt);
        $find_result = mysqli_stmt_get_result($find_stmt);
        
        if (mysqli_num_rows($find_result) > 0) {
            $existing_record = mysqli_fetch_assoc($find_result);
            $successful_query = $query_info;
            mysqli_stmt_close($find_stmt);
            break;
        }
        
        mysqli_stmt_close($find_stmt);
    }
    
    if (!$existing_record) {
        // Debug informasi
        error_log("Data tidak ditemukan untuk: NIP={$nip}, tahun={$tahun}, periode={$periode_formatted}");
        
        // Coba query debug
        $debug_sql = "SELECT id, nip, tahun, bulan, periode FROM nilai WHERE nip = ? AND tahun = ?";
        $debug_stmt = mysqli_prepare($conn, $debug_sql);
        mysqli_stmt_bind_param($debug_stmt, "si", $nip, $tahun);
        mysqli_stmt_execute($debug_stmt);
        $debug_result = mysqli_stmt_get_result($debug_stmt);
        
        $available_data = [];
        while ($row = mysqli_fetch_assoc($debug_result)) {
            $available_data[] = $row;
        }
        mysqli_stmt_close($debug_stmt);
        
        error_log("Data yang tersedia: " . print_r($available_data, true));
        
        throw new Exception('Data tidak ditemukan. Row key: ' . $row_key . ', Periode: ' . $periode_formatted);
    }
    
    error_log("Record ditemukan: " . print_r($existing_record, true));
    
    // Tentukan field database yang akan diupdate
    $update_fields = [];
    $params = [];
    $param_types = '';
    
    // Map field ke kolom database
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
            
        case 'bulan':
            // Update kolom bulan dan periode
            $update_fields[] = 'bulan = ?';
            $update_fields[] = 'periode = ?';
            $params[] = $new_value;
            $params[] = $new_value; // Set periode sama dengan bulan
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
    
    // PERBAIKAN: Update berdasarkan ID yang unik
    if (isset($existing_record['id'])) {
        $update_sql = "UPDATE nilai SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $params[] = $existing_record['id'];
        $param_types .= 'i';
    } else {
        // Fallback ke kombinasi NIP, tahun, dan periode
        $update_sql = "UPDATE nilai SET " . implode(', ', $update_fields) . " 
                       WHERE nip = ? AND tahun = ? AND (bulan = ? OR periode = ?)";
        $params[] = $nip;
        $params[] = $tahun;
        $params[] = $periode_formatted;
        $params[] = $periode_formatted;
        $param_types .= 'siss';
    }
    
    $update_stmt = mysqli_prepare($conn, $update_sql);
    if (!$update_stmt) {
        throw new Exception('Prepare statement gagal: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($update_stmt, $param_types, ...$params);
    
    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception('Update gagal: ' . mysqli_stmt_error($update_stmt));
    }
    
    $affected_rows = mysqli_stmt_affected_rows($update_stmt);
    mysqli_stmt_close($update_stmt);
    
    if ($affected_rows === 0) {
        throw new Exception('Tidak ada data yang diupdate');
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    $response = [
        'status' => 'success',
        'message' => 'Data berhasil diupdate',
        'affected_rows' => $affected_rows,
        'debug_info' => [
            'row_key' => $row_key,
            'tahun' => $tahun,
            'periode' => $periode_formatted,
            'field' => $field,
            'new_value' => $new_value
        ]
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
        'message' => $e->getMessage(),
        'debug_info' => [
            'row_key' => $row_key,
            'parsed_tahun' => $tahun ?? 'N/A',
            'parsed_periode' => $periode_formatted ?? 'N/A',
            'field' => $field ?? 'N/A',
            'new_value' => $new_value ?? 'N/A'
        ]
    ]);
} finally {
    mysqli_autocommit($conn, true);
}
?>