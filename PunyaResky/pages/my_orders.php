<?php
/**
 * ================================================
 * MY ORDERS - SUPERMARKET BUAH E-COMMERCE
 * ================================================
 * File: pages/my_orders.php
 * Layout: Kartu horisontal dengan aksen warna status (desain baru)
 * Style: Minimalis modern, tanpa emoji
 */

session_start();
require_once '../auth/check_session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkLogin();

$user       = getLoggedInUser();
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

$orders = fetchAll(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC",
    [$user['id']]
);

function statusInfo($status) {
    return match($status) {
        'pending'    => ['label' => 'Menunggu',  'color' => '#D97706', 'bg' => '#FEF3C7', 'border' => '#F59E0B'],
        'processing' => ['label' => 'Diproses',  'color' => '#2563EB', 'bg' => '#DBEAFE', 'border' => '#3B82F6'],
        'shipped'    => ['label' => 'Dikirim',   'color' => '#4F46E5', 'bg' => '#E0E7FF', 'border' => '#6366F1'],
        'delivered'  => ['label' => 'Selesai',   'color' => '#16A34A', 'bg' => '#D1FAE5', 'border' => '#22C55E'],
        'cancelled'  => ['label' => 'Dibatalkan','color' => '#DC2626', 'bg' => '#FEE2E2', 'border' => '#EF4444'],
        default      => ['label' => ucfirst($status), 'color' => '#666', 'bg' => '#f5f5f5', 'border' => '#999'],
    };
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Supermarket Buah</title>
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
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        /* NAVBAR */
        .navbar {
            background: rgba(245, 251, 230, 0.96);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #DDE8CE;
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0.75rem 0;
        }
        .nav-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        .nav-logo {
            text-decoration: none;
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #7BA05B;
            letter-spacing: -0.3px;
        }
        .nav-logo span:first-child {
            font-weight: 800;
            color: #5C7E42;
        }
        .nav-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        .btn-nav {
            padding: 0.5rem 1.25rem;
            border-radius: 40px;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
            border: 1.5px solid #7BA05B;
            background: transparent;
            color: #7BA05B;
        }
        .btn-nav:hover {
            background: #E8F3DA;
            transform: translateY(-1px);
        }
        .btn-nav.solid {
            background: #7BA05B;
            color: white;
            border: none;
        }
        .btn-nav.solid:hover {
            background: #5C7E42;
        }
        .cart-wrap {
            position: relative;
        }
        .cart-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #DC2626;
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            min-width: 18px;
            height: 18px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }
        /* PAGE HEADER */
        .page-header {
            padding: 2rem 0 1rem;
            margin-bottom: 1.5rem;
        }
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #2C3A1F;
            letter-spacing: -0.02em;
        }
        .page-sub {
            color: #5A6E4A;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        /* FILTER TABS (client-side) */
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            border-bottom: 1px solid #DDE8CE;
            padding-bottom: 0.75rem;
        }
        .filter-tab {
            padding: 0.4rem 1rem;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 500;
            background: white;
            border: 1px solid #DDE8CE;
            cursor: pointer;
            transition: all 0.2s;
            color: #5A6E4A;
        }
        .filter-tab.active {
            background: #7BA05B;
            border-color: #7BA05B;
            color: white;
        }
        .filter-tab:hover:not(.active) {
            background: #E8F3DA;
            border-color: #7BA05B;
        }
        /* ORDERS LIST */
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding-bottom: 3rem;
        }
        /* NEW ORDER CARD - horizontal dengan aksen kiri */
        .order-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            transition: all 0.2s;
            overflow: hidden;
            display: flex;
            border-left: 6px solid;
        }
        .order-card:hover {
            box-shadow: 0 8px 20px -8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .order-left {
            flex: 1;
            padding: 1.25rem 1.5rem;
        }
        .order-right {
            width: 220px;
            background: #F9FDF2;
            padding: 1.25rem 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: right;
            border-left: 1px solid #DDE8CE;
        }
        .order-header {
            display: flex;
            align-items: baseline;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 0.75rem;
        }
        .order-id {
            font-weight: 700;
            font-size: 0.9rem;
            color: #7BA05B;
            background: #E8F3DA;
            padding: 0.2rem 0.7rem;
            border-radius: 30px;
        }
        .order-date {
            font-size: 0.75rem;
            color: #8A9A7A;
        }
        .product-list {
            margin-top: 0.5rem;
        }
        .product-item {
            font-size: 0.85rem;
            color: #5A6E4A;
            margin-bottom: 0.25rem;
            display: flex;
            gap: 0.5rem;
        }
        .product-name {
            font-weight: 500;
        }
        .product-qty {
            color: #8A9A7A;
        }
        .more-items {
            font-size: 0.75rem;
            color: #8A9A7A;
            margin-top: 0.25rem;
            font-style: italic;
        }
        .order-status {
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 40px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .order-total {
            font-weight: 700;
            font-size: 1.1rem;
            color: #7BA05B;
            margin: 0.5rem 0;
        }
        .btn-detail {
            display: inline-block;
            padding: 0.4rem 1rem;
            background: #7BA05B;
            color: white;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }
        .btn-detail:hover {
            background: #5C7E42;
        }
        /* Empty state */
        .empty-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #DDE8CE;
            text-align: center;
            padding: 3rem;
        }
        .empty-icon {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }
        .empty-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .empty-text {
            color: #5A6E4A;
            margin-bottom: 1.5rem;
        }
        .btn-shop {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            background: #7BA05B;
            color: white;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
        }
        /* Responsive */
        @media (max-width: 700px) {
            .order-card {
                flex-direction: column;
                border-left: none;
                border-top: 4px solid;
            }
            .order-right {
                width: 100%;
                text-align: left;
                border-left: none;
                border-top: 1px solid #DDE8CE;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }
            .order-right .order-total {
                margin: 0;
            }
            .btn-detail {
                margin-top: 0;
            }
            .container {
                padding: 0 1rem;
            }
            .nav-inner {
                padding: 0 1rem;
            }
        }
        @media (max-width: 500px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.3rem;
            }
            .order-right {
                flex-wrap: wrap;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-inner">
            <a href="../index.php" class="nav-logo">
                <span>Supermarket</span><span>Buah</span>
            </a>
            <div class="nav-actions">
                <a href="catalog.php" class="btn-nav">Belanja</a>
                <div class="cart-wrap">
                    <a href="cart.php" class="btn-nav">Keranjang
                        <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <a href="../auth/logout.php" class="btn-nav solid">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Pesanan Saya</h1>
            <p class="page-sub">Halo, <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong> — kelola dan lacak pesanan Anda</p>
        </div>

        <?php if (count($orders) > 0): ?>
        <!-- Filter tabs (client-side) -->
        <div class="filter-tabs" id="filterTabs">
            <button class="filter-tab active" data-status="all">Semua</button>
            <button class="filter-tab" data-status="pending">Menunggu</button>
            <button class="filter-tab" data-status="processing">Diproses</button>
            <button class="filter-tab" data-status="shipped">Dikirim</button>
            <button class="filter-tab" data-status="delivered">Selesai</button>
            <button class="filter-tab" data-status="cancelled">Dibatalkan</button>
        </div>

        <div class="orders-list" id="ordersList">
            <?php foreach ($orders as $order):
                $si = statusInfo($order['status']);
                $items = fetchAll(
                    "SELECT nama_buah, jumlah_kg, subtotal FROM order_items WHERE order_id = ?",
                    [$order['id']]
                );
                $preview = array_slice($items, 0, 2);
                $extra = count($items) - 2;
            ?>
            <div class="order-card" data-status="<?php echo $order['status']; ?>" style="border-left-color: <?php echo $si['border']; ?>; border-top-color: <?php echo $si['border']; ?>;">
                <div class="order-left">
                    <div class="order-header">
                        <span class="order-id">#<?php echo $order['id']; ?></span>
                        <span class="order-date"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?> WIB</span>
                    </div>
                    <div class="product-list">
                        <?php foreach ($preview as $item): ?>
                        <div class="product-item">
                            <span class="product-name"><?php echo htmlspecialchars($item['nama_buah']); ?></span>
                            <span class="product-qty">× <?php echo $item['jumlah_kg']; ?> kg</span>
                        </div>
                        <?php endforeach; ?>
                        <?php if ($extra > 0): ?>
                        <div class="more-items">+ <?php echo $extra; ?> item lainnya</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="order-right">
                    <div>
                        <div class="order-status" style="background: <?php echo $si['bg']; ?>; color: <?php echo $si['color']; ?>;">
                            <?php echo $si['label']; ?>
                        </div>
                        <div class="order-total">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></div>
                    </div>
                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-detail">Detail →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-card">
            <div class="empty-icon">📋</div>
            <h2 class="empty-title">Belum Ada Pesanan</h2>
            <p class="empty-text">Anda belum pernah memesan buah. Yuk mulai belanja sekarang!</p>
            <a href="catalog.php" class="btn-shop">Mulai Belanja →</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Client-side filter berdasarkan status
        const filterTabs = document.querySelectorAll('.filter-tab');
        const orderCards = document.querySelectorAll('.order-card');
        if (filterTabs.length && orderCards.length) {
            filterTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const status = tab.dataset.status;
                    // update active class
                    filterTabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    // filter cards
                    orderCards.forEach(card => {
                        if (status === 'all' || card.dataset.status === status) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        }
    </script>
</body>
</html>