<?php
require_once 'guard.php';
requireCustomer();
$cust = customerData();
require_once '../config/db.php';

$error   = $_GET['error']   ?? '';
$success = $_GET['success'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$cust['id']]);
$pesananList = $stmt->fetchAll();

$statusLabel = [
    'menunggu'     => ['label'=>'Menunggu',     'cls'=>'s-amber'],
    'diproses'     => ['label'=>'Diproses',     'cls'=>'s-blue'],
    'siap_diambil' => ['label'=>'Siap Diambil', 'cls'=>'s-teal'],
    'selesai'      => ['label'=>'Selesai',      'cls'=>'s-green'],
    'dibatalkan'   => ['label'=>'Dibatalkan',   'cls'=>'s-red'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Pesanan Saya</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="style3.css">
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
        <a href="katalog.php" class="nav-btn"> <i class="fas fa-capsules" style="margin-right: 6px"></i> Katalog</a>
        <form action="../auth/handler.php" method="POST" style="display:inline">
        <input type="hidden" name="action" value="logout_customer"/>
        <button class="logout-btn"> <i class="fas fa-sign-out-alt"></i> Keluar</button>
        </form>
    </div>
</nav>

<div class="container">
    <h1>📋 Pesanan Saya</h1>

    <?php if($error):   ?><div class="alert alert-e">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-s">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

    <?php if(empty($pesananList)): ?>
    <div class="empty">
        <div class="ei">📭</div>
        <p>Kamu belum punya pesanan</p>
        <a href="katalog.php">Mulai Pesan Obat →</a>
    </div>
    <?php else: ?>

    <?php foreach($pesananList as $p):
        $det = $pdo->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id=?");
        $det->execute([$p['id']]);
        $details = $det->fetchAll();
        $st = $statusLabel[$p['status']] ?? ['label'=>$p['status'],'cls'=>'s-amber'];
    ?>
    <div class="pes-card">
        <div class="pes-head">
        <div>
            <div class="pes-kode"><?= htmlspecialchars($p['kode_pesanan']) ?></div>
            <div class="pes-meta"><?= date('d M Y, H:i', strtotime($p['created_at'])) ?></div>
        </div>
        <span class="status-badge <?= $st['cls'] ?>"><?= $st['label'] ?></span>
        </div>

        <!-- Kode untuk ditunjukkan -->
        <div class="kode-strip">
        <div style="margin-right:28px">
            <div class="k-label">Kode Transaksi</div>
            <div class="k-val"><?= htmlspecialchars($p['kode_pesanan']) ?></div>
        </div>
        <div style="font-size:.78rem;color:var(--muted);flex:2">Tunjukkan kode ini kepada apoteker saat mengambil obat</div>
        </div>

        <!-- Detail obat -->
        <table class="detail-table">
        <thead><tr><th>Nama Obat</th><th>Harga</th><th>Jml</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php foreach($details as $d): ?>
            <tr>
            <td style="font-weight:600"><?= htmlspecialchars($d['nama_obat']) ?></td>
            <td style="color:var(--muted)">Rp <?= number_format($d['harga_satuan'],0,',','.') ?></td>
            <td><?= $d['jumlah'] ?></td>
            <td style="font-weight:700;color:var(--teal)">Rp <?= number_format($d['subtotal'],0,',','.') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        </table>

        <div class="pes-foot">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <span class="metode-pill"><?= $p['metode_bayar']==='transfer'?'🏦 Transfer':'💵 Cash di Kasir' ?></span>
            <span class="pes-total">Total: Rp <?= number_format($p['total_harga'],0,',','.') ?></span>
        </div>
        <?php if($p['status']==='menunggu'): ?>
        <form action="pesanan_handler.php" method="POST">
            <input type="hidden" name="action" value="batal"/>
            <input type="hidden" name="id" value="<?= $p['id'] ?>"/>
            <button type="submit" class="btn-cancel" onclick="return confirm('Batalkan pesanan ini?')">✕ Batalkan</button>
        </form>
        <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>