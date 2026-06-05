<?php
$pageTitle='Transaksi Penjualan'; $activePage='transaksi';
require_once '../config/db.php';
require_once '../includes/layout.php';

$search = trim($_GET['q']??'');
$where  = $search?"WHERE t.kode_transaksi LIKE ? OR t.nama_pembeli LIKE ?":"";
$params = $search?["%$search%","%$search%"]:[];

$stmt=$pdo->prepare("SELECT t.*,u.nama as kasir FROM transaksi t LEFT JOIN users u ON t.user_id=u.id $where ORDER BY t.created_at DESC");
$stmt->execute($params);
$trxList=$stmt->fetchAll();

$obatList=$pdo->query("SELECT id,kode_obat,nama,harga_jual,stok,satuan FROM obat WHERE stok>0 ORDER BY nama")->fetchAll();
$user=currentUser();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="toolbar">
    <div class="search-box">
        <span class="search-icon"><i class="fas fa-search" style="color: #a9a4b0"></i></span>
        <input type="text" id="sInput" placeholder="Cari kode atau nama pembeli…"
            value="<?= htmlspecialchars($search) ?>" onkeyup="liveSearch('sInput','tTrx')"/>
    </div>
    <button class="btn btn-green" onclick="openModal('m-trx')">＋ Transaksi Baru</button>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div style="padding:18px 22px 0">
        <div class="card-title" style="margin:0"> Riwayat Transaksi <span class="pill"><?= count($trxList) ?></span></div>
    </div>
    <div class="table-wrap" style="padding:12px 0 0">
        <table id="tTrx">
        <thead>
            <tr><th>Kode</th><th>Nama Pembeli</th><th>Total</th><th>Bayar</th><th>Kembalian</th><th>Kasir</th><th>Waktu</th><th style="text-align:center">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if(empty($trxList)): ?>
            <tr><td colspan="9"><div class="empty"><div class="ei">📭</div><p>Belum ada transaksi</p></div></td></tr>
        <?php else: ?>
        <?php foreach($trxList as $i=>$t): ?>
            <tr>
            <td><span class="badge b-blue"><?= htmlspecialchars($t['kode_transaksi']) ?></span></td>
            <td style="font-weight:700"><?= htmlspecialchars($t['nama_pembeli']) ?></td>
            <td style="font-weight:700;color:var(--green)">Rp <?= number_format($t['total_harga'],0,',','.') ?></td>
            <td>Rp <?= number_format($t['bayar'],0,',','.') ?></td>
            <td style="color:var(--blue);font-weight:600">Rp <?= number_format($t['kembalian'],0,',','.') ?></td>
            <td style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($t['kasir']??'—') ?></td>
            <td style="font-size:.78rem;color:var(--muted)"><?= date('d M Y, H:i',strtotime($t['created_at'])) ?></td>
            <td style="text-align:center">
                <button class="btn btn-blue btn-sm" onclick="lihatDetail(<?= $t['id'] ?>)">Detail</button>
                <button class="btn btn-red btn-sm" style="margin-left:4px" onclick="openDelTrx(<?= $t['id'] ?>,'<?= htmlspecialchars(addslashes($t['kode_transaksi'])) ?>')">🗑</button>
            </td>   
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
        </table>
    </div>
</div>

