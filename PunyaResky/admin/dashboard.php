<?php
/**
 * ADMIN DASHBOARD - SUPERMARKET BUAH
 * Hanya menampilkan pesanan terbaru (tanpa tombol tambah produk)
 */
session_start();
require_once '../auth/check_session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();
$admin = getLoggedInUser();

// Recent orders
$recent_orders = fetchAll("SELECT o.*, u.nama_lengkap, u.username 
                          FROM orders o 
                          JOIN users u ON o.user_id = u.id 
                          ORDER BY o.created_at DESC 
                          LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Supermarket Buah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #F5FBE6;
            color: #2C3A1F;
            line-height: 1.5;
        }
        /* TOP NAVBAR */
        .topbar {
            background: white;
            border-bottom: 1px solid #DDE8CE;
            padding: 0.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: #5C7E42;
            text-decoration: none;
        }
        .logo span {
            color: #7BA05B;
        }
        .nav-menu {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-link {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: #5A6E4A;
            font-weight: 500;
            border-radius: 40px;
            transition: 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            background: #E8F3DA;
            color: #7BA05B;
        }
        .btn-logout {
            background: #7BA05B;
            color: white;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-logout:hover {
            background: #5C7E42;
            transform: translateY(-1px);
        }
        .user-greeting {
            font-size: 0.85rem;
            color: #5A6E4A;
        }
        /* MAIN CONTAINER */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #2C3A1F;
        }
        /* CARD PESANAN */
        .card {
            background: white;
            border-radius: 24px;
            border: 1px solid #DDE8CE;
            overflow: hidden;
        }
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #DDE8CE;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: #8A9A7A;
            text-transform: uppercase;
            border-bottom: 1px solid #DDE8CE;
        }
        .data-table td {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            border-bottom: 1px solid #DDE8CE;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .order-id {
            font-weight: 600;
            color: #7BA05B;
        }
        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-pending { background: #FEF3C7; color: #D97706; }
        .status-processing { background: #DBEAFE; color: #2563EB; }
        .status-shipped { background: #E0E7FF; color: #4F46E5; }
        .status-delivered { background: #D1FAE5; color: #16A34A; }
        .status-cancelled { background: #FEE2E2; color: #DC2626; }
        .empty-orders {
            text-align: center;
            padding: 2rem;
            color: #8A9A7A;
        }
        @media (max-width: 768px) {
            .topbar {
                padding: 0.75rem 1rem;
            }
            .container {
                padding: 1rem;
            }
            .data-table th, .data-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>

<div class="topbar">
    <a href="dashboard.php" class="logo">Supermarket<span>Buah</span></a>
    <div class="nav-menu">
        <a href="dashboard.php" class="nav-link active">Dashboard</a>
        <a href="products.php" class="nav-link">Produk</a>
        <a href="orders.php" class="nav-link">Pesanan</a>
        <span class="user-greeting">Halo, <?php echo htmlspecialchars($admin['nama_lengkap']); ?></span>
        <a href="../pages/catalog.php" class="nav-link">Lihat Website</a>
        <a href="../auth/logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p style="color: #5A6E4A;">Selamat datang kembali, <?php echo htmlspecialchars($admin['nama_lengkap']); ?>!</p>
    </div>

    <!-- HANYA PESANAN TERBARU -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Pesanan Terbaru</h2>
            <a href="orders.php" style="font-size:0.8rem; color:#7BA05B; text-decoration:none;">Lihat Semua →</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (count($recent_orders) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><span class="order-id">#<?php echo $order['id']; ?></span></td>
                        <td>
                            <div><?php echo htmlspecialchars($order['nama_pemesan']); ?></div>
                            <div style="font-size:0.7rem; color:#8A9A7A;"><?php echo htmlspecialchars($order['username']); ?></div>
                        </td>
                        <td class="price"><?php echo formatRupiah($order['total_harga']); ?></td>
                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span></td>
                        <td><?php echo formatTanggal($order['created_at'], 'pendek'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-orders">Belum ada pesanan</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>