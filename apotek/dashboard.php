<?php
$pageTitle = 'Dashboard'; $activePage = 'dashboard';
require_once '../config/db.php';
require_once '../includes/layout.php';

$totalObat      = $pdo->query("SELECT COUNT(*) FROM obat")->fetchColumn();
$totalStok      = $pdo->query("SELECT SUM(stok) FROM obat")->fetchColumn() ?? 0;
$transaksiHari  = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$pendapatanHari = $pdo->query("SELECT SUM(total_harga) FROM transaksi WHERE DATE(created_at)=CURDATE()")->fetchColumn() ?? 0;

$stokRendah = $pdo->query("SELECT nama,stok,stok_minimum,kode_obat FROM obat WHERE stok <= stok_minimum ORDER BY stok ASC LIMIT 6")->fetchAll();
$akanExpired= $pdo->query("SELECT nama,tanggal_expired,kode_obat FROM obat WHERE tanggal_expired IS NOT NULL AND tanggal_expired <= DATE_ADD(CURDATE(),INTERVAL 60 DAY) AND tanggal_expired >= CURDATE() ORDER BY tanggal_expired ASC LIMIT 6")->fetchAll();

$transaksiTerbaru = $pdo->query("SELECT t.*,u.nama as kasir FROM transaksi t LEFT JOIN users u ON t.user_id=u.id ORDER BY t.created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Stats -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<div class="stats-grid">
    <div class="stat" style="--clr:var(--green);animation-delay:.04s">
        <div class="s-icon">💊</div>
        <div class="s-val"><?= $totalObat ?></div>
        <div class="s-lbl">Total Jenis Obat</div>
        <div class="s-note">▲ Terdaftar di sistem</div>
    </div>
    <div class="stat" style="--clr:var(--blue);animation-delay:.08s">
        <div class="s-icon">📦</div>
        <div class="s-val"><?= number_format($totalStok) ?></div>
        <div class="s-lbl">Total Stok</div>
        <div class="s-note">▲<?= count($stokRendah) ?> item stok rendah</div>
    </div>
    <div class="stat" style="--clr:var(--amber);animation-delay:.12s">
        <div class="s-icon">🧾</div>
        <div class="s-val"><?= $transaksiHari ?></div>
        <div class="s-lbl">Transaksi Hari Ini</div>
        <div class="s-note">▲ <?= date('d M Y') ?></div>
    </div>
    <div class="stat" style="--clr:var(--red);animation-delay:.16s">
        <div class="s-icon">💵</div>
        <div class="s-val">Rp <?= number_format($pendapatanHari/1000,0,',','.') ?>rb</div>
        <div class="s-lbl">Pendapatan Hari Ini</div>
        <div class="s-note">▲ Total penjualan</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
    <!-- Stok Rendah -->
    <div class="card">
        <div class="card-title"><i class="fas fa-exclamation-triangle" style="color: #ffb557"></i> Stok Hampir Habis</div>
        <?php if(empty($stokRendah)): ?>
            <div class="empty"><div class="ei"><i class="fas fa-check" style="color: #16a34a"></i></div><p>Semua stok aman</p></div>
        <?php else: ?>
        <?php foreach($stokRendah as $s): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--border)">
        <div>
            <div style="font-size:.855rem;font-weight:600"><?= ($s['nama']) ?></div>
            <div style="font-size:.72rem;color:var(--muted)"><?= $s['kode_obat'] ?> · min <?= $s['stok_minimum'] ?></div>
        </div>
            <span class="badge <?= $s['stok']==0?'b-red':'b-amber' ?>"><?= $s['stok'] ?> sisa</span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Akan Expired -->
    <div class="card">
        <div class="card-title">📅 Mendekati Expired <span style="font-size:.72rem;color:var(--muted);font-weight:500">(&lt; 60 hari)</span></div>
        <?php if(empty($akanExpired)): ?>
            <div class="empty"><div class="ei"><i class="fas fa-check" style="color: #16a34a"></i></div><p>Tidak ada obat mendekati expired</p></div>
        <?php else: ?>
        <?php foreach($akanExpired as $e): ?>
        <?php $hari = (int)((strtotime($e['tanggal_expired'])-time())/86400) ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--border)">
        <div>
            <div style="font-size:.855rem;font-weight:600"><?= ($e['nama']) ?></div>
            <div style="font-size:.72rem;color:var(--muted)"><?= date('d M Y',strtotime($e['tanggal_expired'])) ?></div>
        </div>
            <span class="badge <?= $hari<=30?'b-red':'b-amber' ?>"><?= $hari ?> hari</span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Transaksi Terbaru -->
<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:18px 22px 0;display:flex;justify-content:space-between;align-items:center">
        <div class="card-title" style="margin:0">🧾 Transaksi Terbaru
        <a href="transaksi.php" class="btn btn-outline btn-sm" style="margin-left:8px">Lihat Semua →</a>
        </div>
    </div>
    <div class="table-wrap" style="padding:12px 0 0">
        <table>
        <thead>
            <tr><th>Kode</th><th>Pembeli</th><th>Total</th><th>Kasir</th><th>Waktu</th></tr>
        </thead>
        <tbody>
            <?php if(empty($transaksiTerbaru)): ?>
                <tr><td colspan="5"><div class="empty"><div class="ei">📭</div><p>Belum ada transaksi</p></div></td></tr>
            <?php else: ?>
            <?php foreach($transaksiTerbaru as $t): ?>
            <tr>
                <td><span class="badge b-blue"><?= htmlspecialchars($t['kode_transaksi']) ?></span></td>
                <td style="font-weight:600"><?= htmlspecialchars($t['nama_pembeli']) ?></td>
                <td style="color:var(--green);font-weight:700">Rp <?= number_format($t['total_harga'],0,',','.') ?></td>
                <td style="color:var(--muted);font-size:.82rem"><?= htmlspecialchars($t['kasir']??'-') ?></td>
                <td style="font-size:.78rem;color:var(--muted)"><?= date('d M, H:i',strtotime($t['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/layout_end.php'; ?>