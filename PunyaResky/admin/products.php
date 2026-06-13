<?php
/**
 * ================================================
 * ADMIN PRODUCTS - SUPERMARKET BUAH E-COMMERCE
 * ================================================
 * File: admin/products.php
 * Layout: Navbar horizontal + Grid kartu produk + Modal form
 * Fungsi PHP tetap, tampilan baru total
 * ================================================
 */

session_start();
require_once '../auth/check_session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

requireAdmin();

$admin = getLoggedInUser();

// ================================================
// HANDLE AJAX / POST ACTIONS
// ================================================
$message = '';
$message_type = '';

// DELETE Product (via GET)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = intval($_GET['delete']);
    try {
        $product = fetchOne("SELECT gambar FROM buah WHERE id = ?", [$product_id]);
        execute("DELETE FROM buah WHERE id = ?", [$product_id]);
        if ($product && !empty($product['gambar']) && !filter_var($product['gambar'], FILTER_VALIDATE_URL)) {
            deleteImage(basename($product['gambar']), '../assets/images/products/');
        }
        $message = 'Produk berhasil dihapus!';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Gagal menghapus produk: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// ADD/EDIT Product (via POST, bisa dari modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $nama_buah = trim($_POST['nama_buah'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga_kg = floatval($_POST['harga_kg'] ?? 0);
    $stok_kg = floatval($_POST['stok_kg'] ?? 0);
    $asal = trim($_POST['asal'] ?? '');
    $kategori = $_POST['kategori'] ?? 'lokal';
    $status = $_POST['status'] ?? 'active';
    $keep_old_image = $_POST['keep_old_image'] ?? '';
    
    $errors = [];
    if (empty($nama_buah)) $errors[] = 'Nama buah harus diisi';
    if ($harga_kg <= 0) $errors[] = 'Harga harus lebih dari 0';
    if ($stok_kg < 0) $errors[] = 'Stok tidak boleh negatif';
    
    $gambar = $keep_old_image ? basename($keep_old_image) : '';
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadImage($_FILES['gambar'], '../assets/images/products/');
        if ($upload_result['success']) {
            if ($product_id && !empty($keep_old_image) && !filter_var($keep_old_image, FILTER_VALIDATE_URL)) {
                deleteImage(basename($keep_old_image), '../assets/images/products/');
            }
            $gambar = $upload_result['filename'];
        } else {
            $errors[] = $upload_result['error'];
        }
    }
    
    if (empty($errors)) {
        try {
            $slug = generateSlug($nama_buah);
            if ($product_id) {
                execute("UPDATE buah SET nama_buah=?, slug=?, deskripsi=?, harga_kg=?, stok_kg=?, gambar=?, asal=?, kategori=?, status=? WHERE id=?",
                    [$nama_buah, $slug, $deskripsi, $harga_kg, $stok_kg, $gambar, $asal, $kategori, $status, $product_id]);
                $message = 'Produk berhasil diupdate!';
            } else {
                execute("INSERT INTO buah (nama_buah, slug, deskripsi, harga_kg, stok_kg, gambar, asal, kategori, status) VALUES (?,?,?,?,?,?,?,?,?)",
                    [$nama_buah, $slug, $deskripsi, $harga_kg, $stok_kg, $gambar, $asal, $kategori, $status]);
                $message = 'Produk berhasil ditambahkan!';
            }
            $message_type = 'success';
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}

// Get product for edit (via AJAX or direct)
$edit_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_product = fetchOne("SELECT * FROM buah WHERE id = ?", [intval($_GET['edit'])]);
}

// Fetch all products
$products = fetchAll("SELECT * FROM buah ORDER BY created_at DESC");

