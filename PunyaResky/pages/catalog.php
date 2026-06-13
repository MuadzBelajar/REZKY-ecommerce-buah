<?php
/**
 * CATALOG PAGE - SUPERMARKET BUAH
 * Layout: Sidebar filter kiri + Grid produk kanan (desain baru)
 * Style: Minimalis modern, semua fungsi tetap
 */
session_start();
require_once '../auth/check_session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

checkLogin();
$user       = getLoggedInUser();
$cart_count = getCartItemCount();

function getProductImage($filename) {
    if (empty($filename)) return 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400';
    if (filter_var($filename, FILTER_VALIDATE_URL)) return $filename;
    return '../assets/images/products/' . basename($filename);
}

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'semua';

$count_sql    = "SELECT COUNT(*) as total FROM buah WHERE status = 'active'";
$count_params = [];
if (!empty($search)) { $count_sql .= " AND nama_buah LIKE ?"; $count_params[] = "%{$search}%"; }
if ($filter === 'lokal') $count_sql .= " AND kategori = 'lokal'";
if ($filter === 'impor') $count_sql .= " AND kategori = 'impor'";
try {
    $count_row      = fetchOne($count_sql, $count_params);
    $total_products = intval($count_row['total'] ?? 0);
} catch (Exception $e) { $total_products = 0; }

