<?php
/**
 * INDEX PAGE - SUPERMARKET BUAH
 * Layout: Hero split (kiri teks, kanan ilustrasi) - tanpa showcase
 * Style: Modern minimalis, warna palet asli
 */
session_start();
require_once 'auth/check_session.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Cek login user
$user = getLoggedInUser();
$cart_count = getCartItemCount();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Supermarket Buah — Kesegaran Alami Setiap Hari</title>
    <meta name="description" content="Toko buah online dengan buah segar pilihan terbaik dari petani lokal dan impor berkualitas premium.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #7BA05B;
            --primary-dark: #5C7E42;
            --primary-light: #E8F3DA;
            --secondary: #E9B35F;
            --text: #2C3A1F;
            --text-light: #5A6E4A;
            --text-lighter: #8A9A7A;
            --bg: #F5FBE6;
            --bg-alt: #EFF8E0;
            --border: #DDE8CE;
            --white: #FFFFFF;
            --error: #DC2626;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.03), 0 1px 1px rgba(0,0,0,0.02);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.05);
            --shadow-lg: 0 12px 24px -8px rgba(0,0,0,0.08);
            --radius-md: 12px;
            --radius-lg: 20px;
            --t: 200ms ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* ----- NAVBAR ----- */
        .navbar {
            position: sticky;
            top: 0;
            background: rgba(245, 251, 230, 0.96);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            padding: 0.75rem 0;
        }
        .nav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            letter-spacing: -0.3px;
            text-decoration: none;
        }
        .logo span {
            color: var(--primary);
            font-weight: 700;
        }
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            font-weight: 500;
            color: var(--text-light);
            transition: var(--t);
            font-size: 0.9rem;
        }
        .nav-links a:hover {
            color: var(--primary);
        }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .cart-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -12px;
            background: var(--error);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            min-width: 18px;
            height: 18px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }
        .btn-login, .btn-logout {
            padding: 0.4rem 1rem;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--t);
        }
        .btn-login {
            background: var(--primary);
            color: white;
            border: none;
        }
        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .btn-logout {
            border: 1.5px solid var(--border);
            color: var(--text-light);
        }
        .btn-logout:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }
        .user-greeting {
            font-size: 0.85rem;
            color: var(--text-light);
        }
        .user-greeting strong {
            color: var(--text);
            font-weight: 600;
        }
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
        }
        .hamburger span {
            width: 24px;
            height: 2px;
            background: var(--text);
            border-radius: 2px;
        }

        /* ----- HERO SPLIT ----- */
        .hero {
            padding: 4rem 0 5rem;
            background: linear-gradient(135deg, var(--bg) 0%, var(--white) 100%);
            min-height: calc(100vh - 70px);
            display: flex;
            align-items: center;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.2;
            color: var(--text);
            margin-bottom: 1rem;
        }
        .hero-highlight {
            color: var(--primary);
            border-bottom: 3px solid var(--secondary);
            display: inline-block;
        }
        .hero-desc {
            font-size: 1rem;
            color: var(--text-light);
            margin-bottom: 2rem;
            max-width: 90%;
        }
        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .btn-primary, .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--t);
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .btn-outline {
            border: 1.5px solid var(--border);
            color: var(--text);
        }
        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }
        .hero-stats {
            display: flex;
            gap: 2rem;
        }
        .stat-item {
            text-align: left;
        }
        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.8rem;
            color: var(--text-lighter);
        }
        .hero-image {
            text-align: center;
        }
        .hero-image img {
            max-width: 100%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        /* ----- FOOTER ----- */
        .footer {
            background: var(--primary-dark);
            color: white;
            padding: 2rem 0;
            text-align: center;
            font-size: 0.8rem;
        }
        .footer p {
            opacity: 0.8;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }
            .hero-content h1 {
                font-size: 2.5rem;
            }
            .hero-desc {
                max-width: 100%;
            }
            .hero-stats {
                justify-content: center;
            }
            .hero-buttons {
                justify-content: center;
            }
            .stat-item {
                text-align: center;
            }
        }
        @media (max-width: 768px) {
            .container {
                padding: 0 1.25rem;
            }
            .nav-links {
                display: none;
            }
            .hamburger {
                display: flex;
            }
            .nav-inner {
                padding: 0 1rem;
            }
        }
        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-inner">
        <a href="index.php" class="logo">Supermarket<span>Buah</span></a>
        <div class="nav-links">
            <a href="index.php">Beranda</a>
            <a href="pages/catalog.php">Belanja</a>
            <a href="pages/my_orders.php">Pesanan Saya</a>
        </div>
        <div class="nav-actions">
            <?php if ($user): ?>
                <span class="user-greeting">Halo, <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong></span>
                <a href="pages/cart.php" class="cart-link">
                    Keranjang
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge" id="cartBadge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="auth/logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="auth/login.php" class="btn-login">Masuk</a>
                <a href="auth/register.php" class="btn-logout" style="border-color: var(--primary); color: var(--primary);">Daftar</a>
            <?php endif; ?>
        </div>
        <button class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- HERO SPLIT (tanpa showcase) -->
<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <h1>Buah Segar<br><span class="hero-highlight">Langsung dari Kebun</span></h1>
                <p class="hero-desc">Nikmati kesegaran buah pilihan terbaik dari petani lokal dan impor berkualitas premium, dikirim langsung ke rumah Anda.</p>
                <div class="hero-buttons">
                    <a href="pages/catalog.php" class="btn-primary">Belanja Sekarang →</a>
                    <a href="pages/catalog.php" class="btn-outline">Lihat Produk</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number">15.000+</span>
                        <span class="stat-label">Pelanggan</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">100+</span>
                        <span class="stat-label">Jenis Buah</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Terjamin</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=600" alt="Buah segar" loading="lazy">
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Supermarket Buah. Kesegaran Alami Setiap Hari.</p>
    </div>
</footer>

<script>
    // Hamburger menu untuk mobile
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.querySelector('.nav-links');
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('show');
            hamburger.classList.toggle('open');
        });
        // Tambahkan style untuk mobile menu
        const style = document.createElement('style');
        style.textContent = `
            @media (max-width: 768px) {
                .nav-links {
                    position: fixed;
                    top: 60px;
                    left: -100%;
                    width: 100%;
                    background: var(--bg);
                    flex-direction: column;
                    align-items: center;
                    padding: 2rem 0;
                    gap: 1.5rem;
                    transition: left 0.3s ease;
                    box-shadow: var(--shadow-lg);
                    z-index: 99;
                }
                .nav-links.show {
                    left: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    // Badge cart update jika diperlukan
    function updateBadge(count) {
        const badge = document.getElementById('cartBadge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    window.updateBadge = updateBadge;
</script>
</body>
</html>