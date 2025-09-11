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
    <script>
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

        function showErrorMessage(message) {
            // Create error alert if it doesn't exist
            let existingAlert = document.querySelector('.alert.error');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alert = document.createElement('div');
            alert.className = 'alert error';
            alert.style.cssText = `
                background: #f8d7da;
                color: #721c24;
                padding: 12px 20px;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                margin: 10px 0;
                position: relative;
            `;
            alert.innerHTML = message + '<span class="alert-close" onclick="hideAlert(this.parentElement)" style="position: absolute; right: 10px; top: 10px; cursor: pointer; font-size: 18px;">&times;</span>';
            
            // Insert before the form
            const form = document.querySelector('.konversi-form');
            form.parentNode.insertBefore(alert, form);
            
            // Auto hide after 5 seconds
            setTimeout(() => hideAlert(alert), 5000);
        }

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
            // ===== AUTOMATIC PERCENTAGE CALCULATION BASED ON MONTHS - FIXED =====
            const bulanMap = {
                "januari": 1, "februari": 2, "maret": 3, "april": 4,
                "mei": 5, "juni": 6, "juli": 7, "agustus": 8,
                "september": 9, "oktober": 10, "november": 11, "desember": 12
            };

            const bulanAwalSelect = document.getElementById("bulan_awal");
            const bulanAkhirSelect = document.getElementById("bulan_akhir");

            function hitungPersentase() {
                const bulanAwalValue = bulanAwalSelect.value;
                const bulanAkhirValue = bulanAkhirSelect.value;
                
                if (bulanAwalValue && bulanAkhirValue) {
                    let awal = bulanMap[bulanAwalValue.toLowerCase()];
                    let akhir = bulanMap[bulanAkhirValue.toLowerCase()];

                    if (awal && akhir) {
                        let selisih = akhir - awal + 1;
                        if (selisih <= 0) selisih += 12; // Handle cross-year periods
                        
                        persentaseInput.value = selisih;
                        
                        // Trigger angka kredit calculation dengan predikat
                        hitungAngkaKreditDenganPredikat();
                        
                        console.log("Perhitungan Persentase:");
                        console.log("Bulan Awal:", bulanAwalValue, "(", awal, ")");
                        console.log("Bulan Akhir:", bulanAkhirValue, "(", akhir, ")");
                        console.log("Selisih Bulan:", selisih);
                    }
                } else {
                    persentaseInput.value = "";
                    angkaKreditInput.value = "";
                }
            }

            // Event listeners untuk dropdown bulan
            bulanAwalSelect.addEventListener("change", hitungPersentase);
            bulanAkhirSelect.addEventListener("change", hitungPersentase);

            // Event listeners untuk dropdown predikat dan input lainnya
            predikatSelect.addEventListener("change", function() {
                hitungAngkaKreditDenganPredikat();
                
                // Show tooltip with percentage info
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const percentage = selectedOption.getAttribute('data-persen');
                    console.log(`Predikat dipilih: ${selectedOption.text} (${percentage}%)`);
                }
            });
            
            persentaseInput.addEventListener("input", hitungAngkaKreditDenganPredikat);
            koefisienInput.addEventListener("input", hitungAngkaKreditDenganPredikat);

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
                    closeBtn.style.cssText = 'position: absolute; right: 10px; top: 10px; cursor: pointer; font-size: 18px;';
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

            // ===== LOAD KETERANGAN DATA (if function exists) =====
            if (typeof loadKeteranganData === 'function') {
                loadKeteranganData();
            }
            
            // ===== TRIGGER INITIAL CALCULATION =====
            if (koefisienInput.value && persentaseInput.value) {
                hitungAngkaKreditDenganPredikat();
            }
        });

        // ===== GLOBAL FUNCTIONS =====
        let performanceRowCounter = 6; // Keep this if used elsewhere
    </script>
</body>
