<?php
$pageTitle = 'Data Obat'; $activePage = 'obat';
require_once '../config/db.php';
require_once '../includes/layout.php';

$katList = $pdo->query("SELECT * FROM kategori_obat ORDER BY nama")->fetchAll();
$filterKat = $_GET['kat'] ?? '';
$search    = trim($_GET['q'] ?? '');
$where = ['1=1']; 
$params = [];

if($search){ 
    $where[]="(o.nama LIKE ? OR o.kode_obat LIKE ?)"; 
    $params[]="%$search%"; 
    $params[]="%$search%"; }
if($filterKat){ 
    $where[]="o.kategori_id=?"; 
    $params[]=$filterKat; }

$sql = "SELECT o.*,k.nama as kat_nama FROM obat o LEFT JOIN kategori_obat k ON o.kategori_id=k.id WHERE ".implode(' AND ',$where)." ORDER BY o.nama";
$stmt=$pdo->prepare($sql); $stmt->execute($params);
$obatList=$stmt->fetchAll();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<div class="toolbar">
    <div class="search-box">
        <span class="search-icon"><i class="fas fa-search" style="color: #a9a4b0"></i></span>
        <input type="text" id="sInput" placeholder="Cari nama atau kode obat…"
            value="<?= ($search) ?>" onkeyup="liveSearch('sInput','tObat')"/>
    </div>
    <select class="filter-sel" onchange="location.href='obat.php?kat='+this.value">
        <option value="">Semua Kategori</option>
        <?php foreach($katList as $k): ?>
        <option value="<?= $k['id'] ?>" <?= $filterKat==$k['id']?'selected':'' ?>><?= ($k['nama']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-green" onclick="openModal('m-tambah')">＋ Tambah Obat</button>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:18px 22px 0;display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
        <div class="card-title" style="margin:0">Daftar Obat <span class="pill"><?= count($obatList) ?></span></div>
    </div>
    <div class="table-wrap" style="padding:10px 0 0">
        <table id="tObat">
        <thead>
            <tr><th>#</th><th>Kode</th><th>Nama Obat</th><th>Kategori</th><th>Satuan</th><th>Harga Jual</th><th>Stok</th><th>Expired</th><th style="text-align:center">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if(empty($obatList)): ?>
            <tr><td colspan="9"><div class="empty"><div class="ei">📭</div><p>Belum ada data obat</p></div></td></tr>
        <?php else: ?>
        <?php foreach($obatList as $i=>$o):
            $expiredCls='';
            if($o['tanggal_expired']){
                $hari=(int)((strtotime($o['tanggal_expired'])-time())/86400);
            if($hari<=30) $expiredCls='b-red';
            elseif($hari<=60) $expiredCls='b-amber';
            else $expiredCls='b-green';
            }
            $stokCls=$o['stok']==0?'b-red':($o['stok']<=$o['stok_minimum']?'b-amber':'b-green');
        ?>
            <tr>
            <td style="color:var(--muted);font-size:.78rem"><?= $i+1 ?></td>
            <td><span class="badge b-blue"><?= htmlspecialchars($o['kode_obat']) ?></span></td>
            <td>
                <div style="font-weight:700"><?= htmlspecialchars($o['nama']) ?></div>
                <?php if($o['deskripsi']): ?>
                <div style="font-size:.72rem;color:var(--muted)"><?= htmlspecialchars(substr($o['deskripsi'],0,45)) ?>…</div>
                <?php endif; ?>
            </td>
            <td style="font-size:.82rem"><?= htmlspecialchars($o['kat_nama']??'—') ?></td>
            <td><span class="badge b-gray"><?= htmlspecialchars($o['satuan']) ?></span></td>
            <td style="font-weight:700;color:var(--green)">Rp <?= number_format($o['harga_jual'],0,',','.') ?></td>
            <td><span class="badge <?= $stokCls ?>"><?= $o['stok'] ?></span></td>
            <td>
                <?php if($o['tanggal_expired']): ?>
                <span class="badge <?= $expiredCls ?>"><?= date('M Y',strtotime($o['tanggal_expired'])) ?></span>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td style="text-align:center;white-space:nowrap">
                <button class="btn btn-amber btn-sm" onclick='openEdit(<?= htmlspecialchars(json_encode($o)) ?>)'>Edit</button>
                <button class="btn btn-red btn-sm" style="margin-left:4px" onclick="openDel(<?= $o['id'] ?>,'<?= htmlspecialchars(addslashes($o['nama'])) ?>')">🗑</button>
            </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="overlay" id="m-tambah">
<div class="modal">
    <div class="modal-hd">
        <div class="modal-title">Tambah Obat Baru</div>
        <button class="modal-x" onclick="closeModal('m-tambah')">✕</button>
    </div>
    <form action="obat_handler.php" method="POST">
        <input type="hidden" name="action" value="tambah"/>
        <div class="form-row">
        <div class="fg"><label>Nama Obat</label><input type="text" name="nama" placeholder="Nama lengkap obat" required/></div>
        <div class="fg"><label>Kategori</label>
            <select name="kategori_id">
            <option value="">— Pilih —</option>
            <?php foreach($katList as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
            <?php endforeach; ?>
            </select>
        </div>
        </div>
        <div class="form-row">
        <div class="fg"><label>Satuan</label>
            <select name="satuan">
            <option>tablet</option><option>kapsul</option><option>botol</option>
            <option>sachet</option><option>ampul</option><option>tube</option><option>strip</option>
            </select>
        </div>
        <div class="fg"><label>Stok Minimum</label><input type="number" name="stok_minimum" value="10" min="0"/></div>
        </div>
        <div class="form-row">
        <div class="fg"><label>Harga Beli (Rp)</label><input type="number" name="harga_beli" placeholder="0" min="0" required/></div>
        <div class="fg"><label>Harga Jual (Rp)</label><input type="number" name="harga_jual" placeholder="0" min="0" required/></div>
        </div>
        <div class="form-row">
        <div class="fg"><label>Stok Awal</label><input type="number" name="stok" placeholder="0" min="0" required/></div>
        <div class="fg"><label>Tanggal Expired</label><input type="date" name="tanggal_expired"/></div>
        </div>
        <div class="fg"><label>Deskripsi (opsional)</label><textarea name="deskripsi" placeholder="Keterangan obat…"></textarea></div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-outline" onclick="closeModal('m-tambah')">Batal</button>
        <button type="submit" class="btn btn-green"> Simpan Obat</button>
        </div>
    </form>
</div>
</div>

<!-- Modal Edit -->
<div class="overlay" id="m-edit">
<div class="modal">
    <div class="modal-hd">
        <div class="modal-title"> Edit Data Obat</div>
        <button class="modal-x" onclick="closeModal('m-edit')">✕</button>
    </div>
    <form action="obat_handler.php" method="POST">
        <input type="hidden" name="action" value="edit"/>
        <input type="hidden" name="id" id="e-id"/>
        <div class="form-row">
        <div class="fg"><label>Kode Obat</label><input type="text" name="kode_obat" id="e-kode" required/></div>
        <div class="fg"><label>Kategori</label>
            <select name="kategori_id" id="e-kat">
            <option value="">— Pilih —</option>
            <?php foreach($katList as $k): ?>
            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
            <?php endforeach; ?>
            </select>
        </div>
        </div>
        <div class="fg"><label>Nama Obat</label><input type="text" name="nama" id="e-nama" required/></div>
        <div class="form-row">
        <div class="fg"><label>Satuan</label>
            <select name="satuan" id="e-satuan">
            <option>tablet</option><option>kapsul</option><option>botol</option>
            <option>sachet</option><option>ampul</option><option>tube</option><option>strip</option>
            </select>
        </div>
        <div class="fg"><label>Stok Minimum</label><input type="number" name="stok_minimum" id="e-stokmin" min="0"/></div>
        </div>
        <div class="form-row">
        <div class="fg"><label>Harga Beli (Rp)</label><input type="number" name="harga_beli" id="e-beli" min="0" required/></div>
        <div class="fg"><label>Harga Jual (Rp)</label><input type="number" name="harga_jual" id="e-jual" min="0" required/></div>
        </div>
        <div class="form-row">
        <div class="fg"><label>Stok</label><input type="number" name="stok" id="e-stok" min="0" required/></div>
        <div class="fg"><label>Tanggal Expired</label><input type="date" name="tanggal_expired" id="e-exp"/></div>
        </div>
        <div class="fg"><label>Deskripsi</label><textarea name="deskripsi" id="e-desk"></textarea></div>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px">
        <button type="button" class="btn btn-outline" onclick="closeModal('m-edit')">Batal</button>
        <button type="submit" class="btn btn-green"> Update Obat</button>
        </div>
    </form>
</div>
</div>

<div class="confirm-overlay" id="c-del">
    <div class="confirm-box">
        <div style="font-size:2.4rem;margin-bottom:10px"></div>
        <h3>Hapus Obat?</h3>
        <p id="c-del-text">Data obat ini akan dihapus permanen.</p>
        <div class="confirm-actions">
        <button class="btn btn-outline" onclick="closeConfirm()">Batal</button>
        <form action="obat_handler.php" method="POST" style="display:inline">
            <input type="hidden" name="action" value="hapus"/>
            <input type="hidden" name="id" id="c-del-id"/>
            <button type="submit" class="btn btn-red">Ya, Hapus</button>
        </form>
        </div>
    </div>
</div>

<script>
function openEdit(d){
    document.getElementById('e-id').value    = d.id;
    document.getElementById('e-kode').value  = d.kode_obat;
    document.getElementById('e-nama').value  = d.nama;
    document.getElementById('e-beli').value  = d.harga_beli;
    document.getElementById('e-jual').value  = d.harga_jual;
    document.getElementById('e-stok').value  = d.stok;
    document.getElementById('e-stokmin').value = d.stok_minimum;
    document.getElementById('e-exp').value   = d.tanggal_expired||'';
    document.getElementById('e-desk').value  = d.deskripsi||'';
    document.getElementById('e-kat').value   = d.kategori_id||'';
    document.getElementById('e-satuan').value= d.satuan||'tablet';
    openModal('m-edit');
}
function openDel(id,nama){
    document.getElementById('c-del-id').value=id;
    document.getElementById('c-del-text').textContent=`Obat "${nama}" akan dihapus permanen.`;
    document.getElementById('c-del').classList.add('open');
}
</script>

<?php require_once '../includes/layout_end.php'; ?>