function getImagePath($filename) {
    if (empty($filename)) return 'https://via.placeholder.com/150?text=No+Image';
    if (filter_var($filename, FILTER_VALIDATE_URL)) return $filename;
    return '../assets/images/products/' . basename($filename);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Supermarket Buah</title>
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
        /* HEADER SECTION */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #2C3A1F;
        }
        .btn-primary {
            background: #7BA05B;
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.2s;
        }
        .btn-primary:hover {
            background: #5C7E42;
            transform: translateY(-2px);
        }
        /* ALERT */
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
        /* PRODUCT GRID */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .product-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #DDE8CE;
            overflow: hidden;
            transition: 0.2s;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -12px rgba(0,0,0,0.1);
        }
        .product-img {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            background: #EFF8E0;
        }
        .product-info {
            padding: 1rem;
        }
        .product-name {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        .product-price {
            font-weight: 700;
            color: #7BA05B;
            font-size: 1.1rem;
        }
        .product-stock {
            font-size: 0.75rem;
            color: #8A9A7A;
            margin: 0.25rem 0;
        }
        .product-stock.low { color: #DC2626; }
        .product-category {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            background: #E8F3DA;
            border-radius: 20px;
            font-size: 0.7rem;
            margin-top: 0.5rem;
        }
        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            border-top: 1px solid #DDE8CE;
            padding-top: 0.75rem;
        }
        .btn-edit, .btn-delete {
            flex: 1;
            padding: 0.4rem;
            border-radius: 40px;
            text-align: center;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-edit {
            background: #DBEAFE;
            color: #2563EB;
        }
        .btn-edit:hover {
            background: #2563EB;
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
        /* MODAL FORM */
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
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
            max-width: 700px;
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
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #DDE8CE;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
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
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group.full {
            grid-column: span 2;
        }
        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            display: block;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: 1.5px solid #DDE8CE;
            border-radius: 16px;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #7BA05B;
        }
        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }
        .image-preview {
            margin-top: 0.5rem;
            position: relative;
            display: inline-block;
        }
        .image-preview img {
            max-width: 120px;
            border-radius: 12px;
        }
        .btn-submit {
            background: #7BA05B;
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
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
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full { grid-column: span 1; }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
        }
    </style>
</head>
<body>

<div class="topbar">
    <a href="dashboard.php" class="logo">Supermarket<span>Buah</span></a>
    <div class="nav-menu">
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="products.php" class="nav-link active">Produk</a>
        <a href="orders.php" class="nav-link">Pesanan</a>
        <span class="user-greeting">Halo, <?php echo htmlspecialchars($admin['nama_lengkap']); ?></span>
        <a href="../auth/logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<div class="container">
    <div class="header-section">
        <h1 class="page-title">Kelola Produk</h1>
        <button class="btn-primary" id="openModalBtn">+ Tambah Produk</button>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (count($products) > 0): ?>
    <div class="products-grid">
        <?php foreach ($products as $product): 
            $stock_class = $product['stok_kg'] <= 10 ? 'low' : '';
        ?>
        <div class="product-card">
            <img src="<?php echo htmlspecialchars(getImagePath($product['gambar'])); ?>" 
                 alt="<?php echo htmlspecialchars($product['nama_buah']); ?>"
                 class="product-img"
                 onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
            <div class="product-info">
                <div class="product-name"><?php echo htmlspecialchars($product['nama_buah']); ?></div>
                <div class="product-price">Rp <?php echo number_format($product['harga_kg'],0,',','.'); ?>/kg</div>
                <div class="product-stock <?php echo $stock_class; ?>">Stok: <?php echo $product['stok_kg']; ?> kg</div>
                <span class="product-category"><?php echo ucfirst($product['kategori']); ?></span>
                <div class="card-actions">
                    <a href="?edit=<?php echo $product['id']; ?>" class="btn-edit" id="editBtn-<?php echo $product['id']; ?>">Edit</a>
                    <a href="?delete=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirm('Yakin hapus produk ini?')">Hapus</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <p>Belum ada produk. Klik "Tambah Produk" untuk memulai.</p>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL FORM (Add/Edit) -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Tambah Produk</h2>
            <button class="modal-close" id="closeModalBtn">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="productForm">
            <div class="modal-body">
                <input type="hidden" name="product_id" id="product_id">
                <input type="hidden" name="keep_old_image" id="keep_old_image">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Buah *</label>
                        <input type="text" name="nama_buah" id="nama_buah" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Harga (Rp/kg) *</label>
                        <input type="number" name="harga_kg" id="harga_kg" class="form-input" step="1000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stok (kg) *</label>
                        <input type="number" name="stok_kg" id="stok_kg" class="form-input" step="0.1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asal Daerah</label>
                        <input type="text" name="asal" id="asal" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" id="kategori" class="form-select">
                            <option value="lokal">Lokal</option>
                            <option value="impor">Impor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Gambar</label>
                        <input type="file" name="gambar" id="gambar" accept="image/*" class="form-input">
                        <div id="currentImagePreview" style="margin-top: 0.5rem;"></div>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-textarea" rows="3"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-submit" id="submitBtn">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal elements
    const modal = document.getElementById('productModal');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('productForm');
    const productIdField = document.getElementById('product_id');
    const keepOldImageField = document.getElementById('keep_old_image');
    const namaField = document.getElementById('nama_buah');
    const hargaField = document.getElementById('harga_kg');
    const stokField = document.getElementById('stok_kg');
    const asalField = document.getElementById('asal');
    const kategoriField = document.getElementById('kategori');
    const statusField = document.getElementById('status');
    const deskripsiField = document.getElementById('deskripsi');
    const gambarInput = document.getElementById('gambar');
    const currentImagePreview = document.getElementById('currentImagePreview');

    // Open modal for Add
    if (openBtn) {
        openBtn.addEventListener('click', () => {
            resetForm();
            modalTitle.innerText = 'Tambah Produk';
            productIdField.value = '';
            keepOldImageField.value = '';
            currentImagePreview.innerHTML = '';
            modal.classList.add('show');
        });
    }

    // Edit product: fetch via AJAX or load from URL parameter
    <?php if (isset($_GET['edit']) && $edit_product): ?>
    window.addEventListener('DOMContentLoaded', () => {
        modalTitle.innerText = 'Edit Produk';
        productIdField.value = '<?php echo $edit_product['id']; ?>';
        keepOldImageField.value = '<?php echo addslashes($edit_product['gambar']); ?>';
        namaField.value = '<?php echo addslashes($edit_product['nama_buah']); ?>';
        hargaField.value = '<?php echo $edit_product['harga_kg']; ?>';
        stokField.value = '<?php echo $edit_product['stok_kg']; ?>';
        asalField.value = '<?php echo addslashes($edit_product['asal']); ?>';
        kategoriField.value = '<?php echo $edit_product['kategori']; ?>';
        statusField.value = '<?php echo $edit_product['status']; ?>';
        deskripsiField.value = '<?php echo addslashes($edit_product['deskripsi']); ?>';
        let imgPath = '<?php echo getImagePath($edit_product['gambar']); ?>';
        if (imgPath && !imgPath.includes('placeholder')) {
            currentImagePreview.innerHTML = `<div style="margin-top:0.5rem"><img src="${imgPath}" style="max-width:100px; border-radius:8px;"><p style="font-size:0.7rem; color:#8A9A7A;">Gambar saat ini</p></div>`;
        }
        modal.classList.add('show');
        // Hapus parameter edit dari URL tanpa reload
        history.replaceState(null, '', 'products.php');
    });
    <?php endif; ?>

    function resetForm() {
        form.reset();
        productIdField.value = '';
        keepOldImageField.value = '';
        currentImagePreview.innerHTML = '';
        gambarInput.value = '';
    }

    closeBtn.addEventListener('click', () => {
        modal.classList.remove('show');
    });
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('show');
    });

    // Optional: preview gambar baru
    gambarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                currentImagePreview.innerHTML = `<div><img src="${ev.target.result}" style="max-width:100px; border-radius:8px;"><p style="font-size:0.7rem;">Preview baru</p></div>`;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>