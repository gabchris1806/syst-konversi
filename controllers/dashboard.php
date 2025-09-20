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
            <div class="nav-links">
                <!-- <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="../views/format1.php" class="nav-link">Input Data</a>
                <a href="../views/format2.php" class="nav-link">Daftar Konversi</a>
                <a href="../views/format3.php" class="nav-link">Rekap Konversi</a> -->
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

    <!-- ALERT MESSAGES -->
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

    <!-- MAIN DASHBOARD CONTENT -->
    <div class="dashboard-content">
        <div class="welcome-section">
            <h1>Selamat Datang, <?php echo htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="welcome-subtitle">Sistem Konversi Angka Kredit - Dashboard Utama</p>
        </div>

        <!-- DASHBOARD CARDS -->
        <div class="dashboard-cards">
            <div class="dashboard-card" onclick="location.href='../views/format1.php'">
                <div class="card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="10,9 9,9 8,9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="card-content">
                    <h3>Input Data</h3>
                    <p>Masukkan data konversi angka kredit baru</p>
                </div>
                <div class="card-arrow">→</div>
            </div>

            <div class="dashboard-card" onclick="location.href='../views/format2.php'">
                <div class="card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                        <path d="M9 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="9" y="11" width="6" height="11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="m9 7 3-3 3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="12" y1="4" x2="12" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="card-content">
                    <h3>Daftar Konversi</h3>
                    <p>Lihat dan kelola data konversi yang telah diinput</p>
                </div>
                <div class="card-arrow">→</div>
            </div>

            <div class="dashboard-card" onclick="location.href='../views/format3.php'">
                <div class="card-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="14" y="14" width="7" height="7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="card-content">
                    <h3>Rekap Konversi</h3>
                    <p>Lihat rekapitulasi dan cetak laporan</p>
                </div>
                <div class="card-arrow">→</div>
            </div>
        </div>
    </div>

    <script>
        // ===== ALERT FUNCTIONS =====
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

        // ===== MESSAGE FUNCTIONS =====
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

        // ===== LOAD DASHBOARD STATS =====
        function loadDashboardStats() {
            $.ajax({
                url: '../models/get_dashboard_stats.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#total-data').text(response.data.total_data || '0');
                        $('#data-tahun-ini').text(response.data.data_tahun_ini || '0');
                        $('#total-angka-kredit').text(response.data.total_angka_kredit || '0.00');
                    } else {
                        console.error('Failed to load dashboard stats:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading dashboard stats:', error);
                }
            });
        }

        // ===== DOCUMENT READY =====
        document.addEventListener("DOMContentLoaded", function() {
            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert, index) {
                if (!alert.id) {
                    alert.id = 'auto-alert-' + index;
                }
                
                setTimeout(function() {
                    hideAlert(alert);
                }, 5000);
            });

            // Load dashboard statistics
            loadDashboardStats();

            // Add hover effects to dashboard cards
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>