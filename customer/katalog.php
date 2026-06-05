<?php
require_once 'guard.php';
requireCustomer();
$cust = customerData();
require_once '../config/db.php';

$search    = trim($_GET['q'] ?? '');
$filterKat = $_GET['kat'] ?? '';

$katList = $pdo->query("SELECT * FROM kategori_obat ORDER BY nama")->fetchAll();

$where = ["o.stok > 0"]; $params = [];
if ($search)    { $where[] = "o.nama LIKE ?"; $params[] = "%$search%"; }
if ($filterKat) { $where[] = "o.kategori_id = ?"; $params[] = $filterKat; }

$stmt = $pdo->prepare("SELECT o.*, k.nama as kat_nama FROM obat o LEFT JOIN kategori_obat k ON o.kategori_id=k.id WHERE ".implode(' AND ',$where)." ORDER BY o.nama");
$stmt->execute($params);
$obatList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Katalog Obat</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style2.css">
<link rel="icon" href="../foto/Logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav>
    <div class="nav-logo">
        <div class="box">
        <img src="../foto/Logo.png" alt="Logo"/>
        </div>
        <div class="brand-text">
            Apotek<span>Care</span>
        </div>
    </div>
    <div class="nav-right">
        <span class="nav-user"> Hallo,  <?= htmlspecialchars($cust['nama']) ?>!</span>
        <button class="cart-btn" onclick="toggleCart()">
        <i class="fa fa-shopping-cart"></i> Keranjang
        <span class="cart-count" id="cartCount" style="display:none">0</span>
        </button>
        <a href="pesanan.php"><button class="order-btn"> <i class="fas fa-clipboard-list" style="margin-right: 2px"></i> Pesanan Saya</button></a>
        <form action="../auth/handler.php" method="POST" style="display:inline">
        <input type="hidden" name="action" value="logout_customer"/>
        <button class="logout-btn"> <i class="fas fa-sign-out-alt"></i> Keluar</button>
        </form>
    </div>
</nav>

<div class="hero">
    <h1><i class="fas fa-prescription-bottle-alt" style="margin-right: 6px"></i> Katalog Obat ApotekCare</h1>
    <p>Cek ketersediaan stok dan pesan obat dari rumah</p>
    <form class="hero-search" method="GET">
        <input type="text" name="q" placeholder="Cari nama obat…" value="<?= htmlspecialchars($search) ?>"/>
        <button type="submit"><i class="fas fa-search"></i> Cari</button>
    </form>
</div>

<div class="container">
    <div class="filter-bar">
        <a href="katalog.php" class="filter-chip <?= !$filterKat?'active':'' ?>">Semua</a>
        <?php foreach($katList as $k): ?>
        <a href="katalog.php?kat=<?= $k['id'] ?><?= $search?"&q=$search":'' ?>"
        class="filter-chip <?= $filterKat==$k['id']?'active':'' ?>">
        <?= htmlspecialchars($k['nama']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if(empty($obatList)): ?>
    <div style="text-align:center;padding:60px 20px;color:var(--muted)">
        <div style="font-size:3rem;margin-bottom:12px"><i class="fas fa-search" style="color: #a9a4b0"></i></div>
        <p>Obat tidak ditemukan atau stok habis</p>
    </div>
    <?php else: ?>
    <div class="grid">
        <?php foreach($obatList as $o): ?>
        <div class="obat-card">
        <div class="obat-name"><?= htmlspecialchars($o['nama']) ?></div>
        <div class="obat-kat"><?= htmlspecialchars($o['kat_nama']??'—') ?></div>
        <div class="obat-price">Rp <?= number_format($o['harga_jual'],0,',','.') ?> <span style="font-size:.72rem;font-weight:500;color:var(--muted)">/ <?= $o['satuan'] ?></span></div>
        <div class="obat-stok">
            <span class="stok-badge <?= $o['stok']<=10?'stok-low':'stok-ok' ?>">
            <?= $o['stok']<=10?' Stok terbatas':' Tersedia' ?> (<?= $o['stok'] ?>)
            </span>
        </div>
        <button class="add-btn" onclick='addCart(<?= htmlspecialchars(json_encode([
            "id"    => $o["id"],
            "nama"  => $o["nama"],
            "harga" => (float)$o["harga_jual"],
            "satuan"=> $o["satuan"],
            "stok"  => (int)$o["stok"]
        ])) ?>)'>+ Tambah ke Keranjang</button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-head">
        <h3>Keranjang Belanja</h3>
        <button class="close-cart" onclick="toggleCart()">✕</button>
    </div>
    <div class="cart-body" id="cartBody">
        <div class="cart-empty"><div class="ei">🛒</div><p>Keranjang masih kosong</p></div>
    </div>
    <div class="cart-foot">
        <div class="cart-total">
        <span>Total</span>
        <span id="cartTotal">Rp 0</span>
        </div>
        <button class="checkout-btn" id="checkoutBtn" onclick="openCheckout()" disabled>Lanjut Checkout →</button>
    </div>
