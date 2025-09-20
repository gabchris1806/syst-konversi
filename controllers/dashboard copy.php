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
<body class="dashboard-page">
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-left">
            <img src="../assets/images/Logo_PTKI_Medan.png" class="logo" alt="Logo">
            <span class="app-title">SISTEM KONVERSI ANGKA KREDIT</span>
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

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error" id="session-error-alert">
            <span class="alert-close" onclick="hideAlert('session-error-alert')">&times;</span>
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" id="session-success-alert">
            <span class="alert-close" onclick="hideAlert('session-success-alert')">&times;</span>
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <!-- TAB CONTAINER -->
    <div class="tab-container">
        <!-- TAB BAR -->
        <div class="tab-bar">
            <button class="tab-button active" data-tab="format1">Format 1 - Konversi</button>
            <button class="tab-button" data-tab="format2">Format 2 - Data Tahunan</button>
            <button class="tab-button" data-tab="format3">Format 3 - Laporan</button>
        </div>

        <!-- TAB CONTENT -->
        <div class="tab-content">
            <!-- FORMAT 1 CONTENT -->
            

            <!-- FORMAT 2 CONTENT -->
            <div class="form-content" id="format2">
                <div class="format2-header">
                    <h2 style="margin: 0; color: #333;">Format 2 - Data Tahunan</h2>
                </div>
                
                <div class="year-selector-container">
                    <label for="tahun_pilih_f2">Tahun:</label>
                    <select id="tahun_pilih_f2" required>
                        <option value="">Pilih Tahun</option>
                        <?php
                        $current_year = date('Y');
                        for ($i = 2020; $i <= $current_year + 5; $i++) {
                            echo "<option value='".$i."'>".$i."</option>";
                        }
                        ?>
                    </select>
                    <button type="button" id="btn-lihat-f2" class="btn-lihat">Lihat Data</button>
                </div>

                <!-- Summary Data -->
                <div class="summary-container" id="summary-container-f2" style="display:none;">
                    <div class="summary-row">
                        <div class="summary-item">
                            <label>Angka Dasar:</label>
                            <span id="angka-dasar-f2">50,0</span>
                        </div>
                        <div class="summary-item">
                            <label>Koefisien Per Tahun:</label>
                            <span id="koefisien-per-tahun-f2">12.5</span>
                        </div>
                        <div class="summary-item">
                            <label>Angka Kredit Yang Didapat:</label>
                            <span id="angka-kredit-didapat-f2">50.0</span>
                        </div>
                    </div>
                </div>

                <!-- Tabel Format 2 -->
                <table class="format2-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Tahun</th>
                            <th style="width: 20%;">Periode (Bulan)</th>
                            <th style="width: 15%;">Predikat</th>
                            <th style="width: 15%;">Persentase</th>
                            <th style="width: 15%;">Koefisien</th>
                            <th style="width: 15%;">Angka Kredit</th>
                            <th style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-format2">
                        <tr>
                            <td colspan="7" class="no-data-message">Silakan pilih tahun terlebih dahulu</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- FORMAT 3 CONTENT -->
            <div class="form-content" id="format3">
                <h2>Format 3 - Laporan</h2>
                
                <div class="year-selector-container">
                    <label for="tahun_pilih_f3">Tahun:</label>
                    <select id="tahun_pilih_f3" required>
                        <option value="">Pilih Tahun</option>
                        <?php
                        for ($i = 2020; $i <= $current_year + 5; $i++) {
                            echo "<option value='".$i."'>".$i."</option>";
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
                            <!-- BARIS TOTAL DIPINDAHKAN KE TBODY -->
                            <tr class="total-row" style="font-weight: bold; background-color: #007bff !important; color: white !important;">
                            <!-- kolom nomor dibiarkan kosong -->
                            <td style="background-color: #007bff !important; border: 1px solid #007bff;"></td>
                            
                            <!-- gabungkan deskripsi + keterangan kolom -->
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
                    
                    <!-- TABEL KEDUA  -->
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

                    <!-- UPDATED BOTTOM SECTION: KETERANGAN WITH CETAK LAPORAN BUTTON & WARNING MOVED DOWN -->
                    <div class="format3-bottom-section no-print">
                        <div class="format3-notes-section no-print">
                            <h4>Keterangan:</h4>
                            <p>**) Diisi dengan angka kredit yang diperoleh dari hasil konversi</p>
                            <p>***) Diisi dengan jenis kegiatan lainnya yang dapat dinilai angka kreditnya</p>
                            
                            <!-- CETAK LAPORAN BUTTON - MOVED TO RIGHT -->
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

                        <!-- WARNING MESSAGE - MOVED BELOW -->
                        <div class="format3-warning" style="display: none; background: #fff3cd; border: 1px solid #ffeaa7; padding: 12px; border-radius: 5px; margin-top: 15px;">
                            <span style="color: #856404; font-weight: bold;">
                                Laporan ini dibuat berdasarkan data konversi yang telah diinput untuk tahun <span id="current-year-display">2025</span>
                            </span>
                        </div>
                    </div>
                </div>    
            </div>
        </div>
    </div>

    <script>
    // Global variable untuk tracking row counter
    

    // ===== TAB SWITCHING =====
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.form-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // ===== FORMAT 2: DELETE FUNCTION =====
    

    // ===== UPDATED CETAK LAPORAN FUNCTION WITH USER IDENTITY =====
    

    // ===== FORMAT 2: AJAX Load Data =====
    

    // ===== FORMAT 3: AJAX Load Data - UPDATED WITH DYNAMIC YEAR =====
    

    // ===== IMPROVED MESSAGE FUNCTIONS =====
    function showErrorMessage(message) {
        const existingAlert = document.getElementById('dynamic-error-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-error';
        alertDiv.id = 'dynamic-error-alert';
        alertDiv.innerHTML = `
            <span class="alert-close" onclick="hideAlert('dynamic-error-alert')">&times;</span>
            ${message}
        `;
        
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.insertAdjacentElement('afterend', alertDiv);
        } else {
            document.body.insertBefore(alertDiv, document.body.firstChild);
        }
        
        setTimeout(function() {
            hideAlert(alertDiv);
        }, 5000);
    }

    function showSuccessMessage(message) {
        const existingAlert = document.getElementById('dynamic-success-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success';
        alertDiv.id = 'dynamic-success-alert';
        alertDiv.innerHTML = `
            <span class="alert-close" onclick="hideAlert('dynamic-success-alert')">&times;</span>
            ${message}
        `;
        
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            navbar.insertAdjacentElement('afterend', alertDiv);
        } else {
            document.body.insertBefore(alertDiv, document.body.firstChild);
        }
        
        setTimeout(function() {
            hideAlert(alertDiv);
        }, 5000);
    }

    // ===== FORM VALIDATION AND SUBMISSION - FIXED =====
    
        
        
    </script>
</body>
</html>