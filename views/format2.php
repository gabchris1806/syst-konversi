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
    <style>
        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }
        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        .notification.success {
            background-color: #28a745;
            border-left: 4px solid #1e7e34;
        }
        .notification.error {
            background-color: #dc3545;
            border-left: 4px solid #bd2130;
        }
    </style>
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

    <script>
        // ===== NOTIFICATION FUNCTIONS =====
        function showSuccessMessage(message) {
            showNotification(message, 'success');
        }

        function showErrorMessage(message) {
            showNotification(message, 'error');
        }

        function showNotification(message, type) {
            // Remove existing notifications
            $('.notification').remove();
            
            // Create notification element
            const notification = $(`<div class="notification ${type}">${message}</div>`);
            $('body').append(notification);
            
            // Show notification
            setTimeout(() => {
                notification.addClass('show');
            }, 100);
            
            // Hide notification after 5 seconds
            setTimeout(() => {
                notification.removeClass('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }

        // ===== LOAD DATA FORMAT 2 =====
        $("#btn-lihat-f2").click(function(e){
            e.preventDefault();
            let tahun = $("#tahun_pilih_f2").val();
            
            console.log("Button F2 clicked, tahun:", tahun);
            
            if(tahun === ""){
                showErrorMessage("Pilih tahun terlebih dahulu!");
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
                        $("#tabel-format2").html(response.table_data || '<tr><td colspan="7" class="no-data-message">Tidak ada data untuk tahun ini</td></tr>');
                        $("#summary-container-f2").hide();
                        if(response.message) {
                            showErrorMessage(response.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error F2:", status, error);
                    console.log("Response Text F2:", xhr.responseText);
                    console.log("Status Code:", xhr.status);
                    $("#tabel-format2").html('<tr><td colspan="7" class="no-data-message" style="color: red;">Terjadi kesalahan saat memuat data</td></tr>');
                    $("#summary-container-f2").hide();
                    showErrorMessage('Terjadi kesalahan saat memuat data');
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
                url: '../models/update_konversi.php',
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
                        
                        // Refresh data jika field penting diubah
                        if (field === 'persentase' || field === 'koefisien') {
                            // Update summary untuk persentase/koefisien, tapi tidak perlu reload penuh
                            setTimeout(function() {
                                $("#btn-lihat-f2").click();
                            }, 1500);
                        } else if (field === 'tahun') {
                            // Jika tahun berubah, reload segera karena data mungkin pindah ke tahun lain
                            setTimeout(function() {
                                // Update dropdown ke tahun baru jika perlu
                                $("#tahun_pilih_f2").val(newValue);
                                $("#btn-lihat-f2").click();
                            }, 1000);
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
                    error: function(xhr, status, error) {
                        showErrorMessage('Terjadi kesalahan saat menghapus data');
                        console.error('Delete Error:', status, error);
                        console.error('Response:', xhr.responseText);
                    }
                });
            }
        }
    </script>
</body>
</html>