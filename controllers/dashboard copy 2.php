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
            <div class="form-content active" id="format1">
                <h2>Form Konversi</h2>
                <form class="konversi-form" action="../models/simpan_konversi.php" method="POST">
                    <div class="row">
                        <div class="form-group">
                            <label for="bulan_awal">Bulan Awal</label>
                            <select id="bulan_awal" name="bulan_awal" required>
                                <option value="">-- Pilih Bulan Awal --</option>
                                <option value="Januari">Januari</option>
                                <option value="Februari">Februari</option>
                                <option value="Maret">Maret</option>
                                <option value="April">April</option>
                                <option value="Mei">Mei</option>
                                <option value="Juni">Juni</option>
                                <option value="Juli">Juli</option>
                                <option value="Agustus">Agustus</option>
                                <option value="September">September</option>
                                <option value="Oktober">Oktober</option>
                                <option value="November">November</option>
                                <option value="Desember">Desember</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bulan_akhir">Bulan Akhir</label>
                            <select id="bulan_akhir" name="bulan_akhir" required>
                                <option value="">-- Pilih Bulan Akhir --</option>
                                <option value="Januari">Januari</option>
                                <option value="Februari">Februari</option>
                                <option value="Maret">Maret</option>
                                <option value="April">April</option>
                                <option value="Mei">Mei</option>
                                <option value="Juni">Juni</option>
                                <option value="Juli">Juli</option>
                                <option value="Agustus">Agustus</option>
                                <option value="September">September</option>
                                <option value="Oktober">Oktober</option>
                                <option value="November">November</option>
                                <option value="Desember">Desember</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tahun">Tahun</label>
                            <input type="number" id="tahun" name="tahun" placeholder="2025" required>
                        </div>
                    </div>

                    <h3>Hasil Penilaian</h3>
                    <div class="row">
                        <div class="form-group">
                            <label for="predikat">Predikat</label>
                            <select id="predikat" name="predikat" class="predikat-dropdown" required>
                                <option value="">-- Pilih Predikat --</option>
                                <option value="sangat baik" data-persen="150">Sangat Baik</option>
                                <option value="baik" data-persen="100">Baik</option>
                                <option value="butuh perbaikan" data-persen="75">Butuh Perbaikan</option>
                                <option value="kurang" data-persen="50">Kurang</option>
                                <option value="sangat kurang" data-persen="25">Sangat Kurang</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="persentase">Persentase (%)</label>
                            <div class="input-wrapper">
                                <input type="number" id="persentase" name="persentase" placeholder="Contoh: 5" required>
                                <span>/12</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="koefisien">Koefisien</label>
                            <input type="number" id="koefisien" name="koefisien" value="12.50" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="angka_kredit">Angka Kredit</label>
                            <input type="number" id="angka_kredit" name="angka_kredit" placeholder="" step="0.01" readonly>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Simpan</button>
                </form>
            </div>

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
    let performanceRowCounter = 6;

    // ===== PREDIKAT DROPDOWN FUNCTIONS - NEW =====
    const predikatSelect = document.getElementById("predikat");
    const persentaseInput = document.getElementById("persentase");
    const koefisienInput = document.getElementById("koefisien");
    const angkaKreditInput = document.getElementById("angka_kredit");

    // Mapping predikat ke persentase multiplier
    const predikatMultiplier = {
        "sangat baik": 1.5,    // 150%
        "baik": 1.0,           // 100%
        "butuh perbaikan": 0.75, // 75%
        "kurang": 0.5,         // 50%
        "sangat kurang": 0.25  // 25%
    };

    function hitungAngkaKreditDenganPredikat() {
        const predikatValue = predikatSelect.value;
        const nilaiPersentase = parseFloat(persentaseInput.value);
        const nilaiKoefisien = parseFloat(koefisienInput.value);
        
        if (predikatValue && !isNaN(nilaiPersentase) && !isNaN(nilaiKoefisien)) {
            // Hitung persentase dasar (bulan/12 * 100)
            const persenDasar = (nilaiPersentase / 12) * 100;
            
            // Kalikan dengan multiplier predikat
            const multiplier = predikatMultiplier[predikatValue];
            const persenAkhir = persenDasar * multiplier;
            
            // Hitung angka kredit
            const angkaKredit = persenAkhir * nilaiKoefisien / 100;
            
            angkaKreditInput.value = angkaKredit.toFixed(3);
            
            console.log("Perhitungan Angka Kredit:");
            console.log("Predikat:", predikatValue, "(" + (multiplier * 100) + "%)");
            console.log("Persentase:", nilaiPersentase + "/12 =", persenDasar.toFixed(2) + "%");
            console.log("Persentase x Multiplier:", persenDasar.toFixed(2) + "% x", multiplier, "=", persenAkhir.toFixed(2) + "%");
            console.log("Angka Kredit:", persenAkhir.toFixed(2) + "% x", nilaiKoefisien, "=", angkaKredit.toFixed(3));
        } else {
            angkaKreditInput.value = "";
        }
    }

    // Event listeners untuk dropdown predikat dan input lainnya
    predikatSelect.addEventListener("change", hitungAngkaKreditDenganPredikat);
    persentaseInput.addEventListener("input", hitungAngkaKreditDenganPredikat);
    koefisienInput.addEventListener("input", hitungAngkaKreditDenganPredikat);

    // ===== ALERT FUNCTIONS - FIXED =====
    function hideAlert(element) {
        if (typeof element === 'string') {
            element = document.getElementById(element);
        }
        
        if (element) {
            element.classList.add('fade-out');
            setTimeout(() => {
                if (element.parentNode) {
                    element.remove();
                }
            }, 500);
        }
    }

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
    function deleteKonversiData(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: '../models/delete_konversi.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showSuccessMessage('Data berhasil dihapus!');
                        // Reload data Format 2
                        $("#btn-lihat-f2").click();
                    } else {
                        showErrorMessage('Gagal menghapus data: ' + response.message);
                    }
                },
                error: function() {
                    showErrorMessage('Terjadi kesalahan saat menghapus data');
                }
            });
        }
    }

    // ===== UPDATED CETAK LAPORAN FUNCTION WITH USER IDENTITY =====
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

    // ===== FORMAT 2: AJAX Load Data =====
    $("#btn-lihat-f2").click(function(e){
        e.preventDefault();
        let tahun = $("#tahun_pilih_f2").val();
        
        console.log("Button F2 clicked, tahun:", tahun);
        
        if(tahun === ""){
            alert("Pilih tahun terlebih dahulu!");
            return;
        }

        // Show loading
        $("#tabel-format2").html('<tr><td colspan="7" class="loading">Memuat data...</td></tr>');
        $("#summary-container-f2").hide();
        
        $.ajax({
            url: "../models/load_form2.php",
            type: "POST",
            data: {tahun_pilih: tahun},
            dataType: 'json',
            success: function(response) {
                console.log("Response dari server F2:", response);

                if(response.status === 'success') {
                    $("#tabel-format2").html(response.table_data);

                    if(response.summary_data) {
                        $("#koefisien-per-tahun-f2").text(response.summary_data.koefisien_per_tahun);
                        $("#angka-kredit-didapat-f2").text(response.summary_data.angka_kredit_yang_didapat);
                        $("#angka-dasar-f2").text("50,0");
                        $("#summary-container-f2").show();
                    }
                } else {
                    $("#tabel-format2").html(response.table_data);
                    $("#summary-container-f2").hide();
                }
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error F2:", status, error);
                console.log("Response Text F2:", xhr.responseText);
                console.log("Status Code:", xhr.status);
                $("#tabel-format2").html('<tr><td colspan="7" class="no-data-message" style="color: red;">Terjadi kesalahan saat memuat data</td></tr>');
                $("#summary-container-f2").hide();
            }
        });
    });
    
    // ===== FORMAT 2: INLINE EDITING FUNCTIONS =====
    $(document).on('click', '.editable-field', function() {
        if ($(this).find('input').length > 0) return; // Already editing
        
        const currentValue = $(this).text().replace('/12', ''); // Remove /12 from persentase
        const field = $(this).attr('data-field');
        const rowKey = $(this).closest('tr').attr('data-row-key');
        
        let inputType = 'text';
        let step = '';
        let inputOptions = '';
        
        // Determine input type based on field
        switch(field) {
            case 'persentase':
                inputType = 'number';
                inputOptions = 'min="1" max="12"';
                break;
            case 'koefisien':
                inputType = 'number';
                inputOptions = 'step="0.01" min="0.01"';
                break;
            case 'tahun':
                inputType = 'number';
                inputOptions = 'min="1900" max="2100"';
                break;
            case 'periode':
                // For periode, we'll use a text input with validation
                inputType = 'text';
                inputOptions = 'placeholder="Contoh: April atau April - Juni"';
                break;
            case 'predikat':
                inputType = 'text';
                inputOptions = 'placeholder="Masukkan predikat"';
                break;
            default:
                inputType = 'text';
        }
        
        // Create input element
        const input = $(`<input type="${inputType}" ${inputOptions} class="cell-input-f2" value="${currentValue}" style="width: 100%; border: 2px solid #007bff; padding: 4px; text-align: center; background: #fff3cd; border-radius: 4px;">`);
        
        // Replace cell content with input
        $(this).html(input);
        input.focus().select();
        
        // Handle save on blur or Enter
        input.on('blur keypress', function(e) {
            if (e.type === 'blur' || e.which === 13) {
                const newValue = $(this).val().trim();
                const cell = $(this).parent();
                
                // Validate input based on field type
                if (!validateFieldInput(field, newValue, currentValue, cell)) {
                    return;
                }
                
                if (newValue !== currentValue) {
                    // Save to database
                    saveFieldValue(rowKey, field, newValue, cell, currentValue);
                } else {
                    // No change, restore original value
                    const displayValue = getDisplayValue(field, newValue);
                    cell.text(displayValue);
                }
            }
        });
        
        // Handle Escape key to cancel
        input.on('keyup', function(e) {
            if (e.which === 27) { // Escape key
                const displayValue = getDisplayValue(field, currentValue);
                $(this).parent().text(displayValue);
            }
        });
    });

        // Helper function to validate field input
        function validateFieldInput(field, newValue, currentValue, cell) {
            switch(field) {
                case 'persentase':
                    if (newValue < 1 || newValue > 12 || !Number.isInteger(Number(newValue))) {
                        showErrorMessage('Persentase harus berupa bilangan bulat antara 1-12');
                        cell.text(getDisplayValue(field, currentValue));
                        return false;
                    }
                    break;
                    
                case 'koefisien':
                    if (isNaN(newValue) || Number(newValue) <= 0) {
                        showErrorMessage('Koefisien harus berupa angka positif');
                        cell.text(getDisplayValue(field, currentValue));
                        return false;
                    }
                    break;
                    
                case 'tahun':
                    if (isNaN(newValue) || Number(newValue) < 1900 || Number(newValue) > 2100) {
                        showErrorMessage('Tahun harus berupa angka antara 1900-2100');
                        cell.text(getDisplayValue(field, currentValue));
                        return false;
                    }
                    break;
                    
                case 'periode':
                    // Validate periode format
                    const validMonths = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 
                                    'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
                    const periodeParts = newValue.toLowerCase().split(' - ');
                    let isValidPeriode = true;
                    
                    for (let month of periodeParts) {
                        month = month.trim();
                        if (!validMonths.includes(month)) {
                            isValidPeriode = false;
                            break;
                        }
                    }
                    
                    if (!isValidPeriode || newValue.trim() === '') {
                        showErrorMessage('Format periode tidak valid. Gunakan nama bulan atau range bulan (contoh: "April" atau "April - Juni")');
                        cell.text(getDisplayValue(field, currentValue));
                        return false;
                    }
                    break;
                    
                case 'predikat':
                    if (newValue.trim() === '') {
                        showErrorMessage('Predikat tidak boleh kosong');
                        cell.text(getDisplayValue(field, currentValue));
                        return false;
                    }
                    break;
            }
            return true;
        }

        // Helper function to get display value
        function getDisplayValue(field, value) {
            switch(field) {
                case 'persentase':
                    return value + '/12';
                case 'koefisien':
                    return parseFloat(value).toFixed(2);
                case 'periode':
                    // Capitalize first letter of each word
                    return value.split(' ').map(word => 
                        word === '-' ? word : word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
                    ).join(' ');
                default:
                    return value;
            }
        }

        // Enhanced Save field value to database function
        function saveFieldValue(rowKey, field, newValue, cell, originalValue) {
            // Show loading state
            cell.html('<span style="color: #666; font-style: italic;">💾 Menyimpan...</span>');
            
            $.ajax({
                url: 'update_konversi.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    row_key: rowKey,
                    field: field,
                    value: newValue
                }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Display success and update cell
                        const displayValue = getDisplayValue(field, newValue);
                        cell.text(displayValue);
                        cell.css('background-color', '#d1ecf1').animate({'background-color': 'transparent'}, 2000);
                        
                        // If persentase or koefisien was updated, update angka_kredit column
                        if ((field === 'persentase' || field === 'koefisien') && response.new_angka_kredit) {
                            const angkaKreditCell = cell.closest('tr').find('.calculated-field');
                            angkaKreditCell.text(response.new_angka_kredit);
                            angkaKreditCell.css('background-color', '#d1ecf1').animate({'background-color': 'transparent'}, 2000);
                        }
                        
                        showSuccessMessage('Data berhasil diperbarui!');
                        
                        // Refresh summary data if necessary
                        if (field === 'persentase' || field === 'koefisien' || field === 'tahun') {
                            setTimeout(function() {
                                $("#btn-lihat-f2").click();
                            }, 1500);
                        }
                        
                    } else {
                        // Restore original value on error
                        const displayValue = getDisplayValue(field, originalValue);
                        cell.text(displayValue);
                        showErrorMessage(response.message || 'Gagal menyimpan data');
                    }
                },
                error: function(xhr, status, error) {
                    // Restore original value on error
                    const displayValue = getDisplayValue(field, originalValue);
                    cell.text(displayValue);
                    showErrorMessage('Terjadi kesalahan saat menyimpan data');
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

    // ===== FORMAT 2: DELETE FUNCTION =====
    function deleteKonversiData(rowKey) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            $.ajax({
                url: '../models/delete_konversi.php',
                type: 'POST',
                data: { row_key: rowKey },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        showSuccessMessage('Data berhasil dihapus!');
                        // Reload data Format 2
                        $("#btn-lihat-f2").click();
                    } else {
                        showErrorMessage('Gagal menghapus data: ' + response.message);
                    }
                },
                error: function() {
                    showErrorMessage('Terjadi kesalahan saat menghapus data');
                }
            });
        }
    }

    // ===== FORMAT 3: AJAX Load Data - UPDATED WITH DYNAMIC YEAR =====
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
    function checkPeriodeDuplikasi(bulanAwal, bulanAkhir, tahun, form) {
        $.ajax({
            url: 'check_periode_duplikasi.php',
            type: 'POST',
            data: {
                bulan_awal: bulanAwal,
                bulan_akhir: bulanAkhir,
                tahun: tahun
            },
            dataType: 'json',
            success: function(response) {
                if (response.exists) {
                    showErrorMessage('Data untuk periode ' + bulanAwal + ' - ' + bulanAkhir + ' tahun ' + tahun + ' sudah ada! Silakan pilih periode yang berbeda.');
                } else {
                    form.submit();
                }
            },
            error: function() {
                console.log('Error checking periode duplikasi, proceeding with server validation');
                form.submit();
            }
        });
    }

    // ===== DOCUMENT READY FUNCTIONS =====
    document.addEventListener("DOMContentLoaded", function() {
        // ===== AUTO-HIDE ALERTS - FIXED =====
        const alerts = document.querySelectorAll('.alert, [style*="background: #f8d7da"], [style*="background: #d4edda"]');
        
        alerts.forEach(function(alert, index) {
            if (!alert.id) {
                alert.id = 'auto-alert-' + index;
            }
            
            if (!alert.querySelector('.alert-close')) {
                const closeBtn = document.createElement('span');
                closeBtn.className = 'alert-close';
                closeBtn.innerHTML = '&times;';
                closeBtn.onclick = function() {
                    hideAlert(alert);
                };
                
                alert.style.position = 'relative';
                alert.appendChild(closeBtn);
            }
            
            setTimeout(function() {
                hideAlert(alert);
            }, 5000);
        });

        // ===== FORM SUBMISSION HANDLER - FIXED =====
        const form = document.querySelector('.konversi-form');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const bulanAwal = document.getElementById('bulan_awal').value;
                const bulanAkhir = document.getElementById('bulan_akhir').value;
                const tahun = document.getElementById('tahun').value;
                
                if (bulanAwal && bulanAkhir && tahun) {
                    e.preventDefault();
                    checkPeriodeDuplikasi(bulanAwal, bulanAkhir, tahun, form);
                }
            });
        }

        // ===== LOAD KETERANGAN DATA =====
        loadKeteranganData();
        
        // ===== TRIGGER INITIAL CALCULATION =====
        const koefisienInputDom = document.getElementById("koefisien");
        const persentaseInputDom = document.getElementById("persentase");
        
        if (koefisienInputDom.value && persentaseInputDom.value) {
            hitungAngkaKreditDenganPredikat();
        }
        
        // ===== AUTOMATIC PERCENTAGE CALCULATION BASED ON MONTHS =====
        const bulanMap = {
            "januari": 1, "februari": 2, "maret": 3, "april": 4,
            "mei": 5, "juni": 6, "juli": 7, "agustus": 8,
            "september": 9, "oktober": 10, "november": 11, "desember": 12
        };

        const bulanAwalSelect = document.getElementById("bulan_awal");
        const bulanAkhirSelect = document.getElementById("bulan_akhir");

        function hitungPersentase() {
            let awal = bulanMap[bulanAwalSelect.value.toLowerCase()] || 0;
            let akhir = bulanMap[bulanAkhirSelect.value.toLowerCase()] || 0;

            if (awal > 0 && akhir > 0) {
                let selisih = akhir - awal + 1;
                if (selisih <= 0) selisih += 12; 
                persentaseInput.value = selisih;
                
                // Trigger angka kredit calculation dengan predikat
                hitungAngkaKreditDenganPredikat();
            } else {
                persentaseInput.value = "";
            }
        }

        bulanAwalSelect.addEventListener("change", hitungPersentase);
        bulanAkhirSelect.addEventListener("change", hitungPersentase);
        
        
            
            // Show tooltip with percentage info
            if (selectedOption.value) {
                const percentage = selectedOption.getAttribute('data-persen');
                console.log(`Predikat dipilih: ${selectedOption.text} (${percentage}%)`);
            }
        });
        
        
    </script>
</body>
</html>