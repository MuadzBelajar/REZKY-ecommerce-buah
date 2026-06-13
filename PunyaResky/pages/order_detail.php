<?php
/**
 * ================================================
 * ORDER DETAIL - SUPERMARKET BUAH E-COMMERCE
 * ================================================
 * File: pages/order_detail.php
 * Layout: Sidebar kiri + Konten utama (layout baru)
 * Style: Minimalis modern, tanpa emoji
 */

session_start();
require_once '../auth/check_session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkLogin();

$user       = getLoggedInUser();
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$order_id   = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    header('Location: my_orders.php');
    exit;
}

$order = fetchOne(
    "SELECT * FROM orders WHERE id = ? AND user_id = ?",
    [$order_id, $user['id']]
);

if (!$order) {
    header('Location: my_orders.php');
    exit;
}

$items = fetchAll(
    "SELECT oi.*, b.gambar FROM order_items oi
     LEFT JOIN buah b ON oi.buah_id = b.id
     WHERE oi.order_id = ?",
    [$order_id]
);

function getItemImage($filename) {
    if (empty($filename)) return 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=80';
    if (filter_var($filename, FILTER_VALIDATE_URL)) return $filename;
    return '../assets/images/products/' . basename($filename);
}

function statusInfo($status) {
    return match($status) {
        'pending'    => ['label' => 'Menunggu Konfirmasi', 'color' => '#D97706', 'bg' => '#FEF3C7', 'desc' => 'Pesananmu sudah masuk dan menunggu konfirmasi dari admin.'],
        'processing' => ['label' => 'Sedang Diproses',    'color' => '#2563EB', 'bg' => '#DBEAFE', 'desc' => 'Admin sedang mempersiapkan pesananmu.'],
        'shipped'    => ['label' => 'Dalam Pengiriman',   'color' => '#4F46E5', 'bg' => '#E0E7FF', 'desc' => 'Pesananmu sedang dalam perjalanan ke alamatmu.'],
        'delivered'  => ['label' => 'Pesanan Selesai',    'color' => '#16A34A', 'bg' => '#D1FAE5', 'desc' => 'Pesananmu telah sampai. Terima kasih sudah belanja!'],
        'cancelled'  => ['label' => 'Dibatalkan',         'color' => '#DC2626', 'bg' => '#FEE2E2', 'desc' => 'Pesanan ini telah dibatalkan.'],
        default      => ['label' => ucfirst($status),     'color' => '#666',    'bg' => '#f5f5f5', 'desc' => ''],
    };
}

