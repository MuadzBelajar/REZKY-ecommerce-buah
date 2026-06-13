<?php
/**
 * ================================================
 * LOGIN PAGE - SUPERMARKET BUAH E-COMMERCE
 * ================================================
 * File: auth/login.php
 * Layout: Split-screen (form kiri, promo kanan)
 * PLAIN TEXT PASSWORD VERSION
 * ================================================
 */

session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../pages/catalog.php");
    }
    exit();
}

require_once '../config/database.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    if (empty($username)) {
        $error_message = 'Username atau email tidak boleh kosong';
    } elseif (empty($password)) {
        $error_message = 'Password tidak boleh kosong';
    } else {
        try {
            $sql = "SELECT * FROM users 
                    WHERE (username = ? OR email = ?) 
                    AND status = 'active' 
                    LIMIT 1";
            $user = fetchOne($sql, [$username, $username]);
            
            if (!$user) {
                $error_message = 'Username atau email tidak ditemukan';
            } elseif ($password !== $user['password']) {
                $error_message = 'Password salah';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../pages/catalog.php");
                }
                exit();
            }
        } catch (Exception $e) {
            error_log("Login Error: " . $e->getMessage());
            $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Supermarket Buah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #F5FBE6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1200px;
            width: 100%;
            margin: 2rem;
            background: white;
            border-radius: 32px;
            box-shadow: 0 20px 40px -12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        /* Panel Kiri - Form */
        .login-form-panel {
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-header {
            margin-bottom: 2rem;
        }
        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 800;
            color: #2C3A1F;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        .login-subtitle {
            color: #5A6E4A;
            font-size: 0.9rem;
        }
        /* Alert */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
        }
        .alert-error {
            background: #FEF2F2;
            color: #DC2626;
            border-left: 4px solid #DC2626;
        }
        .alert-success {
            background: #F0FDF4;
            color: #16A34A;
            border-left: 4px solid #16A34A;
        }
        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #2C3A1F;
            margin-bottom: 0.5rem;
        }
        .input-wrapper {
            position: relative;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #DDE8CE;
            border-radius: 16px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: 0.2s;
            background: white;
        }
        .form-input:focus {
            outline: none;
            border-color: #7BA05B;
            box-shadow: 0 0 0 3px rgba(123,160,91,0.1);
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #8A9A7A;
            font-size: 1rem;
        }
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .form-checkbox {
            width: 1rem;
            height: 1rem;
            accent-color: #7BA05B;
        }
        .checkbox-label {
            font-size: 0.8rem;
            color: #5A6E4A;
        }
        .btn-login {
            width: 100%;
            padding: 0.85rem;
            background: #7BA05B;
            color: white;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-login:hover {
            background: #5C7E42;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(92,126,66,0.2);
        }
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .demo-box {
            background: #E8F3DA;
            border-radius: 20px;
            padding: 1rem 1.25rem;
            margin-top: 1.5rem;
        }
        .demo-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: #5C7E42;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .demo-item {
            font-size: 0.8rem;
            font-family: monospace;
            color: #2C3A1F;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            color: #7BA05B;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .back-link:hover {
            gap: 0.75rem;
            color: #5C7E42;
        }
        /* Panel Kanan - Ilustrasi/Promo */
        .login-illustration {
            background: linear-gradient(145deg, #7BA05B 0%, #5C7E42 100%);
            padding: 3rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .illustration-content {
            position: relative;
            z-index: 2;
        }
        .illustration-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1rem;
        }
        .illustration-desc {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .fruit-highlights {
            list-style: none;
            margin-top: 1rem;
        }
        .fruit-highlights li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }
        .fruit-icon {
            font-size: 1.25rem;
        }
        .bg-pattern {
            position: absolute;
            bottom: -20px;
            right: -20px;
            font-size: 15rem;
            opacity: 0.1;
            pointer-events: none;
        }
        /* Responsive */
        @media (max-width: 800px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                margin: 1rem;
                border-radius: 24px;
            }
            .login-illustration {
                display: none;
            }
            .login-form-panel {
                padding: 2rem;
            }
        }
        @media (max-width: 480px) {
            .login-form-panel {
                padding: 1.5rem;
            }
            .login-title {
                font-size: 1.5rem;
            }
        }
        .shake {
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%,100%{transform:translateX(0)}
            10%,30%,50%,70%,90%{transform:translateX(-5px)}
            20%,40%,60%,80%{transform:translateX(5px)}
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <!-- Kiri: Form Login -->
    <div class="login-form-panel">
        <div class="login-header">
            <h1 class="login-title">Selamat Datang</h1>
            <p class="login-subtitle">Masuk ke akun Anda untuk melanjutkan belanja</p>
        </div>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-error">
            <span>⚠️</span>
            <span><?php echo htmlspecialchars($error_message); ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <span>✓</span>
            <span><?php echo htmlspecialchars($success_message); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label class="form-label" for="username">Username atau Email</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" class="form-input"
                           placeholder="Masukkan username atau email"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="form-input"
                           placeholder="Masukkan password" required>
                    <button type="button" class="password-toggle" id="togglePassword">👁️</button>
                </div>
            </div>

            <div class="checkbox-wrapper">
                <input type="checkbox" id="remember" name="remember" class="form-checkbox">
                <label for="remember" class="checkbox-label">Ingat saya</label>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <span class="spinner"></span>
                <span class="btn-text">Masuk</span>
            </button>
        </form>

        <div class="demo-box">
            <div class="demo-title">Akun Demo (Plain Text)</div>
            <div class="demo-item">Admin: admin / admin123</div>
            <div class="demo-item">Customer: budi / budi123</div>
            <div class="demo-item">Customer: siti / siti123</div>
        </div>

        <a href="../index.php" class="back-link">← Kembali ke Beranda</a>
    </div>

    <!-- Kanan: Ilustrasi Promo -->
    <div class="login-illustration">
        <div class="illustration-content">
            <div class="illustration-title">Buah Segar<br>Langsung dari Kebun</div>
            <p class="illustration-desc">Nikmati kesegaran buah pilihan terbaik dari petani lokal dan impor berkualitas premium, dikirim langsung ke rumah Anda.</p>
            <ul class="fruit-highlights">
                <li><span class="fruit-icon">🍎</span> Apel Fuji Renyah</li>
                <li><span class="fruit-icon">🥭</span> Mangga Harum Manis</li>
                <li><span class="fruit-icon">🍊</span> Jeruk Medan Segar</li>
                <li><span class="fruit-icon">🍇</span> Anggur Red Globe</li>
            </ul>
        </div>
        <div class="bg-pattern">🍎</div>
    </div>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    }

    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            const spinner = submitBtn.querySelector('.spinner');
            const btnText = submitBtn.querySelector('.btn-text');
            if (spinner) spinner.style.display = 'block';
            if (btnText) btnText.style.display = 'none';
        });
    }

    const errorAlert = document.querySelector('.alert-error');
    if (errorAlert) {
        const formPanel = document.querySelector('.login-form-panel');
        formPanel.classList.add('shake');
        setTimeout(() => formPanel.classList.remove('shake'), 500);
        const lastInput = passwordInput.value ? passwordInput : document.getElementById('username');
        if (lastInput) lastInput.focus();
    }
</script>
</body>
</html>