</div>

<div class="overlay" id="m-checkout">
<div class="modal">
    <div class="modal-hd">
        <div class="modal-title"> Checkout Pesanan</div>
        <button class="modal-x" onclick="document.getElementById('m-checkout').classList.remove('open')">✕</button>
    </div>

    <div class="fg">
        <label>Nama Penerima</label>
        <input type="text" id="co-nama" value="<?= htmlspecialchars($cust['nama']) ?>" required/>
    </div>

    <div style="margin-bottom:16px">
        <label style="display:block;font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;margin-bottom:10px">Metode Pembayaran</label>
        <div class="metode-wrap">
        <label class="metode-card selected" id="m-cash" onclick="pilihMetode('cash')">
            <input type="radio" name="metode" value="cash" checked/>
            <div class="metode-icon">💵</div>
            <div class="metode-label">Cash</div>
            <div class="metode-desc">Bayar di kasir</div>
        </label>
        <label class="metode-card" id="m-transfer" onclick="pilihMetode('transfer')">
            <input type="radio" name="metode" value="transfer"/>
            <div class="metode-icon">🏦</div>
            <div class="metode-label">Transfer</div>
            <div class="metode-desc">Transfer bank</div>
        </label>
        </div>
    </div>

    <div id="transfer-info" style="display:none;background:#f0fdfa;border:1px solid var(--teal-m);border-radius:10px;padding:14px;margin-bottom:16px;font-size:.83rem">
        <b style="color:var(--teal)">Info Transfer:</b><br/>
        Bank BCA — No. Rek: <b>1234567890</b> a/n ApotekCare<br/>
        <span style="color:var(--muted)">Tunjukkan bukti transfer kepada apoteker saat mengambil obat.</span>
    </div>

    <div class="fg">
        <label>Catatan (opsional)</label>
        <input type="text" id="co-catatan" placeholder="Resep dokter, dll…"/>
    </div>

    <div class="order-summary" id="co-summary"></div>

    <button class="btn-submit" onclick="simpanPesanan()"> Buat Pesanan</button>
    <button class="btn-outline" onclick="document.getElementById('m-checkout').classList.remove('open')">Kembali ke Keranjang</button>
</div>
</div>

<div class="overlay" id="m-kode">
<div class="modal" style="width:min(400px,100%)">
    <div style="text-align:center;padding:10px 0 20px">
        <div style="font-size:3rem;margin-bottom:10px">🎉</div>
        <h2 style="font-size:1.2rem;font-weight:800;margin-bottom:6px">Pesanan Berhasil!</h2>
        <p style="font-size:.855rem;color:var(--muted);margin-bottom:20px">Tunjukkan kode ini kepada apoteker saat kamu tiba di apotek</p>
        <div class="kode-display" id="kode-result">—</div>
        <p class="kode-info">Simpan kode ini baik-baik. Apoteker akan memverifikasi pesananmu menggunakan kode tersebut.</p>
        <div style="margin-top:20px;padding:12px;background:var(--bg);border-radius:10px;font-size:.82rem;color:var(--muted);text-align:left">
        <b>Selanjutnya:</b><br/>
        1. Datang ke ApotekCare<br/>
        2. Tunjukkan kode pesanan ke apoteker<br/>
        3. Bayar sesuai metode yang dipilih<br/>
        4. Ambil obat kamu ✅
        </div>
        <button onclick="window.location.href='pesanan.php'" style="width:100%;margin-top:16px;padding:12px;background:var(--teal);color:#fff;border:none;border-radius:9px;font-size:.9rem;font-weight:700;cursor:pointer">
        Lihat Riwayat Pesanan
        </button>
    </div>
</div>
</div>

<script>
let cart = JSON.parse(localStorage.getItem('apotek_cart')) || [];
const fmt = n => 'Rp ' + parseInt(n).toLocaleString('id-ID');
let metodeBayar = 'cash';

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
});

function saveCart() {
    localStorage.setItem('apotek_cart', JSON.stringify(cart));
}