$timeline_steps = ['pending', 'processing', 'shipped', 'delivered'];
$current_index  = array_search($order['status'], $timeline_steps);
$si = statusInfo($order['status']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $order_id; ?> - Supermarket Buah</title>
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
        .app-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        /* NAVBAR (sederhana, sticky) */
        .navbar {
            background: rgba(245, 251, 230, 0.96);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #DDE8CE;
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 0.75rem 1.5rem;
        }
        .nav-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: #5C7E42;
            text-decoration: none;
        }
        .nav-logo span {
            color: #7BA05B;
        }
        .nav-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        .btn-nav {
            padding: 0.4rem 1rem;
            border-radius: 40px;
            font-weight: 500;
            font-size: 0.85rem;
            text-decoration: none;
            border: 1.5px solid #7BA05B;
            background: transparent;
            color: #7BA05B;
            transition: all 0.2s ease;
        }
        .btn-nav:hover {
            background: #E8F3DA;
        }
        .btn-nav.solid {
            background: #7BA05B;
            color: white;
            border: none;
        }
        .btn-nav.solid:hover {
            background: #5C7E42;
        }
        .cart-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #DC2626;
            color: white;
            font-size: 0.65rem;
            min-width: 18px;
            height: 18px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cart-wrap {
            position: relative;
        }

        /* LAYOUT UTAMA: 2 kolom (sidebar kiri + konten kanan) */
        .order-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        /* SIDEBAR KIRI */
        .order-sidebar {
            position: sticky;
            top: 90px;
            align-self: start;
        }
        .info-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #DDE8CE;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .order-id-large {
            text-align: center;
            margin-bottom: 1rem;
        }
        .order-id-large .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8A9A7A;
        }
        .order-id-large .number {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #7BA05B;
        }
        .order-date {
            text-align: center;
            font-size: 0.8rem;
            color: #8A9A7A;
            border-top: 1px solid #DDE8CE;
            padding-top: 0.75rem;
            margin-top: 0.5rem;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #DDE8CE;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1rem;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 2px solid #DDE8CE;
            color: #7BA05B;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .btn-action {
            display: block;
            text-align: center;
            padding: 0.7rem;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-action.primary {
            background: #7BA05B;
            color: white;
        }
        .btn-action.primary:hover {
            background: #5C7E42;
        }
        .btn-action.outline {
            border: 1.5px solid #7BA05B;
            color: #7BA05B;
            background: white;
        }
        .btn-action.outline:hover {
            background: #E8F3DA;
        }

        /* KONTEN KANAN */
        .order-content {
            background: white;
            border-radius: 24px;
            border: 1px solid #DDE8CE;
            overflow: hidden;
        }
        .status-header {
            padding: 1.5rem;
            background: #FEFEF7;
            border-bottom: 1px solid #DDE8CE;
        }
        .status-badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 40px;
            font-size: 0.8rem;
            font-weight: 600;
            background: <?php echo $si['bg']; ?>;
            color: <?php echo $si['color']; ?>;
            margin-bottom: 0.75rem;
        }
        .status-desc {
            font-size: 0.9rem;
            color: #5A6E4A;
        }
        .timeline-container {
            padding: 1.5rem;
            border-bottom: 1px solid #DDE8CE;
        }
        .timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
        }
        .timeline-step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .timeline-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 14px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #DDE8CE;
            z-index: 0;
        }
        .timeline-step.done:not(:last-child)::after {
            background: #7BA05B;
        }
        .step-dot {
            width: 28px;
            height: 28px;
            background: white;
            border: 2px solid #DDE8CE;
            border-radius: 50%;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .timeline-step.done .step-dot {
            background: #7BA05B;
            border-color: #7BA05B;
            color: white;
        }
        .timeline-step.current .step-dot {
            border-color: #7BA05B;
            box-shadow: 0 0 0 3px rgba(123,160,91,0.2);
        }
        .step-label {
            font-size: 0.7rem;
            margin-top: 0.5rem;
            color: #8A9A7A;
        }
        .timeline-step.done .step-label,
        .timeline-step.current .step-label {
            color: #7BA05B;
            font-weight: 600;
        }
        .items-section {
            padding: 1.5rem;
            border-bottom: 1px solid #DDE8CE;
        }
        .items-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .item-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #DDE8CE;
        }
        .item-img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            background: #EFF8E0;
        }
        .item-detail {
            flex: 1;
        }
        .item-name {
            font-weight: 600;
        }
        .item-meta {
            font-size: 0.75rem;
            color: #8A9A7A;
        }
        .item-price {
            font-weight: 700;
            color: #7BA05B;
        }
        .shipping-section {
            padding: 1.5rem;
        }
        .shipping-title {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .shipping-info p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .shipping-info strong {
            color: #5A6E4A;
            display: inline-block;
            width: 110px;
        }

        @media (max-width: 800px) {
            .order-layout {
                grid-template-columns: 1fr;
            }
            .order-sidebar {
                position: static;
            }
            .app-container {
                padding: 1rem;
            }
        }
        @media (max-width: 500px) {
            .timeline {
                flex-direction: column;
                gap: 1rem;
            }
            .timeline-step:not(:last-child)::after {
                display: none;
            }
            .step-dot {
                margin: 0;
            }
            .timeline-step {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .step-label {
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-inner">
            <a href="../index.php" class="nav-logo">Supermarket<span>Buah</span></a>
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

    <div class="app-container">
        <div class="order-layout">
            <!-- SIDEBAR KIRI -->
            <aside class="order-sidebar">
                <div class="info-card">
                    <div class="order-id-large">
                        <div class="label">Nomor Pesanan</div>
                        <div class="number">#<?php echo $order['id']; ?></div>
                    </div>
                    <div class="order-date">
                        <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?> WIB
                    </div>
                </div>

                <div class="info-card">
                    <div style="font-weight: 600; margin-bottom: 1rem;">Ringkasan Belanja</div>
                    <?php foreach ($items as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['nama_buah']); ?> × <?php echo $item['jumlah_kg']; ?> kg</span>
                        <span>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="info-card">
                    <div class="action-buttons">
                        <a href="catalog.php" class="btn-action primary">Belanja Lagi</a>
                        <a href="my_orders.php" class="btn-action outline">Semua Pesanan</a>
                    </div>
                </div>

                <?php if ($order['status'] === 'cancelled'): ?>
                <div class="info-card" style="background: #FEE2E2; border-color: #FECACA;">
                    <p style="font-size: 0.85rem; color: #DC2626;">Pesanan ini telah dibatalkan dan tidak dapat diproses kembali.</p>
                </div>
                <?php endif; ?>
            </aside>

            <!-- KONTEN UTAMA -->
            <main class="order-content">
                <div class="status-header">
                    <div class="status-badge"><?php echo $si['label']; ?></div>
                    <div class="status-desc"><?php echo $si['desc']; ?></div>
                </div>

                <?php if ($order['status'] !== 'cancelled'): ?>
                <div class="timeline-container">
                    <div class="timeline">
                        <?php
                        $step_labels = ['Menunggu', 'Diproses', 'Dikirim', 'Selesai'];
                        foreach ($timeline_steps as $i => $step):
                            $is_done    = ($current_index !== false && $i <= $current_index);
                            $is_current = ($i === $current_index);
                            $cls = $is_done ? 'done' : '';
                            $cls .= $is_current ? ' current' : '';
                        ?>
                        <div class="timeline-step <?php echo trim($cls); ?>">
                            <div class="step-dot"><?php echo $is_done ? '✓' : ($i + 1); ?></div>
                            <div class="step-label"><?php echo $step_labels[$i]; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="items-section">
                    <div class="items-title">Item Pesanan</div>
                    <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <img src="<?php echo htmlspecialchars(getItemImage($item['gambar'])); ?>" class="item-img" alt="<?php echo htmlspecialchars($item['nama_buah']); ?>">
                        <div class="item-detail">
                            <div class="item-name"><?php echo htmlspecialchars($item['nama_buah']); ?></div>
                            <div class="item-meta"><?php echo $item['jumlah_kg']; ?> kg × Rp <?php echo number_format($item['harga_kg'], 0, ',', '.'); ?>/kg</div>
                        </div>
                        <div class="item-price">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="shipping-section">
                    <div class="shipping-title">Informasi Pengiriman</div>
                    <div class="shipping-info">
                        <p><strong>Nama Penerima</strong> <?php echo htmlspecialchars($order['nama_pemesan']); ?></p>
                        <p><strong>Telepon</strong> <?php echo htmlspecialchars($order['no_telepon']); ?></p>
                        <p><strong>Alamat</strong> <?php echo nl2br(htmlspecialchars($order['alamat_kirim'])); ?></p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>