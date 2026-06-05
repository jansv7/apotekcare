<?php
session_start();
if (!empty($_SESSION['user_id']) && $_SESSION['user_role'] === 'customer') {
    header('Location: katalog.php'); exit;
}
$tab   = $_GET['tab']   ?? 'login';
$error = $_GET['error'] ?? '';
$msg   = $_GET['msg']   ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>ApotekCare</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Lora:ital@1&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style.css">
<link rel="icon" href="../foto/Logo.png">
</head>
<body>

<div class="left">
    <div class="logo">
        <div class="logo-box">
        <img src="../foto/logo.png"/>
        </div>
        <div class="logo-text">Apotek<span>Care</span></div>
    </div>
    <div class="headline">Pesan Obat<br/>Lebih Mudah,<br/>Tanpa Antre</div>
    <p class="sub">Cek ketersediaan obat dari rumah. Pesan online, bayar di kasir atau transfer, lalu ambil langsung di apotek.</p>
    <div class="steps">
        <div class="step">
        <div class="step-num">1</div>
        <div class="step-text"><b>Cari & Cek Stok</b> <br>Pastikan obat yang kamu butuhkan tersedia</div>
        </div>
        <div class="step">
        <div class="step-num">2</div>
        <div class="step-text"><b>Pesan Online</b> <br>Pilih obat dan metode pembayaran</div>
        </div>
        <div class="step">
        <div class="step-num">3</div>
        <div class="step-text"><b>Dapat Kode Transaksi</b> <br>Tunjukkan ke apoteker saat tiba</div>
        </div>
        <div class="step">
        <div class="step-num">4</div>
        <div class="step-text"><b>Ambil Obat</b> <br>Bayar sesuai metode yang dipilih & ambil obat</div>
        </div>
    </div>
</div>

<div class="right">
    <div class="form-wrap">
        <div class="portal-label">
        </div>

        <?php if($error): ?><div class="alert alert-e">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if($msg):   ?><div class="alert alert-s">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>

        <div class="tabs">
        <button class="tab-btn <?= $tab==='login'?'active':'' ?>" onclick="sw('login',this)">Masuk</button>
        <button class="tab-btn <?= $tab==='register'?'active':'' ?>" onclick="sw('register',this)">Daftar</button>
        </div>

        <div class="panel <?= $tab==='login'?'active':'' ?>" id="p-login">
        <h2>Selamat Datang</h2>
        <p class="subtitle">Masuk untuk memesan obat</p>
        <form action="../auth/handler.php" method="POST">
            <input type="hidden" name="action" value="login"/>
            <input type="hidden" name="from" value="customer"/>
            <div class="fg"><label>Email</label><input type="email" name="email" placeholder="email@kamu.com" required/></div>
            <div class="fg"><label>Password</label>
            <div class="pw-wrap">
                <input type="password" name="password" id="pw1" placeholder="••••••••" required/>
                <button type="button" class="eye" onclick="tp('pw1',this)">👁</button>
            </div>
            </div>
            <button type="submit" class="btn-submit">Masuk →</button>
        </form>
        <p class="divider">Belum punya akun? <a href="#" onclick="sw('register')">Daftar</a></p>
        </div>
        <div class="panel <?= $tab==='register'?'active':'' ?>" id="p-register">
        <h2>Buat Akun Baru</h2>
        <p class="subtitle">Daftar gratis, langsung bisa pesan</p>
        <form action="../auth/handler.php" method="POST">
            <input type="hidden" name="action" value="register_customer"/>
            <div class="fg"><label>Nama Lengkap</label><input type="text" name="nama" placeholder="Nama kamu" required/></div>
            <div class="fg"><label>Email</label><input type="email" name="email" placeholder="email@kamu.com" required/></div>
            <div class="fg"><label>No. HP / WhatsApp</label><input type="text" name="no_hp" placeholder="08123456789" required/></div>
            <div class="fg"><label>Password</label>
            <div class="pw-wrap">
                <input type="password" name="password" id="pw2" placeholder="Min. 6 karakter" required/>
                <button type="button" class="eye" onclick="tp('pw2',this)">👁</button>
            </div>
            </div>
            <div class="fg"><label>Konfirmasi Password</label>
            <div class="pw-wrap">
                <input type="password" name="konfirm" id="pw3" placeholder="Ulangi password" required/>
                <button type="button" class="eye" onclick="tp('pw3',this)">👁</button>
            </div>
            </div>
            <button type="submit" class="btn-submit">Buat Akun →</button>
        </form>
        <p class="divider">Sudah punya akun? <a href="#" onclick="sw('login')">Masuk</a></p>
        </div>
    </div>
</div>

<script>
function sw(tab,btn){
    document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('p-'+tab).classList.add('active');
    if(btn) btn.classList.add('active');
    else document.querySelectorAll('.tab-btn')[tab==='login'?0:1].classList.add('active');
}
function tp(id,btn){
    const i=document.getElementById(id);
    i.type=i.type==='password'?'text':'password';
    btn.textContent=i.type==='password'?'👁':'👁';
}
</script>
</body>
</html>