<!-- Modal Transaksi -->
<div class="overlay" id="m-trx">
<div class="modal" style="width:min(680px,100%)">
    <div class="modal-hd">
        <div class="modal-title">🧾 Transaksi Baru</div>
        <button class="modal-x" onclick="closeModal('m-trx')">✕</button>
    </div>

    <!-- Tambah item -->
    <div style="background:var(--bg);border-radius:10px;padding:16px;margin-bottom:16px">
        <div style="font-size:.78rem;font-weight:700;color:var(--muted);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px">Pilih Obat</div>
        <div class="form-row" style="align-items:flex-end">
            <div class="fg" style="margin:0;grid-column:span 1">
                <label>Obat</label>
                <select id="pilihObat" onchange="setHarga()">
                <option value="">— Pilih Obat —</option>
                <?php foreach($obatList as $o): ?>
                <option value="<?= $o['id'] ?>" data-harga="<?= $o['harga_jual'] ?>" data-stok="<?= $o['stok'] ?>" data-satuan="<?= htmlspecialchars($o['satuan']) ?>">
                    <?= htmlspecialchars($o['nama']) ?> (stok: <?= $o['stok'] ?>)
                </option>
                <?php endforeach; ?>
                </select>
            </div>
            <div class="fg" style="margin:0">
                <label>Jumlah</label>
                <input type="number" id="pilihJml" value="1" min="1" oninput="hitungSubtotal()"/>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin-top:10px">
        <div style="font-size:.855rem;color:var(--muted)">Subtotal: <b id="subtotalPreview" style="color:var(--green)">Rp 0</b></div>
        <button type="button" class="btn btn-green btn-sm" onclick="tambahItem()" style="margin-left:auto">＋ Tambah ke Keranjang</button>
        </div>
    </div>

    <!-- Keranjang -->
    <div style="margin-bottom:16px">
        <div style="font-size:.78rem;font-weight:700;color:var(--muted);margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">Keranjang</div>
        <div id="keranjang" style="min-height:60px;border:1.5px dashed var(--border);border-radius:10px;padding:10px">
        <p style="text-align:center;color:var(--muted);font-size:.83rem;padding:14px 0" id="emptyCart">Keranjang kosong — tambahkan obat di atas</p>
        </div>
    </div>

    <!-- Info pembeli & bayar -->
    <div class="form-row">
        <div class="fg">
        <label>Nama Pembeli</label>
        <input type="text" id="namaPembeli" placeholder="Nama pasien / pembeli" required/>
        </div>
        <div class="fg">
        <label>Jumlah Bayar (Rp)</label>
        <input type="number" id="jumlahBayar" placeholder="0" min="0" oninput="hitungKembalian()"/>
        </div>
    </div>
    <div style="display:flex;gap:20px;align-items:center;background:var(--green-l);border-radius:10px;padding:14px 18px;margin-bottom:16px">
        <div>
        <div style="font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase">Total</div>
        <div style="font-size:1.3rem;font-weight:800;color:var(--green)" id="totalDisplay">Rp 0</div>
        </div>
        <div style="margin-left:auto;text-align:right">
        <div style="font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase">Kembalian</div>
        <div style="font-size:1.1rem;font-weight:800;color:var(--blue)" id="kembalianDisplay">Rp 0</div>
        </div>
    </div>
    <div class="fg">
        <label>Catatan (opsional)</label>
        <input type="text" id="catatanTrx" placeholder="Resep dokter, dll…"/>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn-outline" onclick="closeModal('m-trx')">Batal</button>
        <button type="button" class="btn btn-green" onclick="simpanTrx()"> Simpan Transaksi</button>
    </div>
</div>
</div>

<!-- Modal Detail -->
<div class="overlay" id="m-detail">
<div class="modal" style="width:min(520px,100%)">
    <div class="modal-hd">
        <div class="modal-title" id="detail-kode">Detail Transaksi</div>
        <button class="modal-x" onclick="closeModal('m-detail')">✕</button>
    </div>
    <div id="detail-info" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px"></div>
    <div class="table-wrap">
        <table id="detail-table">
        <thead><tr><th>Nama Obat</th><th>Harga</th><th>Jml</th><th>Subtotal</th></tr></thead>
        <tbody id="detail-tbody"></tbody>
        </table>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:20px;margin-top:16px;padding-top:14px;border-top:1px solid var(--border)">
        <div style="text-align:right">
        <div style="font-size:.75rem;color:var(--muted);font-weight:700">TOTAL</div>
        <div style="font-size:1.2rem;font-weight:800;color:var(--green)" id="detail-total"></div>
        </div>
    </div>
