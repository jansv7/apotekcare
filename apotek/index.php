<?php
session_start();
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$tab   = $_GET['tab']   ?? 'login';
$error = $_GET['error'] ?? '';
$msg   = $_GET['msg']   ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>ApotekCare — Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Lora:ital@0;1&display=swap" rel="stylesheet"/>
<link rel="icon" href="../foto/Logo.png">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="left-panel">
    <div class="lp-logo">
        <div class="cross">
            <img src="../foto/Logo.png" alt="Logo" style="width:28px;height:28px;object-fit:contain"/>
        </div>
        <div class="brand">Apotek<span>Care</span></div>
    </div>

<div class="lp-headline">Sistem Manajemen<br/>ApotekCare<br/></div>
<p class="lp-sub">Mengelola data obat, stok, dan transaksi penjualan dalam satu sistem.</p>

    <div class="lp-features">
        <div class="feature-item">
            <div class="feature-icon">💊</div>
            <div class="feature-text">Manajemen stok obat real-time</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">🧾</div>
            <div class="feature-text">Transaksi penjualan & kasir digital</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">⚠️</div>
            <div class="feature-text">Peringatan stok rendah & expired</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon">📊</div>
            <div class="feature-text">Dashboard statistik & laporan</div>
        </div>
    </div>
</div>

<div class="right-panel">
    <div class="form-card">
        <div class="form-logo">
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php elseif ($msg): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab-btn <?= $tab==='login'?'active':'' ?>" onclick="switchTab('login',this)">Masuk</button>
            <button class="tab-btn <?= $tab==='register'?'active':'' ?>" onclick="switchTab('register',this)">Daftar</button>
        </div>

        <div class="panel <?= $tab==='login'?'active':'' ?>" id="panel-login">
            <h2>Selamat Datang</h2>
            <p class="subtitle">Masuk untuk mengakses sistem apotek</p>
            <form action="../auth/handler.php" method="POST">
                <input type="hidden" name="action" value="login"/>
                <input type="hidden" name="from" value="staff"/>
                <div class="form-group">
                    <label>Alamat Email</label>
                    <div class="input-wrap">
                        <input type="email" name="email" placeholder="admin@apotek.com" required/>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="pw1" placeholder="•••••••••" required/>
                        <button type="button" class="eye" onclick="togglePw('pw1',this)">👁</button>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Masuk ke Dashboard →</button>
            </form>
            <p class="divider">Belum punya akun? <a href="#" onclick="switchTab('register')">Daftar sekarang</a></p>
        </div>

        <div class="panel <?= $tab==='register'?'active':'' ?>" id="panel-register">
            <h2>Buat Akun Baru</h2>
            <p class="subtitle">Daftarkan diri sebagai apoteker</p>
            <form action="../auth/handler.php" method="POST">
                <input type="hidden" name="action" value="register"/>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="dr. Budi Santoso" required/>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="budi@apotek.com" required/>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="pw2" placeholder="Min. 6 karakter" required/>
                        <button type="button" class="eye" onclick="togglePw('pw2',this)">👁</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <div class="input-wrap">
                        <input type="password" name="konfirm" id="pw3" placeholder="Ulangi password" required/>
                        <button type="button" class="eye" onclick="togglePw('pw3',this)">👁</button>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Buat Akun →</button>
            </form>
            <p class="divider">Sudah punya akun? <a href="#" onclick="switchTab('login')">Masuk</a></p>
        </div>

    </div>
</div>

<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    if (btn) btn.classList.add('active');
    else document.querySelectorAll('.tab-btn')[tab==='login'?0:1].classList.add('active');
}
function togglePw(id, btn) {
    const i = document.getElementById(id);
    i.type = i.type === 'password' ? 'text' : 'password';
    btn.textContent = i.type === 'password' ? '👁' : '👁';
}
</script>
</body>
</html>