<?php
session_start();
include __DIR__ . '/../config/db.php';

// Header JSON
header('Content-Type: application/json');

// Pastikan user login
if (!isset($_SESSION['nip'])) {
    echo json_encode([
        'status' => 'error',
        'table_data' => '<tr><td colspan="7" style="color:red;">Anda harus login terlebih dahulu</td></tr>'
    ]);
    exit;
}

$nip = $_SESSION['nip'];

// Debug: Log semua data POST yang diterima
error_log("POST Data received in load_form2: " . print_r($_POST, true));

// Ambil tahun dari POST dengan berbagai kemungkinan nama field
$tahun_pilih = $_POST['tahun_pilih'] ?? $_POST['tahun'] ?? $_GET['tahun_pilih'] ?? $_GET['tahun'] ?? '';

// Debug: Log tahun yang diterima
error_log("Tahun pilih received in load_form2: " . $tahun_pilih);

// Validasi tahun dengan lebih fleksibel
if (empty($tahun_pilih)) {
    echo json_encode([
        'status' => 'error',
        'table_data' => '<tr><td colspan="7">Tahun tidak dipilih. Data POST: ' . json_encode($_POST) . '</td></tr>',
        'debug_info' => [
            'post_data' => $_POST,
            'get_data' => $_GET,
            'received_tahun' => $tahun_pilih
        ]
    ]);
    exit;
}

// Bersihkan dan validasi tahun
$tahun_pilih = trim($tahun_pilih);

// Cek apakah tahun dalam format "2024/2025" dan ambil tahun pertama
if (strpos($tahun_pilih, '/') !== false) {
    $tahun_parts = explode('/', $tahun_pilih);
    $tahun_pilih = trim($tahun_parts[0]);
}

// Validasi numeric setelah pembersihan
if (!is_numeric($tahun_pilih) || strlen($tahun_pilih) !== 4 || $tahun_pilih < 2000 || $tahun_pilih > 2050) {
    echo json_encode([
        'status' => 'error',
        'table_data' => '<tr><td colspan="7">Tahun tidak valid. Harus berupa angka 4 digit (2000-2050). Diterima: "' . htmlspecialchars($tahun_pilih) . '"</td></tr>',
        'debug_info' => [
            'original_input' => $_POST['tahun_pilih'] ?? 'not set',
            'processed_tahun' => $tahun_pilih,
            'is_numeric' => is_numeric($tahun_pilih),
            'length' => strlen($tahun_pilih)
        ]
    ]);
    exit;
}

$tahun_pilih = (int)$tahun_pilih;
$tahun_berikutnya = $tahun_pilih + 1;

// Updated query for academic year: April current year to March next year
$sql = "SELECT * FROM nilai
        WHERE nip = ? 
        AND (
            (tahun = ? AND (
                bulan IN ('April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember')
                OR periode REGEXP 'April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember'
                OR (bulan REGEXP '^[0-9]+$' AND CAST(bulan AS UNSIGNED) BETWEEN 4 AND 12)
            ))
            OR 
            (tahun = ? AND (
                bulan IN ('Januari', 'Februari', 'Maret')
                OR periode REGEXP 'Januari|Februari|Maret'
                OR (bulan REGEXP '^[0-9]+$' AND CAST(bulan AS UNSIGNED) BETWEEN 1 AND 3)
            ))
        )
        ORDER BY 
            CASE 
                WHEN tahun = ? AND bulan IN ('April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember') THEN 1
                WHEN tahun = ? AND bulan IN ('Januari', 'Februari', 'Maret') THEN 2
                ELSE 3
            END,
            CASE bulan
                WHEN 'April' THEN 4
                WHEN 'Mei' THEN 5
                WHEN 'Juni' THEN 6
                WHEN 'Juli' THEN 7
                WHEN 'Agustus' THEN 8
                WHEN 'September' THEN 9
                WHEN 'Oktober' THEN 10
                WHEN 'November' THEN 11
                WHEN 'Desember' THEN 12
                WHEN 'Januari' THEN 1
                WHEN 'Februari' THEN 2
                WHEN 'Maret' THEN 3
                ELSE 99
            END";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'table_data' => '<tr><td colspan="7" style="color:red;">Query error: ' . mysqli_error($conn) . '</td></tr>'
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "siiii", $nip, $tahun_pilih, $tahun_berikutnya, $tahun_pilih, $tahun_berikutnya);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode([
        'status' => 'error',
        'table_data' => '<tr><td colspan="7" class="no-data-message">
            <div style="text-align: center; padding: 20px;">
                <h4 style="margin-bottom: 10px;">Data tidak ditemukan</h4>
                <p>Tidak ada data untuk tahun akademik <strong>' . $tahun_pilih . '/' . $tahun_berikutnya . '</strong></p>
                <p style="font-size: 14px; color: #666;">
                    (Periode: April ' . $tahun_pilih . ' - Maret ' . $tahun_berikutnya . ')
                </p>
            </div>
        </td></tr>'
    ]);
    exit;
}