</div>
</div>

<div class="confirm-overlay" id="c-trx">
    <div class="confirm-box">
        <h3>Hapus Transaksi?</h3>
        <p id="c-trx-text">Transaksi ini akan dihapus permanen.</p>
        <div class="confirm-actions">
        <button class="btn btn-outline" onclick="closeConfirm()">Batal</button>
        <form action="transaksi_handler.php" method="POST" style="display:inline">
            <input type="hidden" name="action" value="hapus"/>
            <input type="hidden" name="id" id="c-trx-id"/>
            <button type="submit" class="btn btn-red">Ya, Hapus</button>
        </form>
        </div>
    </div>
</div>

<script>
let cart = [];
let totalBayar = 0;

function fmt(n){ return 'Rp '+parseInt(n).toLocaleString('id-ID'); }

function setHarga(){
    hitungSubtotal();
}
function hitungSubtotal(){
    const sel = document.getElementById('pilihObat');
    const opt = sel.options[sel.selectedIndex];
    const harga = parseFloat(opt.dataset.harga||0);
    const jml = parseInt(document.getElementById('pilihJml').value)||1;
    document.getElementById('subtotalPreview').textContent = fmt(harga*jml);
}

function tambahItem(){
    const sel   = document.getElementById('pilihObat');
    const jml   = parseInt(document.getElementById('pilihJml').value)||1;
    if(!sel.value){ showToast('Pilih obat terlebih dahulu','error'); return; }
    const opt   = sel.options[sel.selectedIndex];
    const stok  = parseInt(opt.dataset.stok);
    const existing = cart.find(c=>c.id==sel.value);
    const sudahDi  = existing?existing.jumlah:0;
    if(sudahDi+jml>stok){ showToast('Stok tidak mencukupi (tersisa '+stok+')','error'); return; }
    if(existing){ existing.jumlah+=jml; existing.subtotal=existing.harga*existing.jumlah; }
    else{
        cart.push({id:sel.value,nama:opt.text.split('(')[0].trim(),harga:parseFloat(opt.dataset.harga),satuan:opt.dataset.satuan,jumlah:jml,subtotal:parseFloat(opt.dataset.harga)*jml});
    }
    renderCart();
}

function renderCart(){
    const wrap = document.getElementById('keranjang');
    const empty= document.getElementById('emptyCart');
    if(cart.length===0){
        if(!empty){ wrap.innerHTML='<p style="text-align:center;color:var(--muted);font-size:.83rem;padding:14px 0" id="emptyCart">Keranjang kosong</p>'; }
        totalBayar=0;
    } else {
        let html='<table style="width:100%;font-size:.83rem;border-collapse:collapse">';
        html+='<thead><tr style="border-bottom:1px solid var(--border)"><th style="padding:6px 8px;text-align:left;font-size:.7rem;color:var(--muted)">Obat</th><th style="padding:6px 8px;text-align:right">Harga</th><th style="padding:6px 8px;text-align:center">Jml</th><th style="padding:6px 8px;text-align:right">Subtotal</th><th></th></tr></thead><tbody>';
        totalBayar=0;
        cart.forEach((c,i)=>{
        totalBayar+=c.subtotal;
        html+=`<tr style="border-bottom:1px solid var(--border)">
            <td style="padding:7px 8px;font-weight:600">${c.nama}</td>
            <td style="padding:7px 8px;text-align:right;color:var(--muted)">${fmt(c.harga)}</td>
            <td style="padding:7px 8px;text-align:center"><span class="badge b-blue">${c.jumlah} ${c.satuan}</span></td>
            <td style="padding:7px 8px;text-align:right;font-weight:700;color:var(--green)">${fmt(c.subtotal)}</td>
            <td style="padding:7px 8px"><button type="button" onclick="hapusItem(${i})" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:1rem">✕</button></td>
        </tr>`;
        });
        html+='</tbody></table>';
        wrap.innerHTML=html;
    }
    document.getElementById('totalDisplay').textContent=fmt(totalBayar);
    hitungKembalian();
}

