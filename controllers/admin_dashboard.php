<?php
session_start();
include __DIR__ . '/../config/db.php';

// Cek login admin
if (!isset($_SESSION['nip']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil data admin
$nip_admin = $_SESSION['nip'];
$query_admin = mysqli_query($conn, "SELECT * FROM pegawai WHERE nip='$nip_admin'");
$admin = mysqli_fetch_assoc($query_admin);

// Ambil semua data user
$query_users = mysqli_query($conn, "SELECT * FROM pegawai WHERE role='user' OR role IS NULL ORDER BY nama ASC");

// Ambil statistik - FIXED: ganti dari 'konversi' ke 'nilai'
$total_users = mysqli_num_rows($query_users);
$query_total_konversi = mysqli_query($conn, "SELECT COUNT(*) as total FROM nilai");
$total_konversi = mysqli_fetch_assoc($query_total_konversi)['total'];

// Handle delete user - FIXED: ganti dari 'konversi' ke 'nilai'
if (isset($_GET['delete_user']) && isset($_GET['nip'])) {
    $nip_to_delete = $_GET['nip'];
    
    // Delete user's data from nilai table first
    mysqli_query($conn, "DELETE FROM nilai WHERE nip='$nip_to_delete'");
    
    // Delete user
    $delete_result = mysqli_query($conn, "DELETE FROM pegawai WHERE nip='$nip_to_delete' AND role='user'");
    
    if ($delete_result) {
        echo "<script>alert('User berhasil dihapus!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus user!');</script>";
    }
}

// Handle view user data - FIXED: ganti dari 'konversi' ke 'nilai'
$selected_user = null;
$user_konversi_data = [];
if (isset($_GET['view_user']) && isset($_GET['nip'])) {
    $nip_view = $_GET['nip'];
    $query_selected = mysqli_query($conn, "SELECT * FROM pegawai WHERE nip='$nip_view'");
    $selected_user = mysqli_fetch_assoc($query_selected);
    
    if ($selected_user) {
        // Get all possible NIP variations
        $nip_primary = $selected_user['nip'];
        $nip_karpeg = $selected_user['no_seri_karpeg'];
        
        // Build search conditions for multiple NIP formats
        $search_conditions = array();
        if (!empty($nip_view)) $search_conditions[] = "nip='" . mysqli_real_escape_string($conn, $nip_view) . "'";
        if (!empty($nip_primary) && $nip_primary != $nip_view) $search_conditions[] = "nip='" . mysqli_real_escape_string($conn, $nip_primary) . "'";
        if (!empty($nip_karpeg) && $nip_karpeg != $nip_view && $nip_karpeg != $nip_primary) $search_conditions[] = "nip='" . mysqli_real_escape_string($conn, $nip_karpeg) . "'";
        
        // FIXED: Ganti tabel dari 'konversi' ke 'nilai'
        if (!empty($search_conditions)) {
            $where_clause = implode(' OR ', $search_conditions);
            $konversi_query = "SELECT * FROM nilai WHERE $where_clause ORDER BY tahun DESC, bulan ASC";
            
            // Debug information
            echo "<!-- Debug: Searching in 'nilai' table -->";
            echo "<!-- Debug: Query: $konversi_query -->";
            echo "<!-- Debug: Search conditions: " . $where_clause . " -->";
            
            $query_konversi = mysqli_query($conn, $konversi_query);
            
            if (!$query_konversi) {
                echo "<!-- Debug: Query error: " . mysqli_error($conn) . " -->";
            } else {
                $rows_found = mysqli_num_rows($query_konversi);
                echo "<!-- Debug: Query successful, rows found: $rows_found -->";
                
                while ($row = mysqli_fetch_assoc($query_konversi)) {
                    $user_konversi_data[] = $row;
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - PTPN IV</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: white;
            min-height: 100vh;
            margin: 0;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stats-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
            border-left: 4px solid #28a745;
            width: 200px;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.users {
            border-left-color: #007bff;
            margin-left: 30px;
        }
        
        .stat-card.konversi {
            border-left-color: #ffc107;
            margin-left: 30px;
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 8px;
        }
        
        .stat-card.users .stat-number {
            color: #007bff;
        }
        
        .stat-card.konversi .stat-number {
            color: #ffc107;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #666;
            font-weight: 500;
        }
        
        /* FULL VIEW TABLE - NO HORIZONTAL SCROLL */
        .table-scroll-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin: 20px 0;
            width: 100%;
            overflow: visible; /* Remove horizontal scroll */
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            table-layout: auto; /* Auto layout for responsive columns */
        }
        
        .users-table th {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .users-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #eee;
            color: #555;
            transition: background-color 0.3s ease;
            vertical-align: middle;
            font-size: 12px;
            text-align: center;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 150px; /* Prevent cells from getting too wide */
        }
        
        .users-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .users-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        /* RESPONSIVE COLUMN SPECIFICATIONS */
        .users-table th:nth-child(1),
        .users-table td:nth-child(1) { /* No */
            width: 4%;
            max-width: 40px;
        }
        
        .users-table th:nth-child(2),
        .users-table td:nth-child(2) { /* Nama */
            width: 18%;
            text-align: center; /* Nama tidak center */
            font-weight: 600;
            max-width: 200px;
        }
        
        .users-table th:nth-child(3),
        .users-table td:nth-child(3) { /* NIP */
            width: 12%;
            max-width: 150px;
        }
        
        .users-table th:nth-child(4),
        .users-table td:nth-child(4) { /* Tempat, Tanggal Lahir */
            width: 15%;
            max-width: 180px;
        }
        
        .users-table th:nth-child(5),
        .users-table td:nth-child(5) { /* Jenis Kelamin */
            width: 8%;
            max-width: 100px;
        }
        
        .users-table th:nth-child(6),
        .users-table td:nth-child(6) { /* Pangkat/Golongan */
            width: 13%;
            max-width: 160px;
        }
        
        .users-table th:nth-child(7),
        .users-table td:nth-child(7) { /* Jabatan */
            width: 12%;
            max-width: 150px;
        }
        
        .users-table th:nth-child(8),
        .users-table td:nth-child(8) { /* Unit Kerja */
            width: 10%;
            max-width: 120px;
        }
        
        .users-table th:nth-child(9),
        .users-table td:nth-child(9) { /* Instansi */
            width: 12%;
            max-width: 150px;
        }
        
        
        .users-table th:nth-child(11),
        .users-table td:nth-child(11) { /* Aksi */
            width: 10%;
            max-width: 120px;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: row;
            gap: 5px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            min-width: 45px;
            height: 32px;
            background: #f7fafc;
            color: #4a5568;
            box-shadow: none;
        }
        
        .btn-view {
            color: #2563eb;
            border-color: #dbeafe;
        }
        
        .btn-view:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
        }
        
        .btn-delete {
            color: #dc2626;
            border-color: #fecaca;
        }
        
        .btn-delete:hover {
            background: #fef2f2;
            border-color: #ef4444;
            color: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
        }
        
        .user-detail-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .close-modal {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        
        .close-modal:hover {
            background: #c82333;
        }
        
        .user-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .info-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #666;
            font-size: 14px;
            word-wrap: break-word;
        }
        
        .konversi-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .konversi-table th {
            background: #28a745;
            color: white;
            padding: 12px 10px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
        }
        
        .konversi-table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .konversi-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .search-container {
            margin-bottom: 20px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .search-input:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.3);
            transform: translateY(-2px);
        }
        
        .admin-header {
            background: #edede9;
            color: black;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 0 0 15px 15px;
        }
        
        .admin-header h1 {
            text-align: center;
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .admin-header p {
            text-align: center;
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        /* Container adjustments for full width */
        .tab-container {
            max-width: none;
            margin: 0 15px;
            padding: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stats-container {
                flex-direction: column;
                align-items: center;
            }
            
            .stat-card {
                width: 180px;
                height: 100px;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .stat-label {
                font-size: 0.9rem;
            }
            
            .modal-content {
                width: 95%;
                padding: 20px;
            }
            
            .user-info-grid {
                grid-template-columns: 1fr;
            }
            
            .search-container {
                margin-bottom: 15px;
            }
            
            .admin-header h1 {
                font-size: 1.5rem;
            }
            
            .tab-container {
                margin: 0 10px;
            }
            
            .table-scroll-wrapper {
                margin: 10px 0;
            }
            
            /* Mobile table adjustments - keep full view */
            .users-table th,
            .users-table td {
                padding: 10px 6px;
                font-size: 11px;
            }
            
            .users-table td:nth-child(2) { /* Nama tetap left align di mobile */
                text-align: center;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 2px;
            }
            
            .action-btn {
                width: 100%;
                min-width: 50px;
                padding: 6px 8px;
                font-size: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .tab-container {
                margin: 0 5px;
            }
            
            .users-table th,
            .users-table td {
                padding: 8px 4px;
                font-size: 10px;
            }
            
            .users-table td:nth-child(2) { /* Pastikan nama tetap left align */
                text-align: center;
                font-weight: 600;
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-left">
            <img src="ptpn.png" class="logo" alt="Logo">
            <span class="app-title">PT PERKEBUNAN NUSANTARA IV</span>
        </div>
        <div class="navbar-right">
            <div class="profile-menu">
                <img src="assets/images/profile.png" class="profile" alt="Profile">
                <div class="dropdown">
                    <a href="edit_profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($admin['nama']); ?></span>
                <span class="user-nip"><?php echo htmlspecialchars($admin['nip']); ?><span class="admin-badge">Admin</span></span>
            </div>
        </div>
    </nav>

    <!-- ADMIN HEADER -->
    <div class="admin-header">
        <h1>🛠 Dashboard Administrator</h1>
    </div>

    <!-- MAIN CONTENT -->
    <div class="tab-container">
        <!-- STATISTICS -->
        <div class="stats-container">
            <div class="stat-card users">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>
            <div class="stat-card konversi">
                <div class="stat-number"><?php echo $total_konversi; ?></div>
                <div class="stat-label">Total Data Konversi</div>
            </div>
        </div>

        <!-- SEARCH BAR -->
        <div class="search-container">
            <input type="text" class="search-input" id="searchInput" placeholder="🔍 Cari pengguna berdasarkan nama, NIP, atau instansi...">
        </div>

        <!-- USERS TABLE -->
        <div class="table-scroll-wrapper">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Tempat, Tanggal Lahir</th>
                        <th>Jenis Kelamin</th>
                        <th>Pangkat/Golongan</th>
                        <th>Jabatan</th>
                        <th>Unit Kerja</th>
                        <th>Instansi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php
                    mysqli_data_seek($query_users, 0);
                    $no = 1;
                    while ($user = mysqli_fetch_assoc($query_users)) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($user['nama'] ?: '-') . "</td>";
                        echo "<td>" . htmlspecialchars($user['no_seri_karpeg'] ?: $user['nip']) . "</td>";
                        echo "<td>" . htmlspecialchars($user['tempat_tanggal_lahir'] ?: '-') . "</td>";
                        echo "<td>" . htmlspecialchars($user['jenis_kelamin'] ?: '-') . "</td>";
                        echo "<td>" . htmlspecialchars($user['pangkat_golongan_tmt'] ?: '-') . "</td>";
                        echo "<td>" . htmlspecialchars($user['jabatan_tmt'] ?: '-') . "</td>";
                        echo "<td>" . htmlspecialchars($user['unit_kerja'] ?: '-') . "</td>";
                        echo "<td>" . htmlspecialchars($user['instansi'] ?: '-') . "</td>";
                        echo "<td>";
                        echo "<div class='action-buttons'>";
                        echo "<a href='?view_user=1&nip=" . urlencode($user['nip']) . "' class='action-btn btn-view' title='Lihat Detail'>👁</a>";
                        echo "<a href='?delete_user=1&nip=" . urlencode($user['nip']) . "' class='action-btn btn-delete' title='Hapus User' onclick='return confirm(\"Yakin ingin menghapus user " . htmlspecialchars($user['nama']) . "?\")'>🗑</a>";
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    
                    if ($total_users == 0) {
                        echo "<tr><td colspan='11' class='no-data'>Belum ada pengguna yang terdaftar</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- USER DETAIL MODAL -->
    <?php if ($selected_user): ?>
    <div class="user-detail-modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Detail Pengguna: <?php echo htmlspecialchars($selected_user['nama']); ?></h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            
            <!-- USER INFO -->
            <div class="user-info-grid">
                <div class="info-card">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-value"><?php echo htmlspecialchars($selected_user['nama'] ?: '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">NIP/No. Seri Karpeg</div>
                    <div class="info-value"><?php echo htmlspecialchars($selected_user['no_seri_karpeg'] ?: $selected_user['nip']); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Tempat, Tanggal Lahir</div>
                    <div class="info-value"><?php echo htmlspecialchars($selected_user['tempat_tanggal_lahir'] ?: '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Jenis Kelamin</div>
                    <div class="info-value"><?php echo htmlspecialchars($selected_user['jenis_kelamin'] ?: '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Pangkat/Golongan TMT</div>
                    <div class="info-value"><?php echo htmlspecialchars($selected_user['pangkat_golongan_tmt'] ?: '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Jabatan TMT</div>
                    <div class="info-value"><?php echo htmlspecialchars($selected_user['jabatan_tmt'] ?: '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Unit Kerja</div>
                    <div class="info-value"><?php echo htmlspecialchars($selected_user['unit_kerja'] ?: '-'); ?></div>
                </div>
                <div class="info-card">
                    <div class="info-label">Instansi</div>
                    <div class="info-value"><?php echo htmlspecialchars($selected_user['instansi'] ?: '-'); ?></div>
                </div>
            </div>
            
            <!-- KONVERSI DATA - FIXED: Updated for 'nilai' table -->
            <h3>📊 Data Konversi</h3>
            <!-- Debug info -->
            <p style="font-size: 12px; color: #666; margin: 10px 0;">
                Total data konversi ditemukan: <?php echo count($user_konversi_data); ?> | 
                NIP yang dicari: <?php echo htmlspecialchars($nip_view); ?>
            </p>
            
            <?php if (count($user_konversi_data) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="konversi-table">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Periode</th>
                            <th>Predikat</th>
                            <th>Persentase</th>
                            <th>Koefisien</th>
                            <th>Total Angka Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_konversi_data as $konversi): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($konversi['tahun']); ?></td>
                            <td>
                                <?php 
                                // Handle different period formats
                                if (isset($konversi['periode'])) {
                                    echo htmlspecialchars($konversi['periode']);
                                } elseif (isset($konversi['bulan'])) {
                                    echo htmlspecialchars($konversi['bulan']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($konversi['predikat'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($konversi['persentase'] ?? '-'); ?></td>
                            <td><?php echo isset($konversi['koefisien']) ? number_format($konversi['koefisien'], 2) : '-'; ?></td>
                            <td><strong><?php echo isset($konversi['angka_kredit']) ? number_format($konversi['angka_kredit'], 3) : '-'; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">Pengguna ini belum memiliki data konversi</div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Enhanced search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#usersTableBody tr');
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let found = false;
                
                if (cells.length === 1 && cells[0].classList.contains('no-data')) {
                    return;
                }
                
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                    }
                });
                
                if (found) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            const searchInput = document.getElementById('searchInput');
            if (searchTerm && visibleCount === 0) {
                searchInput.style.borderColor = '#dc3545';
                searchInput.style.backgroundColor = '#fff5f5';
            } else {
                searchInput.style.borderColor = searchTerm ? '#28a745' : '#ddd';
                searchInput.style.backgroundColor = 'white';
            }
        });

        function closeModal() {
            const modal = document.getElementById('userModal');
            if (modal) {
                modal.style.opacity = '0';
                setTimeout(() => {
                    window.location.href = 'admin_dashboard.php';
                }, 300);
            }
        }

        document.addEventListener('click', function(event) {
            const modal = document.getElementById('userModal');
            if (modal && event.target === modal) {
                closeModal();
            }
        });

        setInterval(function() {
            if (!document.getElementById('userModal')) {
                location.reload();
            }
        }, 300000);

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('PERHATIAN: Menghapus user akan menghapus semua data konversi mereka. Lanjutkan?')) {
                    e.preventDefault();
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const finalNumber = parseInt(stat.textContent);
                let currentNumber = 0;
                const increment = finalNumber / 50;
                
                const timer = setInterval(() => {
                    currentNumber += increment;
                    if (currentNumber >= finalNumber) {
                        stat.textContent = finalNumber;
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(currentNumber);
                    }
                }, 20);
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
            
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
                document.getElementById('searchInput').select();
            }
        });
    </script>
</body>
</html>