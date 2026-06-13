<?php
/**
 * ================================================
 * CART PAGE - SUPERMARKET BUAH E-COMMERCE
 * ================================================
 * Layout baru: Kartu item dengan aksen, sidebar ringkasan modern
 * Semua fungsi PHP dan JS tetap dipertahankan
 */

session_start();
require_once '../auth/check_session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkLogin();

$user       = getLoggedInUser();
$cart       = $_SESSION['cart'] ?? [];
$cart_count = count($cart);

$total = 0;
foreach ($cart as $item) {
    $total += $item['harga_kg'] * $item['qty'];
}

function getCartImage($filename) {
    if (empty($filename)) return 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=100';
    if (filter_var($filename, FILTER_VALIDATE_URL)) return $filename;
    return '../assets/images/products/' . basename($filename);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Supermarket Buah</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* ----- NAVBAR (sama seperti sebelumnya, minimalis) ----- */
        .navbar {
            background: rgba(245, 251, 230, 0.96);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #DDE8CE;
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 0.75rem 0;
        }
        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 68px;
        }
        .nav-logo {
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
            text-decoration: none;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #7BA05B;
            letter-spacing: -0.3px;
        }
        .nav-logo span:first-child { font-weight: 800; color: #5C7E42; }
        .nav-actions { display: flex; gap: 0.75rem; align-items: center; }
        .btn-nav {
            padding: 0.5rem 1.25rem;
            border-radius: 40px;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            transition: 0.2s;
            border: 1.5px solid #7BA05B;
            background: transparent;
            color: #7BA05B;
        }
        .btn-nav:hover { background: #E8F3DA; transform: translateY(-1px); }
        .btn-nav.solid {
            background: #7BA05B;
            color: white;
            border: none;
        }
        .btn-nav.solid:hover { background: #5C7E42; }

        /* ----- HEADER HALAMAN ----- */
        .page-header {
            padding: 2rem 0 1rem;
            border-bottom: 1px solid #DDE8CE;
            margin-bottom: 2rem;
        }
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #2C3A1F;
        }
        .cart-summary-text {
            color: #5A6E4A;
            margin-top: 0.25rem;
            font-size: 0.85rem;
        }

        /* ----- LAYOUT 2 KOLOM ----- */
        .cart-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2rem;
            padding-bottom: 3rem;
        }

        /* ----- DAFTAR ITEM (kartu vertikal) ----- */
        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .cart-item-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #DDE8CE;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: 0.2s;
            position: relative;
            border-left: 5px solid #7BA05B;
        }
        .cart-item-card.removing {
            opacity: 0;
            transform: translateX(30px);
            transition: 0.3s;
        }
        .item-img {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: cover;
            background: #EFF8E0;
            flex-shrink: 0;
        }
        .item-details {
            flex: 1;
        }
        .item-name {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }
        .item-price {
            font-size: 0.8rem;
            color: #5A6E4A;
        }
        .item-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }
        .qty-control {
            display: flex;
            align-items: center;
            border: 1.5px solid #DDE8CE;
            border-radius: 40px;
            overflow: hidden;
        }
        .qty-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #f5f5f5;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }
        .qty-btn:hover:not(:disabled) { background: #E8F3DA; color: #7BA05B; }
        .qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .qty-display {
            width: 40px;
            text-align: center;
            font-weight: 600;
            background: white;
            border-left: 1px solid #DDE8CE;
            border-right: 1px solid #DDE8CE;
            line-height: 32px;
        }
        .item-subtotal {
            font-weight: 700;
            font-size: 1rem;
            color: #7BA05B;
            min-width: 100px;
            text-align: right;
        }
        .btn-delete {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #8A9A7A;
            padding: 0.25rem;
            border-radius: 8px;
            transition: 0.2s;
        }
        .btn-delete:hover {
            color: #DC2626;
            background: #FEE2E2;
        }

        /* ----- SIDEBAR RINGKASAN ----- */
        .order-summary {
            background: white;
            border-radius: 24px;
            border: 1px solid #DDE8CE;
            padding: 1.5rem;
            position: sticky;
            top: 90px;
            height: fit-content;
        }
        .summary-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #DDE8CE;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #DDE8CE;
        }
        .summary-row .label { color: #5A6E4A; }
        .summary-row .value { font-weight: 600; }
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 2px solid #DDE8CE;
            color: #7BA05B;
        }
        .btn-checkout {
            width: 100%;
            padding: 0.9rem;
            background: #7BA05B;
            color: white;
            border: none;
            border-radius: 40px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 1.5rem;
        }
        .btn-checkout:hover {
            background: #5C7E42;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(92,126,66,0.2);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #8A9A7A;
            text-decoration: none;
        }
        .back-link:hover { color: #7BA05B; }

        /* ----- EMPTY STATE ----- */
        .empty-cart {
            background: white;
            border-radius: 24px;
            border: 1px solid #DDE8CE;
            text-align: center;
            padding: 3rem;
        }
        .empty-icon { font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; }
        .empty-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; }
        .empty-text { color: #5A6E4A; margin: 0.5rem 0 1.5rem; }
        .btn-primary {
            display: inline-block;
            padding: 0.7rem 1.8rem;
            background: #7BA05B;
            color: white;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
        }

        /* ----- MODAL, TOAST, FOOTER (sama seperti sebelumnya, tidak diubah) ----- */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 500; align-items: center; justify-content: center; padding: 1rem; }
        .modal-overlay.show { display: flex; }
        .modal-box { background: white; border-radius: 24px; width: 100%; max-width: 500px; box-shadow: 0 12px 24px -8px rgba(0,0,0,0.08); animation: popIn 0.2s; max-height: 90vh; overflow-y: auto; }
        @keyframes popIn { from { opacity: 0; transform: scale(0.96); } to { opacity: 1; transform: scale(1); } }
        .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #DDE8CE; display: flex; justify-content: space-between; align-items: center; background: white; position: sticky; top: 0; }
        .modal-title { font-family: 'Playfair Display', serif; font-size: 1.25rem; font-weight: 700; }
        .modal-close { background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #8A9A7A; }
        .modal-close:hover { background: #f5f5f5; border-radius: 8px; }
        .modal-body { padding: 1.5rem; }
        .modal-summary { background: #F5FBE6; border-radius: 16px; padding: 1rem; margin-bottom: 1.5rem; }
        .modal-summary-title { font-size: 0.7rem; font-weight: 600; color: #8A9A7A; text-transform: uppercase; margin-bottom: 0.75rem; }
        .modal-summary-items { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 0.75rem; }
        .modal-summary-item { display: flex; justify-content: space-between; font-size: 0.85rem; }
        .modal-summary-total { display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid #DDE8CE; font-weight: 700; }
        .modal-summary-total .total-val { color: #7BA05B; }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; }
        .form-label .req { color: #DC2626; }
        .form-input { width: 100%; padding: 0.7rem 1rem; border: 1.5px solid #DDE8CE; border-radius: 12px; font-size: 0.9rem; transition: 0.2s; }
        .form-input:focus { outline: none; border-color: #7BA05B; box-shadow: 0 0 0 3px rgba(123,160,91,0.1); }
        .form-input.is-error { border-color: #DC2626; }
        .field-error { font-size: 0.7rem; color: #DC2626; margin-top: 0.3rem; display: none; }
        .field-error.show { display: block; }
        textarea.form-input { resize: vertical; min-height: 80px; }
        .modal-footer { padding: 1rem 1.5rem 1.5rem; display: flex; gap: 0.75rem; border-top: 1px solid #DDE8CE; }
        .btn-cancel { flex: 1; padding: 0.7rem; border: 1.5px solid #DDE8CE; background: white; border-radius: 40px; font-weight: 500; cursor: pointer; }
        .btn-cancel:hover { border-color: #8A9A7A; }
        .btn-submit-order { flex: 2; padding: 0.7rem; background: #7BA05B; color: white; border: none; border-radius: 40px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.5rem; cursor: pointer; }
        .btn-submit-order:disabled { opacity: 0.6; }
        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.6s linear infinite; display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .success-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
        .success-overlay.show { display: flex; }
        .success-box { background: white; border-radius: 24px; padding: 2rem; text-align: center; max-width: 360px; animation: popIn 0.2s; }
        .success-icon { font-size: 3rem; margin-bottom: 1rem; }
        .success-title { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #16A34A; }
        .success-text { color: #5A6E4A; font-size: 0.85rem; }
        .success-order-id { font-size: 0.8rem; color: #8A9A7A; margin-bottom: 1.5rem; }
        .success-actions { display: flex; flex-direction: column; gap: 0.75rem; }
        .btn-success { display: block; padding: 0.7rem; background: #7BA05B; color: white; border-radius: 40px; text-decoration: none; font-weight: 600; }
        .btn-success-outline { display: block; padding: 0.7rem; background: white; color: #7BA05B; border: 1.5px solid #7BA05B; border-radius: 40px; text-decoration: none; font-weight: 600; }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: white; border-radius: 12px; padding: 0.75rem 1.25rem; box-shadow: 0 12px 24px -8px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 0.75rem; z-index: 9999; border-left: 4px solid #16A34A; font-size: 0.85rem; animation: slideUp 0.2s; }
        .toast.error { border-left-color: #DC2626; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .footer { background: #5C7E42; color: white; padding: 1.5rem 0; margin-top: 3rem; text-align: center; font-size: 0.8rem; opacity: 0.9; }
        @media (max-width: 800px) {
            .cart-grid { grid-template-columns: 1fr; }
            .order-summary { position: static; }
            .cart-item-card { flex-wrap: wrap; }
            .item-controls { margin-left: auto; }
        }
        @media (max-width: 500px) {
            .cart-item-card { flex-direction: column; align-items: flex-start; border-left-width: 3px; }
            .item-controls { width: 100%; justify-content: space-between; margin-left: 0; }
            .item-subtotal { text-align: left; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="../index.php" class="nav-logo"><span>Supermarket</span><span>Buah</span></a>
        <div class="nav-actions">
            <a href="catalog.php" class="btn-nav">← Lanjut Belanja</a>
            <a href="../auth/logout.php" class="btn-nav solid">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1 class="page-title">Keranjang Belanja</h1>
        <?php if ($cart_count > 0): ?>
        <div class="cart-summary-text">Anda memiliki <strong><?php echo $cart_count; ?></strong> item dalam keranjang</div>
        <?php endif; ?>
    </div>

    <?php if ($cart_count > 0): ?>
    <div class="cart-grid">
        <!-- DAFTAR ITEM (kartu baru) -->
        <div class="cart-items" id="cartItemsContainer">
            <?php foreach ($cart as $product_id => $item):
                $subtotal = $item['harga_kg'] * $item['qty'];
            ?>
            <div class="cart-item-card" id="item-<?php echo $product_id; ?>" data-product-id="<?php echo $product_id; ?>">
                <img src="<?php echo htmlspecialchars(getCartImage($item['gambar'])); ?>"
                     alt="<?php echo htmlspecialchars($item['nama_buah']); ?>"
                     class="item-img"
                     onerror="this.src='https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=100'">
                <div class="item-details">
                    <div class="item-name"><?php echo htmlspecialchars($item['nama_buah']); ?></div>
                    <div class="item-price">Rp <?php echo number_format($item['harga_kg'], 0, ',', '.'); ?>/kg</div>
                </div>
                <div class="item-controls">
                    <div class="qty-control">
                        <button class="qty-btn qty-minus" data-product-id="<?php echo $product_id; ?>" <?php echo $item['qty'] <= 1 ? 'disabled' : ''; ?>>−</button>
                        <span class="qty-display" id="qty-<?php echo $product_id; ?>"><?php echo $item['qty']; ?></span>
                        <button class="qty-btn qty-plus" data-product-id="<?php echo $product_id; ?>" data-max="<?php echo (int)$item['stok_max']; ?>" <?php echo $item['qty'] >= $item['stok_max'] ? 'disabled' : ''; ?>>+</button>
                    </div>
                    <div class="item-subtotal" id="subtotal-<?php echo $product_id; ?>">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></div>
                    <button class="btn-delete" data-product-id="<?php echo $product_id; ?>" title="Hapus">✕</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- SIDEBAR RINGKASAN -->
        <aside class="order-summary">
            <div class="summary-title">Ringkasan Pesanan</div>
            <div id="summaryItems">
                <?php foreach ($cart as $product_id => $item): ?>
                <div class="summary-row" id="summary-row-<?php echo $product_id; ?>">
                    <span class="label"><?php echo htmlspecialchars($item['nama_buah']); ?> × <?php echo $item['qty']; ?> kg</span>
                    <span class="value" id="summary-sub-<?php echo $product_id; ?>">Rp <?php echo number_format($item['harga_kg'] * $item['qty'], 0, ',', '.'); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <span id="grandTotal">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
            </div>
            <button class="btn-checkout" id="btnCheckout">Checkout Sekarang →</button>
            <a href="catalog.php" class="back-link">← Lanjut Belanja</a>
        </aside>
    </div>
    <?php else: ?>
    <div class="empty-cart">
        <div class="empty-icon">🛒</div>
        <h2 class="empty-title">Keranjang Kosong</h2>
        <p class="empty-text">Belum ada produk di keranjang Anda.<br>Yuk mulai belanja di Supermarket Buah!</p>
        <a href="catalog.php" class="btn-primary">Mulai Belanja →</a>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <div class="container">
        <p>© <?php echo date('Y'); ?> Supermarket Buah - Fresh & Quality</p>
    </div>
</footer>

<!-- MODAL CHECKOUT (sama persis dengan sebelumnya, tidak diubah) -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Detail Pengiriman</h2>
            <button class="modal-close" id="modalClose">✕</button>
        </div>
        <div class="modal-body">
            <div class="modal-summary">
                <div class="modal-summary-title">Ringkasan Pesanan</div>
                <div class="modal-summary-items">
                    <?php foreach ($cart as $product_id => $item): ?>
                    <div class="modal-summary-item">
                        <span><?php echo htmlspecialchars($item['nama_buah']); ?> × <?php echo $item['qty']; ?> kg</span>
                        <span class="sub">Rp <?php echo number_format($item['harga_kg'] * $item['qty'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-summary-total">
                    <span>Total</span>
                    <span class="total-val">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="inputNama">Nama Penerima <span class="req">*</span></label>
                <input type="text" class="form-input" id="inputNama" placeholder="Nama lengkap penerima" value="<?php echo htmlspecialchars($user['nama_lengkap'] ?? ''); ?>">
                <div class="field-error" id="errNama">Nama penerima harus diisi</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="inputTelepon">Nomor Telepon <span class="req">*</span></label>
                <input type="tel" class="form-input" id="inputTelepon" placeholder="Contoh: 08123456789" value="<?php echo htmlspecialchars($user['no_telepon'] ?? ''); ?>">
                <div class="field-error" id="errTelepon">Nomor telepon harus diisi</div>
            </div>
            <div class="form-group">
                <label class="form-label" for="inputAlamat">Alamat Pengiriman <span class="req">*</span></label>
                <textarea class="form-input" id="inputAlamat" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota..."><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                <div class="field-error" id="errAlamat">Alamat pengiriman harus diisi</div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" id="btnCancel">Batal</button>
            <button class="btn-submit-order" id="btnSubmitOrder">
                <span id="submitText">Konfirmasi Pesanan</span>
                <div class="spinner" id="submitSpinner"></div>
            </button>
        </div>
    </div>
</div>

<!-- SUCCESS OVERLAY -->
<div class="success-overlay" id="successOverlay">
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h2 class="success-title">Pesanan Berhasil!</h2>
        <p class="success-text">Pesanan Anda sudah masuk dan sedang diproses oleh admin.</p>
        <p class="success-order-id" id="successOrderId"></p>
        <div class="success-actions">
            <a href="#" id="btnLihatPesanan" class="btn-success">Lihat Pesanan Saya</a>
            <a href="catalog.php" class="btn-success-outline">Belanja Lagi</a>
        </div>
    </div>
</div>

<script>
// ========== SEMUA FUNGSI JAVASCRIPT SAMA PERSIS SEPERTI SEBELUMNYA ==========
function formatRupiah(n) { return 'Rp ' + parseInt(n).toLocaleString('id-ID'); }
function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = 'toast ' + (type === 'error' ? 'error' : '');
    t.innerHTML = `<div class="toast-icon">${type==='success'?'✓':'✗'}</div><div class="toast-msg">${msg}</div>`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
function updateItemCountLabel() {
    const n = document.querySelectorAll('.cart-item-card').length;
    const header = document.querySelector('.cart-summary-text');
    if (header) header.innerHTML = `Anda memiliki <strong>${n}</strong> item dalam keranjang`;
}
// Modal
const modal = document.getElementById('checkoutModal');
document.getElementById('btnCheckout')?.addEventListener('click', () => { clearErrors(); modal.classList.add('show'); document.body.style.overflow = 'hidden'; });
function closeModal() { modal.classList.remove('show'); document.body.style.overflow = ''; }
document.getElementById('modalClose')?.addEventListener('click', closeModal);
document.getElementById('btnCancel')?.addEventListener('click', closeModal);
modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
// Validation
function clearErrors() { ['Nama','Telepon','Alamat'].forEach(f => { const inp = document.getElementById('input'+f); if(inp) inp.classList.remove('is-error'); const err = document.getElementById('err'+f); if(err) err.classList.remove('show'); }); }
function validateForm() {
    clearErrors(); let ok = true;
    const nama = document.getElementById('inputNama'), telp = document.getElementById('inputTelepon'), alamat = document.getElementById('inputAlamat');
    if (!nama.value.trim()) { nama.classList.add('is-error'); document.getElementById('errNama').classList.add('show'); ok = false; }
    if (!telp.value.trim()) { telp.classList.add('is-error'); document.getElementById('errTelepon').textContent = 'Nomor telepon harus diisi'; document.getElementById('errTelepon').classList.add('show'); ok = false; }
    else if (!/^[0-9+\-\s]{8,15}$/.test(telp.value.trim())) { telp.classList.add('is-error'); document.getElementById('errTelepon').textContent = 'Format nomor tidak valid (8-15 digit)'; document.getElementById('errTelepon').classList.add('show'); ok = false; }
    if (!alamat.value.trim()) { alamat.classList.add('is-error'); document.getElementById('errAlamat').classList.add('show'); ok = false; }
    return ok;
}
// Submit order
document.getElementById('btnSubmitOrder')?.addEventListener('click', async function() {
    if (!validateForm()) return;
    const nama = document.getElementById('inputNama').value.trim(), telp = document.getElementById('inputTelepon').value.trim(), alamat = document.getElementById('inputAlamat').value.trim();
    this.disabled = true; document.getElementById('submitText').style.display = 'none'; document.getElementById('submitSpinner').style.display = 'block';
    try {
        const fd = new FormData(); fd.append('nama_pemesan', nama); fd.append('no_telepon', telp); fd.append('alamat_kirim', alamat);
        const res = await fetch('../api/checkout.php', { method: 'POST', body: fd }); const data = await res.json();
        if (data.success) {
            closeModal(); document.getElementById('successOrderId').textContent = 'No. Pesanan: #' + data.data.order_id;
            document.getElementById('btnLihatPesanan').href = 'order_detail.php?id=' + data.data.order_id;
            document.getElementById('successOverlay').classList.add('show');
        } else { showToast(data.message, 'error'); this.disabled = false; document.getElementById('submitText').style.display = 'inline'; document.getElementById('submitSpinner').style.display = 'none'; }
    } catch(err) { showToast('Terjadi kesalahan sistem', 'error'); this.disabled = false; document.getElementById('submitText').style.display = 'inline'; document.getElementById('submitSpinner').style.display = 'none'; }
});
// Update qty
document.addEventListener('click', async function(e) {
    const minus = e.target.classList.contains('qty-minus'), plus = e.target.classList.contains('qty-plus');
    if (!minus && !plus) return;
    const productId = e.target.dataset.productId, qtyEl = document.getElementById('qty-' + productId), current = parseInt(qtyEl.textContent);
    const max = parseInt(document.querySelector(`.qty-plus[data-product-id="${productId}"]`)?.dataset.max) || 9999;
    if (minus && current <= 1) return; if (plus && current >= max) return;
    await updateQty(productId, minus ? current - 1 : current + 1);
});
async function updateQty(productId, newQty) {
    const minusBtn = document.querySelector(`.qty-minus[data-product-id="${productId}"]`), plusBtn = document.querySelector(`.qty-plus[data-product-id="${productId}"]`), qtyEl = document.getElementById('qty-' + productId);
    if (minusBtn) minusBtn.disabled = true; if (plusBtn) plusBtn.disabled = true;
    try {
        const fd = new FormData(); fd.append('product_id', productId); fd.append('quantity', newQty);
        const res = await fetch('../api/update_cart.php', { method: 'POST', body: fd }); const data = await res.json();
        if (data.success) {
            qtyEl.textContent = newQty; document.getElementById('subtotal-' + productId).textContent = formatRupiah(data.data.subtotal);
            document.getElementById('summary-sub-' + productId).textContent = formatRupiah(data.data.subtotal);
            const sumRow = document.getElementById('summary-row-' + productId); if (sumRow) sumRow.querySelector('.label').textContent = sumRow.querySelector('.label').textContent.split(' ×')[0] + ' × ' + newQty + ' kg';
            document.getElementById('grandTotal').textContent = formatRupiah(data.data.cart_total);
        } else { showToast(data.message, 'error'); }
    } catch(err) { showToast('Terjadi kesalahan', 'error'); }
    finally { const cur = parseInt(qtyEl.textContent), max = parseInt(plusBtn?.dataset.max) || 9999; if (minusBtn) minusBtn.disabled = (cur <= 1); if (plusBtn) plusBtn.disabled = (cur >= max); }
}
// Delete item
document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.btn-delete'); if (!btn) return; if (!confirm('Hapus item ini dari keranjang?')) return;
    const productId = btn.dataset.productId;
    try {
        const fd = new FormData(); fd.append('product_id', productId);
        const res = await fetch('../api/remove_cart.php', { method: 'POST', body: fd }); const data = await res.json();
        if (data.success) {
            const itemEl = document.getElementById('item-' + productId); if (itemEl) { itemEl.classList.add('removing'); setTimeout(() => itemEl.remove(), 300); }
            document.getElementById('summary-row-' + productId)?.remove(); document.getElementById('grandTotal').textContent = formatRupiah(data.data.cart_total);
            setTimeout(() => { updateItemCountLabel(); if (data.data.cart_count === 0) location.reload(); }, 350);
            showToast('Item dihapus dari keranjang');
        } else { showToast(data.message, 'error'); }
    } catch(err) { showToast('Terjadi kesalahan', 'error'); }
});
</script>
</body>
</html>