$limit  = 12;
$sql    = "SELECT * FROM buah WHERE status = 'active'";
$params = [];
if (!empty($search)) { $sql .= " AND nama_buah LIKE ?"; $params[] = "%{$search}%"; }
if ($filter === 'lokal') $sql .= " AND kategori = 'lokal'";
if ($filter === 'impor') $sql .= " AND kategori = 'impor'";
$sql .= " ORDER BY nama_buah ASC LIMIT $limit";
try { $products = fetchAll($sql, $params); } catch (Exception $e) { $products = []; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Katalog Produk - Supermarket Buah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
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
    --error: #DC2626;
    --success: #16A34A;
    --font-display: 'Playfair Display', serif;
    --font-body: 'Inter', sans-serif;
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.03);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.05);
    --shadow-lg: 0 12px 24px -8px rgba(0,0,0,0.08);
    --t: 200ms ease;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: var(--font-body);
    background: var(--bg);
    color: var(--text);
    line-height: 1.5;
}
.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1.5rem;
}
/* NAVBAR (sama seperti sebelumnya) */
.navbar {
    background: rgba(245,251,230,0.96);
    backdrop-filter: blur(8px);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 0.75rem 0;
}
.nav-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.nav-logo {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    text-decoration: none;
    font-family: var(--font-display);
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    letter-spacing: -0.3px;
}
.nav-logo span:first-child { font-weight: 800; color: var(--primary-dark); }
.nav-desktop {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.nav-greeting {
    font-size: 0.8rem;
    color: var(--text-light);
}
.nav-greeting strong { color: var(--text); }
.nav-btn {
    padding: 0.4rem 1rem;
    border-radius: 40px;
    font-weight: 500;
    font-size: 0.8rem;
    text-decoration: none;
    transition: var(--t);
    border: 1.5px solid var(--primary);
    background: transparent;
    color: var(--primary);
}
.nav-btn:hover { background: var(--primary-light); transform: translateY(-1px); }
.nav-btn.solid {
    background: var(--primary);
    color: white;
    border: none;
}
.nav-btn.solid:hover { background: var(--primary-dark); }
.cart-badge {
    position: absolute;
    top: -7px;
    right: -7px;
    background: var(--error);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cart-link { position: relative; }
.nav-mobile { display: none; align-items: center; gap: 0.5rem; }
.cart-icon-btn {
    position: relative;
    text-decoration: none;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-alt);
    border-radius: 10px;
}
.hamburger-btn {
    width: 40px;
    height: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    background: none;
    border: 1.5px solid var(--primary);
    border-radius: 10px;
    cursor: pointer;
}
.hamburger-btn span {
    width: 18px;
    height: 2px;
    background: var(--primary);
    border-radius: 2px;
    transition: var(--t);
}
.hamburger-btn.open span:nth-child(1) { transform: rotate(45deg) translate(5px,5px); }
.hamburger-btn.open span:nth-child(2) { opacity: 0; }
.hamburger-btn.open span:nth-child(3) { transform: rotate(-45deg) translate(5px,-5px); }
.mobile-menu {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    right: 0;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border);
    padding: 0.5rem;
    min-width: 200px;
    z-index: 999;
}
.mobile-menu.open { display: block; }
.mobile-menu-wrap { position: relative; }
.mobile-menu a {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    color: var(--text);
}
.mobile-menu a:hover { background: var(--primary-light); }
.mobile-menu a.logout { color: var(--error); border-top: 1px solid var(--border); margin-top: 0.25rem; padding-top: 1rem; }
.mobile-menu .m-user {
    padding: 0.75rem 1rem 0.5rem;
    font-size: 0.8rem;
    color: var(--text-light);
    border-bottom: 1px solid var(--border);
}
.mobile-menu .m-user strong { color: var(--text); display: block; }
/* LAYOUT UTAMA: 2 KOLOM (SIDEBAR + KONTEN) */
.catalog-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 2rem;
    margin: 2rem 0;
}
/* SIDEBAR FILTER */
.filter-sidebar {
    background: white;
    border-radius: 20px;
    border: 1px solid var(--border);
    padding: 1.5rem;
    position: sticky;
    top: 90px;
    height: fit-content;
}
.filter-sidebar h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--text);
}
.filter-group-side {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}
.filter-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.filter-option a {
    text-decoration: none;
    color: var(--text-light);
    font-size: 0.85rem;
    padding: 0.25rem 0;
    transition: var(--t);
}
.filter-option a:hover { color: var(--primary); }
.filter-option a.active {
    color: var(--primary);
    font-weight: 600;
}
.search-sidebar {
    margin-top: 1rem;
}
.search-sidebar input {
    width: 100%;
    padding: 0.6rem 1rem;
    border: 1.5px solid var(--border);
    border-radius: 40px;
    font-size: 0.8rem;
}
.search-sidebar input:focus {
    outline: none;
    border-color: var(--primary);
}
/* KONTEN PRODUK */
.products-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}
.products-header h2 {
    font-family: var(--font-display);
    font-size: 1.5rem;
    font-weight: 700;
}
.product-count-info {
    font-size: 0.8rem;
    color: var(--text-lighter);
}
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1.5rem;
}
.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid var(--border);
    transition: var(--t);
}
.product-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}
.product-image {
    position: relative;
    aspect-ratio: 1/1;
    overflow: hidden;
    background: var(--bg-alt);
}
.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s;
}
.product-card:hover .product-image img { transform: scale(1.05); }
.stock-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    padding: 0.2rem 0.6rem;
    background: var(--success);
    color: white;
    font-size: 0.65rem;
    font-weight: 600;
    border-radius: 20px;
}
.stock-badge.out-of-stock { background: var(--error); }
.stock-badge.limited { background: var(--secondary); }
.product-info {
    padding: 0.8rem;
}
.product-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.product-price {
    font-weight: 700;
    color: var(--primary);
    font-size: 1rem;
}
.product-price small {
    font-size: 0.7rem;
    font-weight: 400;
    color: var(--text-lighter);
}
.product-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.quantity-wrapper {
    display: flex;
    align-items: center;
    border: 1.5px solid var(--border);
    border-radius: 8px;
}
.qty-btn {
    width: 28px;
    height: 30px;
    border: none;
    background: #f5f5f5;
    font-weight: 600;
    cursor: pointer;
}
.qty-btn:hover:not(:disabled) { background: var(--primary-light); }
.qty-btn:disabled { opacity: 0.3; }
.quantity-input {
    width: 34px;
    text-align: center;
    border: none;
    border-left: 1px solid var(--border);
    border-right: 1px solid var(--border);
    font-weight: 600;
    font-size: 0.8rem;
}
.btn-add-cart {
    flex: 1;
    padding: 0.4rem 0.5rem;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.7rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}
