<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../config/db.php';

$error_message = '';
$login_attempted = false;

if (isset($_POST['login'])) {
    $login_attempted = true;
    $nip = $_POST['nip'];
    $password = $_POST['password'];

    $query = "SELECT * FROM pegawai WHERE nip='$nip'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    
        if (password_verify($password, $row['password'])) {
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['nip'] = $row['nip'];
            $_SESSION['role'] = $row['role'] ?? 'user';
            
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error_message = 'Password salah!';
        }
    } else {
        $error_message = 'NIP tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Penilaian</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #2E7D32 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: float 20s ease-in-out infinite;
            z-index: 1;
        }

        body::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 219, 226, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 174, 188, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 119, 198, 0.3) 0%, transparent 50%);
            z-index: 1;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        /* Login container */
        .login-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1000px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 25px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
            transform-style: preserve-3d;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(50px) rotateX(20deg);
            }
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0deg);
            }
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
            border-radius: 25px;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .login-container:hover::before {
            opacity: 1;
            animation: shimmer 2s ease-in-out;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Logo and header */
        .login-header {
            margin-bottom: 40px;
        }

        .logo-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: pulse 2s ease-in-out infinite alternate;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4); }
            100% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(102, 126, 234, 0.6); }
        }

        .logo-container i {
            font-size: 35px;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .login-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .login-subtitle {
            color: #718096;
            font-size: 1rem;
            font-weight: 400;
            margin-bottom: 10px;
        }

        /* Form styling */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 18px;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .form-input {
            width: 100%;
            padding: 18px 20px 18px 55px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 15px;
            font-size: 16px;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            color: #2d3748;
            transition: all 0.3s ease;
            position: relative;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 
                0 0 0 4px rgba(102, 126, 234, 0.1),
                0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .form-input:focus + i {
            color: #764ba2;
            transform: translateY(-50%) scale(1.1);
        }

        .form-input::placeholder {
            color: #a0aec0;
            font-weight: 400;
        }

        /* Login button */
        .login-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: all 0.5s;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Links */
        .login-links {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .auth-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            position: relative;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .auth-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #764ba2;
            transform: translateY(-1px);
        }

        .auth-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .auth-link:hover::after {
            width: 80%;
        }

        /* Alert styling */
        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 12px;
            font-weight: 500;
            position: relative;
            animation: slideDown 0.3s ease-out;
            backdrop-filter: blur(10px);
        }

        .alert-error {
            background: linear-gradient(135deg, rgba(245, 101, 101, 0.15), rgba(229, 62, 62, 0.15));
            border: 2px solid rgba(245, 101, 101, 0.3);
            color: #c53030;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(72, 187, 120, 0.15), rgba(56, 161, 105, 0.15));
            border: 2px solid rgba(72, 187, 120, 0.3);
            color: #2f855a;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading state */
        .loading {
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 25px;
                border-radius: 20px;
            }

            .login-title {
                font-size: 1.8rem;
            }

            .form-input {
                padding: 16px 18px 16px 50px;
                font-size: 15px;
            }

            .login-btn {
                padding: 16px;
                font-size: 15px;
            }

            .logo-container {
                width: 70px;
                height: 70px;
            }

            .logo-container i {
                font-size: 30px;
            }
        }

        /* Additional animations */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <div class="logo-container">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1 class="login-title">Selamat Datang</h1>
                <p class="login-subtitle">Silahkan masuk ke akun Anda</p>
            </div>

            <!-- Alert container for PHP messages -->
            <div id="alert-container">
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" class="login-form">
                <div class="input-group">
                    <input type="text" name="nip" class="form-input" placeholder="Masukkan NIP Anda" required autocomplete="username" value="<?php echo isset($_POST['nip']) ? htmlspecialchars($_POST['nip']) : ''; ?>">
                    <i class="fas fa-id-card"></i>
                </div>

                <div class="input-group">
                    <input type="password" name="password" class="form-input" placeholder="Masukkan Password" required autocomplete="current-password">
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" name="login" class="login-btn" id="loginButton">
                    Masuk
                </button>
            </form>

            <div class="login-links">
                <a href="register.php" class="auth-link">
                    <i class="fas fa-user-plus"></i> Belum punya akun? Daftar di sini
                </a>
                <a href="#" class="auth-link" onclick="showAlert('Fitur lupa password akan segera tersedia!', 'info')">
                    <i class="fas fa-key"></i> Lupa Password?
                </a>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form interactions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const inputs = document.querySelectorAll('.form-input');
            const loginBtn = document.getElementById('loginButton');

            // Add fade-in animation to container
            document.querySelector('.login-container').classList.add('fade-in');

            // Enhanced input interactions
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });

                // Real-time validation feedback
                input.addEventListener('input', function() {
                    if (this.value.length > 0) {
                        this.style.borderColor = '#48bb78';
                        this.nextElementSibling.style.color = '#48bb78';
                    } else {
                        this.style.borderColor = 'rgba(102, 126, 234, 0.2)';
                        this.nextElementSibling.style.color = '#667eea';
                    }
                });
            });

            // Form submission with loading state
            form.addEventListener('submit', function(e) {
                // Show loading state but don't prevent form submission
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;
                loginBtn.textContent = 'Memproses...';
            });

            // Keyboard navigation enhancement
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.activeElement.classList.contains('form-input')) {
                    const inputs = Array.from(document.querySelectorAll('.form-input'));
                    const currentIndex = inputs.indexOf(document.activeElement);
                    
                    if (currentIndex < inputs.length - 1) {
                        e.preventDefault();
                        inputs[currentIndex + 1].focus();
                    }
                }
            });
        });

        // Alert function for JavaScript alerts
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const iconClass = type === 'success' ? 'check-circle' : 'exclamation-triangle';
            
            alertContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    <i class="fas fa-${iconClass}"></i>
                    ${message}
                </div>
            `;

            // Auto hide after 5 seconds
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alertContainer.innerHTML = '', 300);
                }
            }, 5000);
        }
    </script>
</body>
</html>