<?php
/**
 * ADMIN ORDERS - SUPERMARKET BUAH
 * Layout: Navbar horizontal + Grid kartu pesanan + Modal detail
 * Perbaikan: filter bar lebih rapi (search box di kanan dengan lebar proporsional)
 */
session_start();
require_once '../auth/check_session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();
$admin = getLoggedInUser();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($new_status, $valid_statuses)) {
        try {
            execute("UPDATE orders SET status = ? WHERE id = ?", [$new_status, $order_id]);
            $message = 'Status pesanan berhasil diupdate!';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Gagal update status: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $order_id = intval($_GET['delete']);
    try {
        execute("DELETE FROM order_items WHERE order_id = ?", [$order_id]);
        execute("DELETE FROM orders WHERE id = ?", [$order_id]);
        $message = 'Pesanan berhasil dihapus!';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Gagal menghapus pesanan: ' . $e->getMessage();
        $message_type = 'error';
    }
}

$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT o.*, u.nama_lengkap, u.username, u.no_telepon as user_phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}
if (!empty($search)) {
    $sql .= " AND (o.nama_pemesan LIKE ? OR o.id LIKE ? OR u.username LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
$sql .= " ORDER BY o.created_at DESC";
$orders = fetchAll($sql, $params);

$status_counts = fetchAll("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$count_by_status = [];
foreach ($status_counts as $sc) {
    $count_by_status[$sc['status']] = $sc['count'];
}

$detail_order_id = isset($_GET['detail']) ? intval($_GET['detail']) : 0;
$order_detail = null;
$order_items = [];
if ($detail_order_id) {
    $order_detail = fetchOne("SELECT o.*, u.nama_lengkap, u.username, u.email, u.no_telepon as user_phone 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              WHERE o.id = ?", [$detail_order_id]);
    if ($order_detail) {
        $order_items = fetchAll("SELECT oi.*, b.gambar, b.nama_buah 
                                FROM order_items oi 
                                JOIN buah b ON oi.buah_id = b.id 
                                WHERE oi.order_id = ?", [$detail_order_id]);
    }
}

function getImagePath($filename) {
    if (empty($filename)) return 'https://via.placeholder.com/60';
    if (filter_var($filename, FILTER_VALIDATE_URL)) return $filename;
    return '../assets/images/products/' . basename($filename);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Supermarket Buah</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #F5FBE6;
            color: #2C3A1F;
            line-height: 1.5;
        }
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
        .logo span { color: #7BA05B; }
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
        }
        .user-greeting {
            font-size: 0.85rem;
            color: #5A6E4A;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .header-section {
            margin-bottom: 2rem;
        }
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #2C3A1F;
        }
        /* FILTER BAR - diperbaiki agar search box tidak terlalu mepet */
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #DDE8CE;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            color: #5A6E4A;
            transition: 0.2s;
        }
        .filter-btn.active {
            background: #7BA05B;
            color: white;
            border-color: #7BA05B;
        }
        .filter-btn:hover:not(.active) {
            border-color: #7BA05B;
            background: #E8F3DA;
        }
        .filter-count {
            background: rgba(0,0,0,0.1);
            padding: 0 0.4rem;
            border-radius: 20px;
            margin-left: 0.3rem;
            font-size: 0.7rem;
        }
        .search-box {
            flex-shrink: 0;
            width: 280px;
        }
        .search-box form {
            display: flex;
            width: 100%;
        }
        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.2rem;
            border: 1px solid #DDE8CE;
            border-radius: 40px;
            font-size: 0.85rem;
            background: white url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="%238A9A7A" stroke-width="2"><circle cx="10" cy="10" r="7"/><line x1="21" y1="21" x2="15" y2="15"/></svg>') no-repeat 0.7rem center;
            background-size: 14px;
        }
        .search-input:focus {
            outline: none;
            border-color: #7BA05B;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }
        .alert-success {
            background: #D1FAE5;
            color: #16A34A;
            border-left: 4px solid #16A34A;
        }
        .alert-error {
            background: #FEE2E2;
            color: #DC2626;
            border-left: 4px solid #DC2626;
        }
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 1.5rem;
        }
        .order-card {
            background: white;
            border-radius: 24px;
            border: 1px solid #DDE8CE;
            overflow: hidden;
            transition: 0.2s;
        }
        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -12px rgba(0,0,0,0.1);
        }
        .card-header {
            padding: 1rem 1.25rem;
            background: #F9FDF2;
            border-bottom: 1px solid #DDE8CE;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .order-id {
            font-weight: 700;
            color: #7BA05B;
            font-size: 0.9rem;
        }
        .order-date {
            font-size: 0.7rem;
            color: #8A9A7A;
        }
        .status-badge {
            padding: 0.25rem 0.7rem;
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
        .card-body {
            padding: 1rem 1.25rem;
        }
        .customer-name {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .customer-contact {
            font-size: 0.7rem;
            color: #8A9A7A;
        }
        .order-total {
            font-weight: 700;
            font-size: 1.1rem;
            color: #7BA05B;
            margin: 0.5rem 0;
        }
        .items-preview {
            font-size: 0.7rem;
            color: #5A6E4A;
            border-top: 1px solid #DDE8CE;
            padding-top: 0.5rem;
            margin-top: 0.5rem;
        }
        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            border-top: 1px solid #DDE8CE;
            padding-top: 0.75rem;
        }
        .btn-detail, .btn-delete {
            flex: 1;
            text-align: center;
            padding: 0.4rem;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-detail {
            background: #E8F3DA;
            color: #7BA05B;
        }
        .btn-detail:hover {
            background: #7BA05B;
            color: white;
        }
        .btn-delete {
            background: #FEE2E2;
            color: #DC2626;
        }
        .btn-delete:hover {
            background: #DC2626;
            color: white;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.show { display: flex; }
        .modal-content {
            background: white;
            max-width: 800px;
            width: 90%;
            border-radius: 28px;
            max-height: 90vh;
            overflow-y: auto;
            animation: popIn 0.2s ease;
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #DDE8CE;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }
        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            font-weight: 700;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #8A9A7A;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .info-card {
            background: #F9FDF2;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #DDE8CE;
            font-size: 0.85rem;
        }
        .info-label { color: #5A6E4A; }
        .info-value { font-weight: 600; }
        .item-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: white;
            border-radius: 16px;
            margin-bottom: 0.5rem;
            border: 1px solid #DDE8CE;
        }
        .item-img {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            object-fit: cover;
        }
        .item-detail { flex: 1; }
        .item-name { font-weight: 600; font-size: 0.85rem; }
        .item-meta { font-size: 0.7rem; color: #8A9A7A; }
        .item-price { font-weight: 700; color: #7BA05B; }
        .status-form {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .status-select {
            padding: 0.5rem;
            border: 1px solid #DDE8CE;
            border-radius: 40px;
            font-size: 0.8rem;
        }
        .btn-update {
            background: #7BA05B;
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 40px;
            cursor: pointer;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 24px;
        }
        @media (max-width: 768px) {
            .topbar { padding: 0.75rem 1rem; }
            .container { padding: 1rem; }
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .search-box {
                width: 100%;
            }
            .orders-grid { grid-template-columns: 1fr; }
            .detail-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="topbar">
    <a href="dashboard.php" class="logo">Supermarket<span>Buah</span></a>
    <div class="nav-menu">
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="products.php" class="nav-link">Produk</a>
        <a href="orders.php" class="nav-link active">Pesanan</a>
        <span class="user-greeting">Halo, <?php echo htmlspecialchars($admin['nama_lengkap']); ?></span>
        <a href="../auth/logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<div class="container">
    <div class="header-section">
        <h1 class="page-title">Kelola Pesanan</h1>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- FILTER BAR - rapi: filter di kiri, search di kanan dengan lebar tetap -->
    <div class="filter-bar">
        <div class="filter-group">
            <a href="orders.php" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">Semua <span class="filter-count"><?php echo array_sum($count_by_status); ?></span></a>
            <a href="orders.php?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">Menunggu <span class="filter-count"><?php echo $count_by_status['pending'] ?? 0; ?></span></a>
            <a href="orders.php?status=processing" class="filter-btn <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">Diproses <span class="filter-count"><?php echo $count_by_status['processing'] ?? 0; ?></span></a>
            <a href="orders.php?status=shipped" class="filter-btn <?php echo $status_filter === 'shipped' ? 'active' : ''; ?>">Dikirim <span class="filter-count"><?php echo $count_by_status['shipped'] ?? 0; ?></span></a>
            <a href="orders.php?status=delivered" class="filter-btn <?php echo $status_filter === 'delivered' ? 'active' : ''; ?>">Selesai <span class="filter-count"><?php echo $count_by_status['delivered'] ?? 0; ?></span></a>
        </div>
        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" class="search-input" placeholder="Cari pesanan..." value="<?php echo htmlspecialchars($search); ?>">
                <?php if ($status_filter !== 'all'): ?>
                <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if (count($orders) > 0): ?>
    <div class="orders-grid">
        <?php foreach ($orders as $order):
            $preview_items = fetchAll("SELECT nama_buah, jumlah_kg FROM order_items WHERE order_id = ? LIMIT 2", [$order['id']]);
            $extra_count = fetchOne("SELECT COUNT(*) as cnt FROM order_items WHERE order_id = ?", [$order['id']])['cnt'] - 2;
        ?>
        <div class="order-card">
            <div class="card-header">
                <span class="order-id">#<?php echo $order['id']; ?></span>
                <span class="order-date"><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span>
            </div>
            <div class="card-body">
                <div class="customer-name"><?php echo htmlspecialchars($order['nama_pemesan']); ?></div>
                <div class="customer-contact"><?php echo htmlspecialchars($order['username']); ?> • <?php echo $order['no_telepon']; ?></div>
                <div class="order-total">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></div>
                <div class="items-preview">
                    <?php foreach ($preview_items as $pi): ?>
                    <div><?php echo htmlspecialchars($pi['nama_buah']); ?> × <?php echo $pi['jumlah_kg']; ?> kg</div>
                    <?php endforeach; ?>
                    <?php if ($extra_count > 0): ?>
                    <div>+ <?php echo $extra_count; ?> item lainnya</div>
                    <?php endif; ?>
                </div>
                <div class="card-actions">
                    <a href="#" class="btn-detail" data-id="<?php echo $order['id']; ?>" onclick="openDetail(<?php echo $order['id']; ?>); return false;">Lihat Detail</a>
                    <a href="?delete=<?php echo $order['id']; ?>" class="btn-delete" onclick="return confirm('Hapus pesanan ini?')">Hapus</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <p>Belum ada pesanan yang masuk.</p>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL DETAIL -->
<div class="modal" id="detailModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Detail Pesanan</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align:center; padding:2rem;">Memuat...</div>
        </div>
    </div>
</div>

<script>
function openDetail(orderId) {
    const modal = document.getElementById('detailModal');
    const modalBody = document.getElementById('modalBody');
    modal.classList.add('show');
    modalBody.innerHTML = '<div style="text-align:center; padding:2rem;">Memuat data...</div>';
    fetch('order_detail_ajax.php?id=' + orderId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderDetailModal(data);
            } else {
                modalBody.innerHTML = '<div style="color:red; padding:2rem;">Gagal memuat detail.</div>';
            }
        })
        .catch(() => {
            modalBody.innerHTML = '<div style="color:red; padding:2rem;">Terjadi kesalahan.</div>';
        });
}

function renderDetailModal(data) {
    const order = data.order;
    const items = data.items;
    const statusOptions = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    const statusLabels = { pending:'Menunggu', processing:'Diproses', shipped:'Dikirim', delivered:'Selesai', cancelled:'Dibatalkan' };
    let html = `
    <div class="detail-grid">
        <div>
            <div class="info-card">
                <div class="info-row"><span class="info-label">Order ID</span><span class="info-value">#${order.id}</span></div>
                <div class="info-row"><span class="info-label">Tanggal</span><span class="info-value">${order.created_at}</span></div>
                <div class="info-row"><span class="info-label">Status</span><span class="info-value"><span class="status-badge status-${order.status}">${order.status}</span></span></div>
            </div>
            <div class="info-card">
                <div class="info-row"><span class="info-label">Nama Penerima</span><span class="info-value">${escapeHtml(order.nama_pemesan)}</span></div>
                <div class="info-row"><span class="info-label">Telepon</span><span class="info-value">${order.no_telepon}</span></div>
                <div class="info-row"><span class="info-label">Alamat</span><span class="info-value">${escapeHtml(order.alamat_kirim).replace(/\n/g,'<br>')}</span></div>
            </div>
        </div>
        <div>
            <div class="info-card">
                <div style="font-weight:600; margin-bottom:0.5rem;">Item Pesanan</div>
                <div class="items-list">
                    ${items.map(item => `
                        <div class="item-row">
                            <img src="${item.gambar_url}" class="item-img" onerror="this.src='https://via.placeholder.com/50'">
                            <div class="item-detail">
                                <div class="item-name">${escapeHtml(item.nama_buah)}</div>
                                <div class="item-meta">${item.jumlah_kg} kg × Rp ${formatNumber(item.harga_kg)}/kg</div>
                            </div>
                            <div class="item-price">Rp ${formatNumber(item.subtotal)}</div>
                        </div>
                    `).join('')}
                </div>
                <div class="info-row" style="margin-top:0.5rem;"><span class="info-label">Total</span><span class="info-value" style="font-size:1.1rem; color:#7BA05B;">Rp ${formatNumber(order.total_harga)}</span></div>
            </div>
            <div class="info-card">
                <form method="POST" action="orders.php">
                    <input type="hidden" name="order_id" value="${order.id}">
                    <div class="status-form">
                        <select name="status" class="status-select">
                            ${statusOptions.map(s => `<option value="${s}" ${order.status===s ? 'selected' : ''}>${statusLabels[s]}</option>`).join('')}
                        </select>
                        <button type="submit" name="update_status" class="btn-update">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    `;
    document.getElementById('modalBody').innerHTML = html;
}

function closeModal() {
    document.getElementById('detailModal').classList.remove('show');
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
function formatNumber(n) {
    return parseInt(n).toLocaleString('id-ID');
}
setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) alert.style.display = 'none';
}, 4000);
</script>
</body>
</html>