function addCart(o){
    const ex = cart.find(c=>c.id==o.id);
    if(ex){
        if(ex.qty>=o.stok){ alert('Stok tidak mencukupi!'); return; }
        ex.qty++; ex.subtotal=ex.harga*ex.qty;
    } else {
        cart.push({...o, qty:1, subtotal:o.harga});
    }
    
    saveCart(); 
    renderCart();
    
    const btn = event.target;
    btn.textContent='✓ Ditambahkan!';
    setTimeout(() => btn.textContent='+ Tambah ke Keranjang', 1200);
}

function renderCart(){
    const body = document.getElementById('cartBody');
    const total = cart.reduce((s,c)=>s+c.subtotal,0);
    const count = cart.reduce((s,c)=>s+c.qty,0);

    const badge = document.getElementById('cartCount');
    badge.textContent = count;
    badge.style.display = count>0?'grid':'none';
    document.getElementById('cartTotal').textContent = fmt(total);
    document.getElementById('checkoutBtn').disabled = cart.length===0;

    if(cart.length===0){
        body.innerHTML='<div class="cart-empty"><div class="ei">🛒</div><p>Keranjang masih kosong</p></div>';
        return;
    }
    let html='';
    cart.forEach((c,i)=>{
        html+=`<div class="cart-item">
        <div class="ci-info">
            <div class="ci-name">${c.nama}</div>
            <div class="ci-price">${fmt(c.harga)} / ${c.satuan}</div>
        </div>
        <div class="ci-qty">
            <button class="qty-btn" onclick="changeQty(${i},-1)">−</button>
            <span class="qty-num">${c.qty}</span>
            <button class="qty-btn" onclick="changeQty(${i},1)">+</button>
        </div>
        <div style="font-weight:700;font-size:.83rem;min-width:70px;text-align:right">${fmt(c.subtotal)}</div>
        <button class="ci-del" onclick="removeItem(${i})">✕</button>
        </div>`;
    });
    body.innerHTML=html;
}

function changeQty(i,d){
    const c=cart[i];
    c.qty=Math.max(1,Math.min(c.stok,c.qty+d));
    c.subtotal=c.harga*c.qty;
    
    saveCart(); 
    renderCart();
}

function removeItem(i){ 
    cart.splice(i,1); 
    saveCart(); 
    renderCart(); 
}

function toggleCart(){
    document.getElementById('cartOverlay').classList.toggle('open');
    document.getElementById('cartDrawer').classList.toggle('open');
}

function pilihMetode(m){
    metodeBayar=m;
    document.getElementById('m-cash').classList.toggle('selected',m==='cash');
    document.getElementById('m-transfer').classList.toggle('selected',m==='transfer');
    document.getElementById('transfer-info').style.display=m==='transfer'?'block':'none';
}

function openCheckout(){
    let rows='';
    cart.forEach(c=>{ rows+=`<div class="os-row"><span>${c.nama} x${c.qty}</span><span>${fmt(c.subtotal)}</span></div>` });
    const total=cart.reduce((s,c)=>s+c.subtotal,0);
    rows+=`<div class="os-row os-total"><span>Total</span><span>${fmt(total)}</span></div>`;
    document.getElementById('co-summary').innerHTML=rows;
    toggleCart();
    document.getElementById('m-checkout').classList.add('open');
}

function simpanPesanan(){
    const nama = document.getElementById('co-nama').value.trim();
    if(!nama){ alert('Nama penerima wajib diisi'); return; }
    const total = cart.reduce((s,c)=>s+c.subtotal,0);
    const catatan = document.getElementById('co-catatan').value.trim();

    localStorage.removeItem('apotek_cart');

    const form=document.createElement('form');
    form.method='POST'; form.action='pesanan_handler.php';
    const add=(n,v)=>{ const i=document.createElement('input');i.type='hidden';i.name=n;i.value=v;form.appendChild(i); };
    add('action','buat');
    add('nama_penerima',nama);
    add('metode_bayar',metodeBayar);
    add('total_harga',total);
    add('catatan',catatan);
    add('cart',JSON.stringify(cart));
    document.body.appendChild(form);
    form.submit();
}

(function(){
    const p=new URLSearchParams(location.search);
    const kode=p.get('kode');
    if(kode){
        document.getElementById('kode-result').textContent=kode;
        document.getElementById('m-kode').classList.add('open');
        history.replaceState(null,'',location.pathname);
    }
})();
</script>
</body>
</html>