$table_data = '';
$total_angka_kredit = 0;
$total_koefisien = 0;
$count = 0;

while ($row = mysqli_fetch_assoc($result)) {
    // Handle periode berdasarkan kolom yang ada di database
    $periode = '';
    
    // Prioritas: gunakan kolom periode jika ada dan tidak kosong
    if (!empty($row['periode'])) {
        $periode = $row['periode'];
    } 
    // Fallback ke kolom bulan jika periode kosong
    else if (!empty($row['bulan'])) {
        $periode = $row['bulan'];
    }
    else {
        $periode = 'Tidak Diketahui';
    }
    
    // Pastikan format periode konsisten (huruf pertama kapital)
    $periode = ucfirst(strtolower(trim($periode)));
    
    // Jika periode dalam format "april - desember", pastikan formatnya benar
    if (strpos($periode, ' - ') !== false || strpos($periode, '-') !== false) {
        $periode = preg_replace('/\s*-\s*/', ' - ', $periode);
        $periode_parts = explode(' - ', $periode);
        if (count($periode_parts) == 2) {
            $periode = ucfirst(trim($periode_parts[0])) . ' - ' . ucfirst(trim($periode_parts[1]));
        }
    }

    $persentase = $row['persentase'] ?? ($row['prosentase'] ?? '0');
    
    // Create unique row key for editing (tahun_periode_id)
    $periode_clean = preg_replace('/[^a-zA-Z0-9]/', '_', $periode);
    $row_key = $row['tahun'] . '_' . $periode_clean . '_' . ($row['id'] ?? time());
    
    // Display tahun asli dari database (bukan format akademik)
    $display_tahun = $row['tahun'];
    
    $table_data .= "<tr data-row-key='{$row_key}' data-academic-year='{$tahun_pilih}/{$tahun_berikutnya}'>
        <td class='editable-field' data-field='tahun' title='Tahun: {$display_tahun}'>{$display_tahun}</td>
        <td class='editable-field' data-field='periode' title='Klik untuk edit periode'>{$periode}</td>
        <td class='editable-field' data-field='predikat' title='Klik untuk edit predikat'>{$row['predikat']}</td>
        <td class='editable-field' data-field='persentase' title='Klik untuk edit persentase'>{$persentase}/12</td>
        <td class='editable-field' data-field='koefisien' title='Klik untuk edit koefisien'>" . number_format($row['koefisien'], 2) . "</td>
        <td class='calculated-field' title='Angka kredit dihitung otomatis'>" . number_format($row['angka_kredit'], 3) . "</td>
        <td class='action-cell' style='text-align: center;'>
            <button type='button' class='delete-row-btn' onclick='deleteKonversiData(\"{$row_key}\")' 
                    title='Hapus data periode {$periode}' style='font-size: 14px;'>
                🗑️
            </button>
        </td>
    </tr>";

    $total_angka_kredit += floatval($row['angka_kredit']);
    $total_koefisien += floatval($row['koefisien']);
    $count++;
}

$koefisien_per_tahun = $count > 0 ? $total_koefisien : 0;
$rata_rata_koefisien = $count > 0 ? $total_koefisien / $count : 0;

echo json_encode([
    'status' => 'success',
    'table_data' => $table_data,
    'summary_data' => [
        'koefisien_per_tahun' => number_format($koefisien_per_tahun, 2),
        'rata_rata_koefisien' => number_format($rata_rata_koefisien, 2),
        'angka_kredit_yang_didapat' => number_format($total_angka_kredit, 3),
        'jumlah_periode' => $count,
        'tahun_akademik' => $tahun_pilih . '/' . $tahun_berikutnya
    ],
    'period_info' => [
        'start_period' => 'April ' . $tahun_pilih,
        'end_period' => 'Maret ' . $tahun_berikutnya,
        'academic_year' => $tahun_pilih . '/' . $tahun_berikutnya
    ]
]);
?>