function hapusItem(i){ cart.splice(i,1); renderCart(); }

function hitungKembalian(){
    const bayar=parseFloat(document.getElementById('jumlahBayar').value)||0;
    const kmb=bayar-totalBayar;
    document.getElementById('kembalianDisplay').textContent=fmt(Math.max(0,kmb));
    document.getElementById('kembalianDisplay').style.color=kmb<0?'var(--red)':'var(--blue)';
}

function simpanTrx(){
    if(cart.length===0){ showToast('Keranjang masih kosong','error'); return; }
    const nama=document.getElementById('namaPembeli').value.trim();
    if(!nama){ showToast('Nama pembeli wajib diisi','error'); return; }
    const bayar=parseFloat(document.getElementById('jumlahBayar').value)||0;
    if(bayar<totalBayar){ showToast('Jumlah bayar kurang dari total','error'); return; }
    const catatan=document.getElementById('catatanTrx').value.trim();

    const form=document.createElement('form');
    form.method='POST'; form.action='transaksi_handler.php';
    const add=(n,v)=>{ const i=document.createElement('input');i.type='hidden';i.name=n;i.value=v;form.appendChild(i); };
    add('action','tambah');
    add('nama_pembeli',nama);
    add('total_harga',totalBayar);
    add('bayar',bayar);
    add('kembalian',Math.max(0,bayar-totalBayar));
    add('catatan',catatan);
    add('cart',JSON.stringify(cart));
    document.body.appendChild(form);
    form.submit();
}

function lihatDetail(id){
    fetch('transaksi_handler.php?action=detail&id='+id)
        .then(r=>r.json())
        .then(data=>{
        document.getElementById('detail-kode').textContent='🧾 '+data.kode_transaksi;
        document.getElementById('detail-info').innerHTML=`
            <div style="background:var(--bg);padding:12px;border-radius:8px">
            <div style="font-size:.7rem;color:var(--muted);font-weight:700">PEMBELI</div>
            <div style="font-weight:700">${data.nama_pembeli}</div>
            </div>
            <div style="background:var(--bg);padding:12px;border-radius:8px">
            <div style="font-size:.7rem;color:var(--muted);font-weight:700">KASIR</div>
            <div style="font-weight:700">${data.kasir||'—'}</div>
            </div>
            <div style="background:var(--bg);padding:12px;border-radius:8px">
            <div style="font-size:.7rem;color:var(--muted);font-weight:700">BAYAR</div>
            <div style="font-weight:700">${fmt(data.bayar)}</div>
            </div>
            <div style="background:var(--bg);padding:12px;border-radius:8px">
            <div style="font-size:.7rem;color:var(--muted);font-weight:700">KEMBALIAN</div>
            <div style="font-weight:700;color:var(--blue)">${fmt(data.kembalian)}</div>
            </div>`;
        let rows='';
        data.detail.forEach(d=>{
            rows+=`<tr><td style="padding:10px 12px;font-weight:600">${d.nama_obat}</td><td style="padding:10px 12px">${fmt(d.harga_satuan)}</td><td style="padding:10px 12px;text-align:center">${d.jumlah}</td><td style="padding:10px 12px;font-weight:700;color:var(--green)">${fmt(d.subtotal)}</td></tr>`;
        });
        document.getElementById('detail-tbody').innerHTML=rows;
        document.getElementById('detail-total').textContent=fmt(data.total_harga);
        openModal('m-detail');
    });
}

function openDelTrx(id,kode){
    document.getElementById('c-trx-id').value=id;
    document.getElementById('c-trx-text').textContent=`Transaksi "${kode}" akan dihapus permanen.`;
    document.getElementById('c-trx').classList.add('open');
}
</script>

<?php require_once '../includes/layout_end.php'; ?>