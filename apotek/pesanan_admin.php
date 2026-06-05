<?php
$pageTitle='Kelola Pesanan'; $activePage='pesanan';
require_once '../config/db.php';
require_once '../includes/layout.php';

$filterStatus = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');

$where = ['1=1']; $params = [];
if ($filterStatus) { $where[] = "p.status=?"; $params[] = $filterStatus; }
if ($search)       { $where[] = "(p.kode_pesanan LIKE ? OR u.nama LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$stmt = $pdo->prepare("SELECT p.*, u.nama as nama_customer, u.no_hp
    FROM pesanan p LEFT JOIN users u ON p.user_id=u.id
    WHERE ".implode(' AND ',$where)."
    ORDER BY FIELD(p.status,'menunggu','diproses','siap_diambil','selesai','dibatalkan'), p.created_at DESC");
$stmt->execute($params);
$pesananList = $stmt->fetchAll();

$statusOpt = [
    'menunggu'     => ['label'=>'Menunggu',     'cls'=>'b-amber'],
    'diproses'     => ['label'=>'Diproses',     'cls'=>'b-blue'],
    'siap_diambil' => ['label'=>'Siap Diambil', 'cls'=>'b-green'],
    'selesai'      => ['label'=>'Selesai',      'cls'=>'b-gray'],
    'dibatalkan'   => ['label'=>'Dibatalkan',   'cls'=>'b-red'],
];

$counts = $pdo->query("SELECT status, COUNT(*) as c FROM pesanan GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
    <?php
    $tabs=[''=> 'Semua','menunggu'=>' Menunggu ', 'diproses'=>' Diproses','siap_diambil'=>' Siap Diambil','selesai'=>' Selesai','dibatalkan'=>' Dibatalkan'];
    foreach($tabs as $val=>$label):
        $cnt = $val===''?array_sum($counts):($counts[$val]??0);
        $active = $filterStatus===$val;
    ?>
    <a href="pesanan_admin.php?status=<?= $val ?><?= $search?"&q=$search":'' ?>"
        style="padding:7px 14px;border-radius:99px;font-size:.8rem;font-weight:700;border:1.5px solid <?= $active?'var(--green)':'var(--border)' ?>;background:<?= $active?'var(--green)':'var(--white)' ?>;color:<?= $active?'#fff':'var(--muted)' ?>;text-decoration:none;transition:all .2s">
        <?= $label ?> <?php if($cnt>0): ?><span style="opacity:.8">(<?= $cnt ?>)</span><?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="toolbar">
    <div class="search-box">
        <span class="search-icon"><i class="fas fa-search" style="color: #a9a4b0"></i></span>
        <input type="text" id="sInput" placeholder="Cari kode atau nama customer…"
            value="<?= htmlspecialchars($search) ?>" onkeyup="liveSearch('sInput','tPes')"/>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:18px 22px 0">
        <div class="card-title" style="margin:0"> Daftar Pesanan Customer <span class="pill"><?= count($pesananList) ?></span></div>
    </div>
    <div class="table-wrap" style="padding:12px 0 0">
        <table id="tPes">
        <thead>
            <tr><th>#</th><th>Kode Pesanan</th><th>Customer</th><th>Total</th><th>Metode</th><th>Status</th><th>Waktu</th><th style="text-align:center">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if(empty($pesananList)): ?>
            <tr><td colspan="8"><div class="empty"><div class="ei">📭</div><p>Tidak ada pesanan</p></div></td></tr>
        <?php else: ?>
        <?php foreach($pesananList as $i=>$p): $st=$statusOpt[$p['status']]??['label'=>$p['status'],'cls'=>'b-gray']; ?>
            <tr>
            <td style="color:var(--muted);font-size:.78rem"><?= $i+1 ?></td>
            <td>
                <span class="badge b-blue" style="letter-spacing:.5px"><?= htmlspecialchars($p['kode_pesanan']) ?></span>
            </td>
            <td>
                <div style="font-weight:700"><?= htmlspecialchars($p['nama_customer']??'—') ?></div>
                <div style="font-size:.72rem;color:var(--muted)"><?= htmlspecialchars($p['no_hp']??'') ?></div>
            </td>
            <td style="font-weight:700;color:var(--green)">Rp <?= number_format($p['total_harga'],0,',','.') ?></td>
            <td>
                <span class="badge <?= $p['metode_bayar']==='transfer'?'b-blue':'b-gray' ?>">
                <?= $p['metode_bayar']==='transfer'?'🏦 Transfer':'💵 Cash' ?>
                </span>
            </td>
            <td><span class="badge <?= $st['cls'] ?>"><?= $st['label'] ?></span></td>
            <td style="font-size:.78rem;color:var(--muted)"><?= date('d M, H:i',strtotime($p['created_at'])) ?></td>
            <td style="text-align:center;white-space:nowrap">
                <button class="btn btn-blue btn-sm" onclick="lihatDetail(<?= $p['id'] ?>)">Detail</button>
                <?php if($p['status'] !== 'selesai' && $p['status'] !== 'dibatalkan'): ?>
                <button class="btn btn-amber btn-sm" style="margin-left:4px" onclick="openUpdateStatus(<?= $p['id'] ?>,'<?= $p['status'] ?>','<?= htmlspecialchars(addslashes($p['kode_pesanan'])) ?>')"> Status</button>
                <?php endif; ?>
            </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail -->
<div class="overlay" id="m-detail">
<div class="modal" style="width:min(520px,100%)">
    <div class="modal-hd">
        <div class="modal-title" id="d-kode">Detail Pesanan</div>
        <button class="modal-x" onclick="closeModal('m-detail')">✕</button>
    </div>
    <div id="d-info" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px"></div>
    <div class="table-wrap">
        <table>
        <thead><tr><th>Nama Obat</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
        <tbody id="d-tbody"></tbody>
        </table>
    </div>
    <div style="display:flex;justify-content:flex-end;margin-top:14px;padding-top:12px;border-top:1px solid var(--border)">
        <div style="text-align:right">
        <div style="font-size:.7rem;color:var(--muted);font-weight:700;text-transform:uppercase">Total</div>
        <div style="font-size:1.15rem;font-weight:800;color:var(--green)" id="d-total"></div>
        </div>
    </div>
</div>
</div>

<!-- Modal Update Status -->
<div class="overlay" id="m-status">
<div class="modal" style="width:min(400px,100%)">
    <div class="modal-hd">
        <div class="modal-title">⚙️ Update Status Pesanan</div>
        <button class="modal-x" onclick="closeModal('m-status')">✕</button>
    </div>
    <form action="pesanan_admin_handler.php" method="POST">
        <input type="hidden" name="action" value="update_status"/>
        <input type="hidden" name="id" id="us-id"/>
        <div class="fg" style="margin-bottom:16px">
        <label>Kode Pesanan</label>
        <input type="text" id="us-kode" readonly style="background:var(--bg);color:var(--muted)"/>
        </div>
        <div class="fg" style="margin-bottom:20px">
        <label>Status Baru</label>
        <select name="status" id="us-status">
            <option value="menunggu"> Menunggu</option>
            <option value="diproses"> Diproses</option>
            <option value="siap_diambil"> Siap Diambil</option>
            <option value="selesai"> Selesai</option>
            <option value="dibatalkan"> Dibatalkan</option>
        </select>
        </div>
        <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-outline" onclick="closeModal('m-status')">Batal</button>
        <button type="submit" class="btn btn-green"> Simpan Status</button>
        </div>
    </form>
</div>
</div>

<script>
function lihatDetail(id){
    fetch('pesanan_admin_handler.php?action=detail&id='+id)
        .then(r=>r.json())
        .then(d=>{
        document.getElementById('d-kode').textContent=' '+d.kode_pesanan;
        const fmt=n=>'Rp '+parseInt(n).toLocaleString('id-ID');
        document.getElementById('d-info').innerHTML=`
            <div style="background:var(--bg);padding:11px;border-radius:8px"><div style="font-size:.68rem;font-weight:700;color:var(--muted)">CUSTOMER</div><div style="font-weight:700">${d.nama_customer||'—'}</div><div style="font-size:.75rem;color:var(--muted)">${d.no_hp||''}</div></div>
            <div style="background:var(--bg);padding:11px;border-radius:8px"><div style="font-size:.68rem;font-weight:700;color:var(--muted)">METODE BAYAR</div><div style="font-weight:700">${d.metode_bayar==='transfer'?'🏦 Transfer':'💵 Cash'}</div></div>
            <div style="background:var(--bg);padding:11px;border-radius:8px"><div style="font-size:.68rem;font-weight:700;color:var(--muted)">STATUS</div><div style="font-weight:700">${d.status}</div></div>
            <div style="background:var(--bg);padding:11px;border-radius:8px"><div style="font-size:.68rem;font-weight:700;color:var(--muted)">CATATAN</div><div style="font-size:.83rem">${d.catatan||'—'}</div></div>`;
        let rows='';
        d.detail.forEach(x=>{
            rows+=`<tr><td style="padding:10px 12px;font-weight:600">${x.nama_obat}</td><td style="padding:10px 12px">${fmt(x.harga_satuan)}</td><td style="padding:10px 12px;text-align:center">${x.jumlah}</td><td style="padding:10px 12px;font-weight:700;color:var(--green)">${fmt(x.subtotal)}</td></tr>`;
        });
        document.getElementById('d-tbody').innerHTML=rows;
        document.getElementById('d-total').textContent=fmt(d.total_harga);
        openModal('m-detail');
        });
}

function openUpdateStatus(id,status,kode){
    document.getElementById('us-id').value=id;
    document.getElementById('us-kode').value=kode;
    document.getElementById('us-status').value=status;
    openModal('m-status');
}
</script>

<?php require_once '../includes/layout_end.php'; ?>