.btn-add-cart:hover:not(:disabled) { background: var(--primary-dark); }
.spinner {
    width: 12px;
    height: 12px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    display: none;
}
@keyframes spin { to { transform: rotate(360deg); } }
.load-more-wrap {
    text-align: center;
    margin-top: 2rem;
}
.btn-load-more {
    padding: 0.6rem 1.5rem;
    background: white;
    border: 1.5px solid var(--primary);
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
}
.empty-state {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 20px;
}
/* FOOTER */
.footer {
    background: var(--primary-dark);
    color: white;
    padding: 1.5rem 0;
    margin-top: 3rem;
    text-align: center;
    font-size: 0.8rem;
}
/* Responsive */
@media (max-width: 900px) {
    .catalog-layout {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .filter-sidebar {
        position: static;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }
    .filter-sidebar h3 { display: none; }
    .filter-group-side {
        flex-direction: row;
        margin-bottom: 0;
    }
    .search-sidebar { flex: 1; }
}
@media (max-width: 768px) {
    .nav-desktop { display: none; }
    .nav-mobile { display: flex; }
    .products-grid { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
}
@media (max-width: 500px) {
    .products-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<nav class="navbar">
  <div class="nav-container">
    <a href="../index.php" class="nav-logo"><span>Supermarket</span><span>Buah</span></a>
    <div class="nav-desktop">
      <span class="nav-greeting">Halo, <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong></span>
      <?php if (isAdmin()): ?><a href="../admin/dashboard.php" class="nav-btn">Dashboard</a><?php endif; ?>
      <a href="my_orders.php" class="nav-btn">Pesanan Saya</a>
      <a href="cart.php" class="nav-btn cart-link" id="cartLink">Keranjang<?php if($cart_count>0): ?><span class="cart-badge" id="cartBadge"><?php echo $cart_count; ?></span><?php endif; ?></a>
      <a href="../auth/logout.php" class="nav-btn solid">Logout</a>
    </div>
    <div class="nav-mobile">
      <a href="cart.php" class="cart-icon-btn" id="cartLinkMobile">🛒<?php if($cart_count>0): ?><span class="cart-badge" id="cartBadgeMobile"><?php echo $cart_count; ?></span><?php endif; ?></a>
      <div class="mobile-menu-wrap">
        <button class="hamburger-btn" id="hamburgerBtn"><span></span><span></span><span></span></button>
        <div class="mobile-menu" id="mobileMenu">
          <div class="m-user">Halo, <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong></div>
          <a href="my_orders.php">Pesanan Saya</a>
          <?php if(isAdmin()): ?><a href="../admin/dashboard.php">Dashboard</a><?php endif; ?>
          <a href="../auth/logout.php" class="logout">Logout</a>
        </div>
      </div>
    </div>
  </div>
</nav>

<div class="container">
  <div class="catalog-layout">
    <!-- SIDEBAR FILTER -->
    <aside class="filter-sidebar">
      <h3>Filter</h3>
      <div class="filter-group-side">
        <div class="filter-option"><a href="?filter=semua<?php echo $search?'&search='.urlencode($search):''; ?>" class="<?php echo $filter==='semua'?'active':''; ?>">Semua</a></div>
        <div class="filter-option"><a href="?filter=lokal<?php echo $search?'&search='.urlencode($search):''; ?>" class="<?php echo $filter==='lokal'?'active':''; ?>">Lokal</a></div>
        <div class="filter-option"><a href="?filter=impor<?php echo $search?'&search='.urlencode($search):''; ?>" class="<?php echo $filter==='impor'?'active':''; ?>">Impor</a></div>
      </div>
      <div class="search-sidebar">
        <form method="GET" action="">
          <input type="text" name="search" placeholder="Cari buah..." value="<?php echo htmlspecialchars($search); ?>">
          <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
        </form>
      </div>
    </aside>

    <!-- KONTEN PRODUK -->
    <main>
      <div class="products-header">
        <h2>Katalog Buah</h2>
        <div class="product-count-info">Menampilkan <strong id="shownCount"><?php echo count($products); ?></strong> dari <strong><?php echo $total_products; ?></strong> produk</div>
      </div>
      <?php if (count($products) > 0): ?>
      <div class="products-grid" id="productsGrid">
        <?php foreach ($products as $product):
          $stock = floatval($product['stok_kg']);
          $is_oos = $stock <= 0;
          $is_limited = !$is_oos && $stock <= 10;
          $badge_text = $is_oos?'Habis':($is_limited?'Terbatas':'Tersedia');
          $badge_cls = $is_oos?'out-of-stock':($is_limited?'limited':'');
        ?>
        <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
          <div class="product-image">
            <img src="<?php echo htmlspecialchars(getProductImage($product['gambar'])); ?>" alt="<?php echo htmlspecialchars($product['nama_buah']); ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400'">
            <span class="stock-badge <?php echo $badge_cls; ?>"><?php echo $badge_text; ?></span>
          </div>
          <div class="product-info">
            <div class="product-name"><?php echo htmlspecialchars($product['nama_buah']); ?></div>
            <div class="product-price">Rp <?php echo number_format($product['harga_kg'],0,',','.'); ?><small>/kg</small></div>
            <div class="product-actions">
              <div class="quantity-wrapper">
                <button class="qty-btn qty-minus" data-product-id="<?php echo $product['id']; ?>" <?php echo $is_oos?'disabled':''; ?>>−</button>
                <input type="number" class="quantity-input" min="1" value="1" max="<?php echo (int)$stock; ?>" data-product-id="<?php echo $product['id']; ?>" <?php echo $is_oos?'disabled':''; ?>>
                <button class="qty-btn qty-plus" data-product-id="<?php echo $product['id']; ?>" <?php echo $is_oos?'disabled':''; ?>>+</button>
              </div>
              <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['nama_buah']); ?>" <?php echo $is_oos?'disabled':''; ?>>
                <span class="btn-text">Tambah</span><span class="spinner"></span>
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="load-more-wrap" id="loadMoreWrap" style="<?php echo count($products)>=$total_products?'display:none':''; ?>">
        <button class="btn-load-more" id="btnLoadMore" data-offset="<?php echo count($products); ?>" data-search="<?php echo htmlspecialchars($search); ?>" data-filter="<?php echo htmlspecialchars($filter); ?>" data-total="<?php echo $total_products; ?>">Muat Lebih Banyak</button>
        <div class="load-more-count" id="loadMoreCount">Menampilkan <?php echo count($products); ?> dari <?php echo $total_products; ?> produk</div>
      </div>
      <?php else: ?>
      <div class="empty-state"><div class="empty-icon">🔍</div><h3>Produk tidak ditemukan</h3><p>Coba kata kunci lain atau hapus filter.</p><a href="catalog.php" class="nav-btn solid" style="display:inline-block;margin-top:1rem;">Reset Filter</a></div>
      <?php endif; ?>
    </main>
  </div>
</div>
<footer class="footer"><div class="container"><p>&copy; 2026 Supermarket Buah. Kesegaran Alami Setiap Hari.</p></div></footer>

<template id="tmplSkeleton"><div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-body"><div class="skeleton-line"></div><div class="skeleton-line w60"></div><div class="skeleton-line w40"></div></div></div></template>

<script>
function initQty(root){
    root=root||document;
    root.querySelectorAll('.qty-minus').forEach(b=>b.addEventListener('click',function(){
        const id=this.dataset.productId,inp=document.querySelector('.quantity-input[data-product-id="'+id+'"]');
        const min=parseInt(inp.min)||1;if(parseInt(inp.value)>min)inp.value=parseInt(inp.value)-1;syncMinus(id);
    }));
    root.querySelectorAll('.qty-plus').forEach(b=>b.addEventListener('click',function(){
        const id=this.dataset.productId,inp=document.querySelector('.quantity-input[data-product-id="'+id+'"]');
        const max=parseInt(inp.max),cur=parseInt(inp.value)||1;if(!max||cur<max)inp.value=cur+1;syncMinus(id);
    }));
    root.querySelectorAll('.quantity-input').forEach(inp=>{
        inp.addEventListener('change',function(){
            const min=parseInt(this.min)||1,max=parseInt(this.max);
            let v=parseInt(this.value)||min;if(v<min)v=min;if(max&&v>max)v=max;this.value=v;syncMinus(this.dataset.productId);
        });syncMinus(inp.dataset.productId);
    });
}
function syncMinus(id){
    const inp=document.querySelector('.quantity-input[data-product-id="'+id+'"]');
    const btn=document.querySelector('.qty-minus[data-product-id="'+id+'"]');
    if(inp&&btn)btn.disabled=parseInt(inp.value)<=(parseInt(inp.min)||1);
}
initQty();

function showToast(msg,type){
    const el=document.createElement('div');
    el.className='toast'+(type==='error'?' error':'');
    el.innerHTML='<div class="toast-icon">'+(type==='error'?'✗':'✓')+'</div><div class="toast-msg">'+msg+'</div>';
    document.body.appendChild(el);setTimeout(()=>el.remove(),3500);
}
function updateBadge(count){
    const link=document.getElementById('cartLink');
    let badge=document.getElementById('cartBadge');
    if(count>0){
        if(!badge){badge=document.createElement('span');badge.className='cart-badge';badge.id='cartBadge';link.appendChild(badge);}
        badge.textContent=count;
    }else if(badge)badge.remove();
}
function initCart(root){
    root=root||document;
    root.querySelectorAll('.btn-add-cart').forEach(btn=>btn.addEventListener('click',async function(){
        if(this.disabled)return;
        const pid=this.dataset.productId,name=this.dataset.productName;
        const inp=document.querySelector('.quantity-input[data-product-id="'+pid+'"]');
        const qty=parseInt(inp?.value)||1;
        this.disabled=true;this.classList.add('loading');
        const span=this.querySelector('.btn-text'),spin=this.querySelector('.spinner');
        span.style.opacity='0';spin.style.display='block';
        try{
            const fd=new FormData();fd.append('product_id',pid);fd.append('quantity',qty);
            const res=await fetch('../api/add_to_cart.php',{method:'POST',body:fd});
            const data=await res.json();
            if(data.success){
                updateBadge(data.data.cart_count);
                if(inp){inp.value=1;syncMinus(pid);}
                const orig=span.dataset.orig||span.textContent;span.dataset.orig=orig;
                span.textContent='✓';span.style.opacity='1';
                showToast(name+' ditambahkan!');
                setTimeout(()=>{span.textContent=orig;},2000);
            }else{showToast(data.message,'error');}
        }catch(e){showToast('Terjadi kesalahan','error');}
        finally{
            this.disabled=false;this.classList.remove('loading');span.style.opacity='1';spin.style.display='none';
        }
    }));
}
initCart();

const btnMore=document.getElementById('btnLoadMore');
if(btnMore){
    btnMore.addEventListener('click',async function(){
        const offset=parseInt(this.dataset.offset),total=parseInt(this.dataset.total);
        const search=this.dataset.search,filter=this.dataset.filter,limit=12;
        const grid=document.getElementById('productsGrid'),tmpl=document.getElementById('tmplSkeleton');
        const sw=document.createElement('div');sw.id='skelWrap';sw.style.cssText='display:contents';
        for(let i=0;i<Math.min(6,total-offset);i++)sw.appendChild(tmpl.content.cloneNode(true));
        grid.appendChild(sw);
        this.disabled=true;
        this.textContent='Memuat...';
        try{
            const qs=new URLSearchParams({offset,limit,search,filter});
            const res=await fetch('../api/load_more_products.php?'+qs);
            const data=await res.json();
            document.getElementById('skelWrap')?.remove();
            if(data.success&&data.html){
                const tmp=document.createElement('div');tmp.innerHTML=data.html;
                const cards=[...tmp.querySelectorAll('.product-card')];
                cards.forEach(c=>grid.appendChild(c));
                initQty(grid);initCart(grid);
                const newOffset=offset+cards.length;this.dataset.offset=newOffset;
                document.getElementById('shownCount').textContent=newOffset;
                document.getElementById('loadMoreCount').textContent='Menampilkan '+newOffset+' dari '+data.total+' produk';
                if(newOffset>=data.total){
                    document.getElementById('loadMoreWrap').innerHTML='<p class="load-more-done">Semua produk sudah ditampilkan</p>';
                    return;
                }
                this.textContent='Muat Lebih Banyak';
            }else{showToast('Gagal memuat produk','error');}
        }catch(e){document.getElementById('skelWrap')?.remove();showToast('Terjadi kesalahan','error');}
        finally{
            if(this.isConnected){this.disabled=false;this.textContent='Muat Lebih Banyak';}
        }
    });
}
document.querySelectorAll('.search-sidebar input').forEach(inp=>{let t;inp.addEventListener('input',function(){clearTimeout(t);t=setTimeout(()=>this.form.submit(),500);});});
const hamburgerBtn=document.getElementById('hamburgerBtn'),mobileMenu=document.getElementById('mobileMenu');
if(hamburgerBtn){
    hamburgerBtn.addEventListener('click',function(e){e.stopPropagation();this.classList.toggle('open');mobileMenu.classList.toggle('open');});
    document.addEventListener('click',function(e){if(!hamburgerBtn.contains(e.target)&&!mobileMenu.contains(e.target)){hamburgerBtn.classList.remove('open');mobileMenu.classList.remove('open');}});
}
function updateBadgeMobile(count){const link=document.getElementById('cartLinkMobile');let badge=document.getElementById('cartBadgeMobile');if(!link)return;if(count>0){if(!badge){badge=document.createElement('span');badge.className='cart-badge';badge.id='cartBadgeMobile';link.appendChild(badge);}badge.textContent=count;}else if(badge)badge.remove();}
const origUpdateBadge=updateBadge;
window.updateBadge=function(count){origUpdateBadge(count);updateBadgeMobile(count);};
</script>
</body>
</html>