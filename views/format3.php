<?php
session_start();
include __DIR__ . '/../config/db.php';

// Cek login user
if (!isset($_SESSION['nip'])) {
    header("Location: login.php");
    exit();
}

// Redirect admin ke admin dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$nip = $_SESSION['nip'];

// Ambil data user dari database - FIXED SQL INJECTION
$stmt = $conn->prepare("SELECT * FROM pegawai WHERE nip = ?");
$stmt->bind_param("s", $nip);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: login.php");
    exit();
}

// TAMBAHKAN VARIABEL CURRENT_YEAR
$current_year = date('Y'); // Mendapatkan tahun saat ini
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-left">
            <img src="../assets/images/Logo_PTKI_Medan.png" class="logo" alt="Logo">
            <span class="app-title">SISTEM KONVERSI ANGKA KREDIT</span>
            <div class="nav-links">
                <a href="../controllers/dashboard.php" class="nav-link">Dashboard</a>
                <a href="format1.php" class="nav-link">Input Data</a>
                <a href="format2.php" class="nav-link">Daftar Konversi</a>
                <a href="format3.php" class="nav-link">Rekap Konversi</a>
            </div>
        </div>
        <div class="navbar-right">
            <div class="profile-menu">
                <img src="../assets/images/profile.png" class="profile" alt="Profile">
                <div class="dropdown">
                    <a href="edit_profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="user-nip"><?php echo htmlspecialchars($user['nip'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    </nav>
    <div class="form-content" id="format2">
        <div class="form-content" id="format3">
            <h2>Format 3 - Laporan</h2>
            
            <div class="year-selector-container">
                <label for="tahun_pilih_f3">Tahun:</label>
                <select id="tahun_pilih_f3" required>
                    <option value="">Pilih Tahun</option>
                    <?php
                    // PERBAIKI LOOP TAHUN - TAMBAHKAN SELECTED UNTUK TAHUN SEKARANG
                    for ($i = 2020; $i <= $current_year + 5; $i++) {
                        $selected = ($i == $current_year) ? 'selected' : '';
                        echo "<option value='".$i."' ".$selected.">".$i."</option>";
                    }
                    ?>
                </select>
                <button type="button" id="btn-lihat-f3" class="btn-lihat">Lihat</button>
            </div>

            <!-- Tabel Format 3 - HASIL PENILAIAN ANGKA KREDIT -->
            <div class="format3-container" id="format3-container" style="display:none;">
                <h3 style="text-align: center; margin: 20px 0; font-weight: bold;">HASIL PENILAIAN ANGKA KREDIT</h3>
                
                <div class="table-controls">
                    <button type="button" class="add-row-button" onclick="addPerformanceRow()" title="Tambah Baris">
                        +
                    </button>
                </div>

                <!-- Rest of your HTML table code remains the same -->
                <table class="format3-table" id="main-performance-table">
                    <thead>
                        <tr>
                            <th>II</th>
                            <th>HASIL PENILAIAN KINERJA</th>
                            <th>LAMA</th>
                            <th>BARU</th>
                            <th>JUMLAH</th>
                            <th>KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody id="performance-table-body">
                        <!-- Your existing table rows here -->
                        <tr data-row-id="1">
                            <td class="row-number">1</td>
                            <td style="text-align: left; padding-left: 10px;" class="row-description">
                                <div class="description-container">
                                    <span class="description-text">AK Dasar yang diberikan</span>
                                </div>
                            </td>
                            <td class="editable-cell" data-type="ak_dasar_lama">-</td>
                            <td class="editable-cell" data-type="ak_dasar_baru">50.00</td>
                            <td class="calculated-cell" data-type="ak_dasar_jumlah">50.00</td>
                            <td class="editable-cell keterangan-cell" data-type="keterangan_1" contenteditable="true">-</td>
                        </tr>
                        <!-- Add your other existing rows here... -->
                        <tr data-row-id="2">
                            <td class="row-number">2</td>
                            <td style="text-align: left; padding-left: 10px;" class="row-description">
                                <div class="description-container">
                                    <span class="description-text">AK JF Lama</span>
                                </div>
                            </td>
                            <td class="editable-cell" data-type="ak_jf_lama">-</td>
                            <td class="editable-cell" data-type="ak_jf_baru">0.00</td>
                            <td class="calculated-cell" data-type="ak_jf_jumlah">0.00</td>
                            <td class="editable-cell keterangan-cell" data-type="keterangan_2" contenteditable="true">-</td>
                        </tr>
                        <!-- Continue with other rows... -->
                        <tr data-row-id="3">
                            <td class="row-number">3</td>
                            <td style="text-align: left; padding-left: 10px;" class="row-description">
                                <div class="description-container">
                                    <span class="description-text">AK Penyesuaian/ Penyetaraan</span>
                                </div>
                            </td>
                            <td class="editable-cell" data-type="ak_penyesuaian_lama">-</td>
                            <td class="editable-cell" data-type="ak_penyesuaian_baru">0.00</td>
                            <td class="calculated-cell" data-type="ak_penyesuaian_jumlah">0.00</td>
                            <td class="editable-cell keterangan-cell" data-type="keterangan_3" contenteditable="true">-</td>
                        </tr>
                        <tr data-row-id="4">
                            <td class="row-number">4</td>
                            <td style="text-align: left; padding-left: 10px;" class="row-description">
                                <div class="description-container">
                                    <span class="description-text">AK Konversi</span>
                                </div>
                            </td>
                            <td class="editable-cell" data-type="ak_konversi_lama">-</td>
                            <td class="editable-cell" data-type="ak_konversi_baru" id="ak_konversi_from_form1">0.00</td>
                            <td class="calculated-cell" data-type="ak_konversi_jumlah">0.00</td>
                            <td class="editable-cell keterangan-cell" data-type="keterangan_4" contenteditable="true">-</td>
                        </tr>
                        <tr data-row-id="5">
                            <td class="row-number">5</td>
                            <td style="text-align: left; padding-left: 10px;" class="row-description">
                                <div class="description-container">
                                    <span class="description-text">AK yang diperoleh dari peningkatan pendidikan</span>
                                </div>
                            </td>
                            <td class="editable-cell" data-type="ak_pendidikan_lama">-</td>
                            <td class="editable-cell" data-type="ak_pendidikan_baru">-</td>
                            <td class="calculated-cell" data-type="ak_pendidikan_jumlah">0.00</td>
                            <td class="editable-cell keterangan-cell" data-type="keterangan_5" contenteditable="true">-</td>
                        </tr>
                        <tr data-row-id="6">
                            <td class="row-number">6</td>
                            <td style="text-align: left; padding-left: 10px;" class="row-description">
                                <div class="description-container">
                                    <span class="description-text editable-description">................ **)</span>
                                    <button type="button" class="remove-row-btn" onclick="removePerformanceRow(6)">×</button>
                                </div>
                            </td>
                            <td class="editable-cell" data-type="ak_lainnya_lama">-</td>
                            <td class="editable-cell" data-type="ak_lainnya_baru">-</td>
                            <td class="calculated-cell" data-type="ak_lainnya_jumlah">0.00</td>
                            <td class="editable-cell keterangan-cell" data-type="keterangan_6" contenteditable="true">-</td>
                        </tr>
                        <!-- BARIS TOTAL -->
                        <tr class="total-row" style="font-weight: bold; background-color: #007bff !important; color: white !important;">
                            <td style="background-color: #007bff !important; border: 1px solid #007bff;"></td>
                            <td colspan="1" style="text-align: left; padding-left: 10px; background-color: #007bff !important; color: white !important; border: 1px solid #007bff;">
                                JUMLAH ANGKA KREDIT KUMULATIF
                            </td>
                            <td id="total_lama_kumulatif" style="background-color: #007bff !important; text-align: center; border: 1px solid #007bff;">0.00</td>
                            <td id="total_baru_kumulatif" style="background-color: #007bff !important; text-align: center; border: 1px solid #007bff;">50.00</td>
                            <td id="total_jumlah_kumulatif" style="background-color: #007bff !important; text-align: center; border: 1px solid #007bff;">50.00</td>
                            <td id="total_" style="background-color: #007bff !important; text-align: center; border: 1px solid #007bff;"></td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- TABEL KEDUA & rest of content remains the same -->
                <table class="format3-table" style="margin-top: 30px;">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Keterangan</th>
                            <th style="width: 25%;">Pangkat</th>
                            <th style="width: 25%;">Jenjang Jabatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: left; padding: 12px;">Angka Kredit minimal yang harus dipenuhi untuk kenaikan pangkat/ jenjang</td>
                            <td class="editable-cell" data-type="ak_minimal_pangkat">50</td>
                            <td class="editable-cell" data-type="ak_minimal_jenjang">50</td>
                        </tr>
                        <tr>
                            <td id="keterangan-pangkat" style="text-align: left; padding: 12px;">Kelebihan/ <span style="text-decoration: line-through;">kekurangan</span> <sup>**)</sup> Angka Kredit yang harus dicapai untuk kenaikan pangkat</td>
                            <td class="calculated-cell" data-type="kelebihan_pangkat">12.500</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td id="keterangan-jenjang" style="text-align: left; padding: 12px;">Kelebihan/<span style="text-decoration: line-through;">kekurangan</span> <sup>**)</sup> Angka Kredit yang harus dicapai untuk peningkatan jenjang</td>
                            <td></td>
                            <td class="calculated-cell" data-type="kelebihan_jenjang">12.500</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="colspan-row" style="padding: 12px; font-weight: bold;">DAPAT/TIDAK DAPAT <sup>**)</sup> DIPERTIMBANGKAN UNTUK PENGANGKATAN PERTAMA PNS DALAM JABATAN DOSEN ASISTEN AHLI</td>
                        </tr>
                    </tbody>
                </table>

                <!-- BOTTOM SECTION -->
                <div class="format3-bottom-section no-print">
                    <div class="format3-notes-section no-print">
                        <h4>Keterangan:</h4>
                        <p>**) Diisi dengan angka kredit yang diperoleh dari hasil konversi</p>
                        <p>***) Diisi dengan jenis kegiatan lainnya yang dapat dinilai angka kreditnya</p>
                        
                        <div class="format3-actions no-print" style="text-align: right; margin: 20px 0;">
                            <button type="button" class="report-btn btn-print" onclick="cetakLaporan()" style="padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 14px;">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 8px; vertical-align: middle;">
                                    <path d="M19 8h-1V3H6v5H5c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h2v3h10v-3h2c1.1 0 2-.9 2-2v-6c0-1.1-.9-2-2-2zM8 5h8v3H8V5zm8 12v2H8v-2h8zm2-2v-2H6v2H6v-2H4v-2h16v2h-2v2z"/>
                                    <rect x="6" y="11" width="12" height="2"/>
                                </svg>
                                Cetak Laporan
                            </button>
                        </div>
                    </div>

                    <div class="format3-warning" style="display: none; background: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 5px; margin-top: 15px;">
                        <span style="color: #856404; font-weight: bold;">
                            Laporan ini dibuat berdasarkan data konversi yang telah diinput untuk tahun <span id="current-year-display"><?php echo $current_year; ?></span>
                        </span>
                    </div>
                </div>
            </div>    
        </div>
    </div>

    <script>
        $(document).ready(function() {
            console.log("Page loaded. Current year: <?php echo $current_year; ?>");
            console.log("Year dropdown options:", $("#tahun_pilih_f3 option").length);
            
            // Check if dropdown has options
            if ($("#tahun_pilih_f3 option").length <= 1) {
                console.error("Year dropdown has no options! Check PHP code.");
            }
        });

        function cetakLaporan() {
            const tahun = document.getElementById("tahun_pilih_f3").value;
            
            if (!tahun) {
                alert("Silakan pilih tahun terlebih dahulu!");
                return;
            }

            // Generate current date in Indonesian format
            const now = new Date();
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            
            const day = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();
            
            const formattedDate = `${day} ${month} ${year}`;
            
            // Get user data from session (available in the DOM)
            const userName = document.querySelector('.user-name').textContent;
            const userNip = document.querySelector('.user-nip').textContent;
            
            // CREATE USER IDENTITY TABLE AND ENHANCED HEADER
            const headerHTML = `
                <div class="print-header" id="temp-print-header" style="margin-bottom: 30px; page-break-inside: avoid;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h2 style="margin: 0; font-size: 16px; font-weight: bold; display: block !important;">PENETAPAN ANGKA KREDIT</h2>
                        <p style="margin: 5px 0; font-size: 14px;">NOMOR : B/   /BPSDMI/PTKI/KP/I/${tahun}</p>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <p style="margin: 2px 0; font-size: 14px;"><strong>Instansi :</strong> Kementerian Perindustrian</p>
                        </div>
                        <div style="flex: 1; text-align: right;">
                            <p style="margin: 2px 0; font-size: 14px;"><strong>Masa Penilaian :</strong> Periode ${tahun}</p>
                        </div>
                    </div>
                    
                    <!-- USER IDENTITY TABLE -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px; border: 2px solid #000;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #000; padding: 8px; background-color: #f0f0f0; text-align: center; font-weight: bold; font-size: 14px;" colspan="2">
                                    I. KETERANGAN PERORANGAN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; width: 25%; font-size: 13px; font-weight: bold;">1. Nama</td>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px;">: ${userName}</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px; font-weight: bold;">2. NIP</td>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px;">: ${userNip}</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px; font-weight: bold;">3. Nomor Seri KARPEG</td>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px;" id="karpeg-cell">: <span id="karpeg-data">-</span></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px; font-weight: bold;">4. Tempat/Tanggal Lahir</td>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px;" id="ttl-cell">: <span id="ttl-data">-</span></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px; font-weight: bold;">5. Jenis Kelamin</td>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px;" id="gender-cell">: <span id="gender-data">-</span></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px; font-weight: bold;">6. Pangkat/Golongan Ruang/TMT</td>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px;" id="pangkat-cell">: <span id="pangkat-data">-</span></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px; font-weight: bold;">7. Jabatan/TMT</td>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px;" id="jabatan-cell">: <span id="jabatan-data">-</span></td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px; font-weight: bold;">8. Unit Kerja</td>
                                <td style="border: 1px solid #000; padding: 8px; font-size: 13px;" id="unit-cell">: <span id="unit-data">-</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
            
            // CREATE SIGNATURE ELEMENT
            const signatureHTML = `
                <div class="signature-section" id="temp-signature-section" style="margin-top: 50px; display: flex; justify-content: space-between; padding: 0 50px; page-break-inside: avoid;">
                    <div class="left-signature" style="text-align: left; width: 300px;">
                        <div style="font-size: 12px; margin-bottom: 5px;">
                        </div>
                    </div>
                    <div class="right-signature" style="text-align: left; width: 300px;">
                        <div class="signature-location-date" style="font-size: 14px; margin-bottom: 5px;">
                            Ditetapkan di Medan<br>
                            Pada tanggal ${formattedDate}
                        </div>
                        <div class="signature-title" style="font-size: 14px; font-weight: normal; margin-bottom: 10px;">
                            Pejabat Penilai Kinerja
                        </div>
                        <div style="height: 60px;"></div>
                        <div class="signature-name" style="font-size: 14px; font-weight: bold; margin-bottom: 2px; text-decoration: underline;">
                            Dr. Poltak Evencus Hutajulu, S.T., M.T.
                        </div>
                        <div class="signature-nip" style="font-size: 12px;">
                            NIP. 198211220080301001
                        </div>
                    </div>
                </div>
            `;
            
            // Remove temporary elements if they exist
            const oldHeader = document.getElementById('temp-print-header');
            const oldSignature = document.getElementById('temp-signature-section');
            const oldFinalNote = document.getElementById('temp-final-note');
            
            if (oldHeader) oldHeader.remove();
            if (oldSignature) oldSignature.remove();
            if (oldFinalNote) oldFinalNote.remove();
            
            // Hide elements that shouldn't be printed
            const keteranganSection = document.querySelector('.format3-bottom-section');
            const originalDisplay = keteranganSection ? keteranganSection.style.display : '';
            if (keteranganSection) {
                keteranganSection.style.display = 'none';
            }
            
            // Add header and signature
            const format3Container = document.querySelector('.format3-container');
            if (format3Container) {
                format3Container.insertAdjacentHTML('afterbegin', headerHTML);
                format3Container.insertAdjacentHTML('beforeend', signatureHTML);
                
                // Update the main table title
                const mainTableTitle = format3Container.querySelector('h3');
                if (mainTableTitle && mainTableTitle.textContent.includes('HASIL PENILAIAN ANGKA KREDIT')) {
                    mainTableTitle.style.textAlign = 'center';
                    mainTableTitle.style.margin = '20px 0';
                    mainTableTitle.style.fontSize = '16px';
                    mainTableTitle.style.fontWeight = 'bold';
                }
            }
            
            // Load user data from server
            loadUserDataForPrint();
            
            // Wait for user data to load, then print
            setTimeout(() => {
                window.print();
            }, 1000);

            // Cleanup after printing
            setTimeout(() => {
                const tempHeader = document.getElementById('temp-print-header');
                const tempSignature = document.getElementById('temp-signature-section');
                const tempFinalNote = document.getElementById('temp-final-note');
                
                if (tempHeader) tempHeader.remove();
                if (tempSignature) tempSignature.remove();
                if (tempFinalNote) tempFinalNote.remove();
                
                // Restore keterangan section
                if (keteranganSection) {
                    keteranganSection.style.display = originalDisplay;
                }
            }, 2000);
        }

        // ===== FUNCTION TO LOAD USER DATA FOR PRINT =====
        function loadUserDataForPrint() {
            const userNip = document.querySelector('.user-nip').textContent;
            
            $.ajax({
                url: 'get_user_data.php',
                type: 'POST',
                data: { nip: userNip },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const userData = response.data;
                        
                        // Update user identity table cells
                        if (userData.no_seri_karpeg && userData.no_seri_karpeg !== 'NULL') {
                            document.getElementById('karpeg-data').textContent = userData.no_seri_karpeg;
                        }
                        
                        if (userData.tempat_tanggal_lahir && userData.tempat_tanggal_lahir !== 'NULL') {
                            document.getElementById('ttl-data').textContent = userData.tempat_tanggal_lahir;
                        }
                        
                        if (userData.jenis_kelamin && userData.jenis_kelamin !== 'NULL') {
                            document.getElementById('gender-data').textContent = userData.jenis_kelamin;
                        }
                        
                        if (userData.pangkat_golongan_tmt && userData.pangkat_golongan_tmt !== 'NULL') {
                            document.getElementById('pangkat-data').textContent = userData.pangkat_golongan_tmt;
                        }
                        
                        if (userData.jabatan_tmt && userData.jabatan_tmt !== 'NULL') {
                            document.getElementById('jabatan-data').textContent = userData.jabatan_tmt;
                        }
                        
                        if (userData.unit_kerja && userData.unit_kerja !== 'NULL') {
                            document.getElementById('unit-data').textContent = userData.unit_kerja;
                        }
                        
                        console.log('User data loaded for print:', userData);
                    } else {
                        console.log('Failed to load user data:', response.message);
                    }
                },
                error: function() {
                    console.log('Error loading user data for print');
                }
            });
        }

        // ===== ADD/REMOVE ROWS FUNCTIONS WITH UPDATED KETERANGAN =====
        function addPerformanceRow() {
            // Calculate next row number based on existing rows (not global counter)
            const currentRows = $('#performance-table-body tr:not(.total-row)').length;
            const newRowNumber = currentRows + 1;
            
            // Create new row with proper column structure - insert BEFORE the last row (................ **)
            const newRow = `<tr data-row-id="${newRowNumber}">
                <td class="row-number">${newRowNumber}</td>
                <td style="text-align: left; padding-left: 10px;" class="row-description">
                    <div class="description-container">
                        <span class="description-text editable-description" onclick="editDescription(this)">Item Baru</span>
                        <button type="button" class="remove-row-btn" onclick="removePerformanceRow(${newRowNumber})">×</button>
                    </div>
                </td>
                <td class="editable-cell" data-type="ak_custom_${newRowNumber}_lama">-</td>
                <td class="editable-cell" data-type="ak_custom_${newRowNumber}_baru">0.00</td>
                <td class="calculated-cell" data-type="ak_custom_${newRowNumber}_jumlah">0.00</td>
                <td class="editable-cell keterangan-cell" data-type="keterangan_${newRowNumber}" contenteditable="true">-</td>
            </tr>`;
            
            // Find the row that contains "................ **)" - this should always be the last data row
            const lastSpecialRow = $('#performance-table-body tr:not(.total-row)').filter(function() {
                return $(this).find('.description-text').text().includes('................ **');
            });
            
            if (lastSpecialRow.length > 0) {
                // Insert new row BEFORE the special row
                lastSpecialRow.before(newRow);
            } else {
                // Fallback: insert before total row
                $('.total-row').before(newRow);
            }
            
            // Re-number ALL rows to ensure consistency
            reNumberAllRows();
            
            // Recalculate totals
            calculateFormat3Totals();
        }

        // ===== NEW FUNCTION TO RE-NUMBER ALL ROWS =====
        function reNumberAllRows() {
            let currentNumber = 1;
            
            // Re-number ALL rows (excluding total row) from 1 to N
            $('#performance-table-body tr:not(.total-row)').each(function(index) {
                // Update row data-id and numbering
                $(this).attr('data-row-id', currentNumber);
                
                // Update the row number cell (first column)
                $(this).find('.row-number').first().text(currentNumber);
                
                // Update text in description
                const descriptionSpan = $(this).find('.description-text');
                let currentText = descriptionSpan.text();
                
                // Special handling for the row with dots - keep it as the last row always
                if (currentText.includes('................ **')) {
                    const newText = '................ **)';
                    descriptionSpan.text(newText);
                } else {
                    // For all other rows, update normally
                    let newText = currentText.replace(/^\d+\./, currentNumber + '.');
                    descriptionSpan.text(newText);
                }
                
                // Update remove button onclick
                $(this).find('.remove-row-btn').attr('onclick', 'removePerformanceRow(' + currentNumber + ')');
                
                // Update data-type attributes
                const lamaCell = $(this).find('.editable-cell[data-type*="_lama"]:not(.keterangan-cell)');
                const baruCell = $(this).find('.editable-cell[data-type*="_baru"]:not(.keterangan-cell)');
                const jumlahCell = $(this).find('.calculated-cell[data-type*="_jumlah"]');
                const keteranganCell = $(this).find('.keterangan-cell');
                
                // Determine data-type based on current row number and content
                if (currentNumber === 1) {
                    // AK Dasar yang diberikan
                    lamaCell.attr('data-type', 'ak_dasar_lama');
                    baruCell.attr('data-type', 'ak_dasar_baru');
                    jumlahCell.attr('data-type', 'ak_dasar_jumlah');
                } else if (currentNumber === 2) {
                    // AK JF Lama
                    lamaCell.attr('data-type', 'ak_jf_lama');
                    baruCell.attr('data-type', 'ak_jf_baru');
                    jumlahCell.attr('data-type', 'ak_jf_jumlah');
                } else if (currentNumber === 3) {
                    // AK Penyesuaian/ Penyetaraan
                    lamaCell.attr('data-type', 'ak_penyesuaian_lama');
                    baruCell.attr('data-type', 'ak_penyesuaian_baru');
                    jumlahCell.attr('data-type', 'ak_penyesuaian_jumlah');
                } else if (currentNumber === 4) {
                    // AK Konversi
                    lamaCell.attr('data-type', 'ak_konversi_lama');
                    baruCell.attr('data-type', 'ak_konversi_baru');
                    jumlahCell.attr('data-type', 'ak_konversi_jumlah');
                } else if (currentNumber === 5) {
                    // AK yang diperoleh dari peningkatan pendidikan
                    lamaCell.attr('data-type', 'ak_pendidikan_lama');
                    baruCell.attr('data-type', 'ak_pendidikan_baru');
                    jumlahCell.attr('data-type', 'ak_pendidikan_jumlah');
                } else if (currentText.includes('................ **')) {
                    // Special row with dots - always use "lainnya" type
                    lamaCell.attr('data-type', 'ak_lainnya_lama');
                    baruCell.attr('data-type', 'ak_lainnya_baru');
                    jumlahCell.attr('data-type', 'ak_lainnya_jumlah');
                } else {
                    // Custom added rows
                    lamaCell.attr('data-type', 'ak_custom_' + currentNumber + '_lama');
                    baruCell.attr('data-type', 'ak_custom_' + currentNumber + '_baru');
                    jumlahCell.attr('data-type', 'ak_custom_' + currentNumber + '_jumlah');
                }
                
                // Always update keterangan with current row number
                keteranganCell.attr('data-type', 'keterangan_' + currentNumber);
                
                currentNumber++;
            });
        }

        // ===== UPDATED REMOVE ROW FUNCTION =====
        function removePerformanceRow(rowId) {
            const rowsCount = $('#performance-table-body tr:not(.total-row)').length;
            
            if (rowsCount <= 6) { // Minimum 6 rows (including the special row 6)
                alert('Minimal harus ada 6 baris data!');
                return;
            }
            
            // Don't allow removing rows 1-5 (core data rows)
            if (rowId <= 5) {
                alert('Baris data inti (1-5) tidak dapat dihapus!');
                return;
            }
            
            if (confirm('Apakah Anda yakin ingin menghapus baris ini?')) {
                $('tr[data-row-id="' + rowId + '"]').remove();
                
                // Re-number ALL rows after removal to ensure consistency
                reNumberAllRows();
                
                // Recalculate totals
                calculateFormat3Totals();
            }
        }

        function editDescription(element) {
            const currentText = $(element).text();
            const input = $('<input type="text" value="' + currentText + '" style="background: transparent; border: 1px solid #007bff; padding: 2px 5px; border-radius: 3px; width: auto; min-width: 200px;">');
            
            $(element).replaceWith(input);
            input.focus().select();
            
            input.on('blur keypress', function(e) {
                if (e.type === 'blur' || e.which === 13) {
                    const newText = $(this).val();
                    const newSpan = $('<span class="description-text editable-description" onclick="editDescription(this)">' + newText + '</span>');
                    $(this).replaceWith(newSpan);
                }
            });
        }

        // ===== KETERANGAN CELL FUNCTIONS =====
        function saveKeteranganData() {
            const keteranganData = {};
            
            $('.keterangan-cell').each(function() {
                const dataType = $(this).attr('data-type');
                const content = $(this).text();
                keteranganData[dataType] = content;
            });
            
            console.log('Keterangan data saved:', keteranganData);
        }

        function loadKeteranganData() {
            console.log('Loading keterangan data...');
        }

        // Auto-save when keterangan content changes
        $(document).on('blur', '.keterangan-cell', function() {
            saveKeteranganData();
        });

        $("#btn-lihat-f3").click(function(e){
            e.preventDefault();
            let tahun = $("#tahun_pilih_f3").val();
            
            console.log("Button F3 clicked, tahun:", tahun);
            
            if(tahun === ""){
                alert("Pilih tahun terlebih dahulu!");
                return;
            }
            
            $("#current-year-display").text(tahun);
            console.log("Memuat data Format 3 untuk tahun:", tahun);
            
            $.ajax({
                url: "../models/load_form3.php",
                type: "POST",
                data: {tahun_pilih: tahun},
                dataType: 'json',
                success: function(response) {
                    console.log("Response Format 3:", response);
                    
                    if(response.status === 'success') {
                        $("#format3-container").show();
                        
                        if(response.total_angka_kredit > 0) {
                            $(".editable-cell[data-type='ak_konversi_baru']").text(parseFloat(response.total_angka_kredit).toFixed(2));
                        } else {
                            loadAKKonversiFromFormat1();
                        }
                        
                        calculateFormat3Totals();
                        $(".format3-warning").hide();
                        
                    } else if(response.status === 'no_data') {
                        $("#format3-container").show();
                        $(".format3-warning").show();
                    } else {
                        $("#format3-container").hide();
                        alert("Terjadi kesalahan: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error Format 3:", status, error);
                    console.log("Response Text F3:", xhr.responseText);
                    console.log("Status Code:", xhr.status);
                    $("#format3-container").hide();
                    alert("Terjadi kesalahan saat memuat data Format 3");
                }
            });
        });

        // ===== FORMAT 3: Editable Cells =====
        $(document).on('click', '.editable-cell:not(.keterangan-cell)', function() {
            if ($(this).find('input').length > 0) return;
            
            var currentValue = $(this).text();
            var input = $('<input type="text" class="cell-input" value="' + currentValue + '">');
            
            $(this).html(input);
            input.focus().select();
            
            input.on('blur keypress', function(e) {
                if (e.type === 'blur' || e.which === 13) {
                    var newValue = $(this).val();
                    $(this).parent().text(newValue);
                    calculateFormat3Totals();
                }
            });
        });

        function loadAKKonversiFromFormat1() {
            var angkaKredit = $("#angka_kredit").val();
            if (angkaKredit && angkaKredit !== '') {
                $(".editable-cell[data-type='ak_konversi_baru']").text(parseFloat(angkaKredit).toFixed(2));
            }
        }

        function calculateFormat3Totals() {
            // Hitung untuk setiap baris
            var totalLama = 0;
            var totalBaru = 0;
            var totalJumlah = 0;
            
            // Iterasi semua baris data (tidak termasuk baris total)
            $('#performance-table-body tr:not(.total-row)').each(function() {
                const lamaCell = $(this).find('.editable-cell[data-type*="_lama"]:not(.keterangan-cell)');
                const baruCell = $(this).find('.editable-cell[data-type*="_baru"]:not(.keterangan-cell)');
                const jumlahCell = $(this).find('.calculated-cell[data-type*="_jumlah"]');
                
                if (lamaCell.length && baruCell.length && jumlahCell.length) {
                    // Parse nilai dari cell (handle berbagai format)
                    var lamaText = lamaCell.text().replace(',', '.').replace(/[^\d.-]/g, '');
                    var baruText = baruCell.text().replace(',', '.').replace(/[^\d.-]/g, '');
                    
                    // Konversi ke number, default 0 jika tidak valid atau "-"
                    var lamaValue = (lamaText === '' || lamaText === '-') ? 0 : parseFloat(lamaText) || 0;
                    var baruValue = (baruText === '' || baruText === '-') ? 0 : parseFloat(baruText) || 0;
                    
                    // Hitung jumlah untuk baris ini
                    var jumlah = lamaValue + baruValue;
                    jumlahCell.text(jumlah.toFixed(2));
                    
                    // Tambahkan ke total kumulatif
                    totalLama += lamaValue;
                    totalBaru += baruValue;
                    totalJumlah += jumlah;
                }
            });
            
            // Update baris total kumulatif
            $("#total_lama_kumulatif").text(totalLama.toFixed(2));
            $("#total_baru_kumulatif").text(totalBaru.toFixed(2));
            $("#total_jumlah_kumulatif").text(totalJumlah.toFixed(2));
            
            // Hitung kelebihan/kekurangan angka kredit
            calculateKelebihanAngkaKredit(totalJumlah);
        }

        function calculateKelebihanAngkaKredit(totalJumlah) {
            // Ambil nilai minimal untuk pangkat dan jenjang
            var minimalPangkatText = $(".editable-cell[data-type='ak_minimal_pangkat']").text().replace(',', '.');
            var minimalJenjangText = $(".editable-cell[data-type='ak_minimal_jenjang']").text().replace(',', '.');
            
            var minimalPangkat = parseFloat(minimalPangkatText) || 50; // default 50
            var minimalJenjang = parseFloat(minimalJenjangText) || 50; // default 50
            
            // Hitung kelebihan/kekurangan
            var kelebihanPangkat = totalJumlah - minimalPangkat;
            var kelebihanJenjang = totalJumlah - minimalJenjang;
            
            // Update nilai kelebihan
            $(".calculated-cell[data-type='kelebihan_pangkat']").text(kelebihanPangkat.toFixed(3));
            $(".calculated-cell[data-type='kelebihan_jenjang']").text(kelebihanJenjang.toFixed(3));
            
            // Update strikethrough text
            updateStrikethroughText(kelebihanPangkat, kelebihanJenjang);
        }

        function updateStrikethroughText(kelebihanPangkat, kelebihanJenjang) {
            var pangkatCell = $("#keterangan-pangkat");
            if (pangkatCell.length > 0) {
                var newTextPangkat = "";
                
                if (kelebihanPangkat < 0) {
                    newTextPangkat = '<span style="text-decoration: line-through;">Kelebihan</span>/ kekurangan <sup>**)</sup> Angka Kredit yang harus dicapai untuk kenaikan pangkat';
                } else {
                    newTextPangkat = 'Kelebihan/ <span style="text-decoration: line-through;">kekurangan</span> <sup>**)</sup> Angka Kredit yang harus dicapai untuk kenaikan pangkat';
                }
                
                pangkatCell.html(newTextPangkat);
            }
            
            var jenjangCell = $("#keterangan-jenjang");
            if (jenjangCell.length > 0) {
                var newTextJenjang = "";
                
                if (kelebihanJenjang < 0) {
                    newTextJenjang = '<span style="text-decoration: line-through;">Kelebihan</span>/kekurangan <sup>**)</sup> Angka Kredit yang harus dicapai untuk peningkatan jenjang';
                } else {
                    newTextJenjang = 'Kelebihan/<span style="text-decoration: line-through;">kekurangan</span> <sup>**)</sup> Angka Kredit yang harus dicapai untuk peningkatan jenjang';
                }
                
                jenjangCell.html(newTextJenjang);
            }
        } 

        $(document).on('blur', '.editable-cell[data-type="ak_minimal_pangkat"], .editable-cell[data-type="ak_minimal_jenjang"]', function() {
            setTimeout(function() {
                calculateFormat3Totals();
            }, 100);
        });
    </script>
</body>