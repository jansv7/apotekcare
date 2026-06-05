<?php
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

if(($_GET['action']??'')==='detail'){
    requireLogin();
    $id=(int)($_GET['id']??0);
    $trx=$pdo->prepare("SELECT t.*,u.nama as kasir FROM transaksi t LEFT JOIN users u ON t.user_id=u.id WHERE t.id=?");
    $trx->execute([$id]); $trx=$trx->fetch();
    $det=$pdo->prepare("SELECT * FROM detail_transaksi WHERE transaksi_id=?");
    $det->execute([$id]);
    $trx['detail']=$det->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($trx); exit;
}

requireLogin();
$user=currentUser();
$action=$_POST['action']??'';

if($action==='tambah'){
    $nama   =trim($_POST['nama_pembeli']??'');
    $total  =(float)($_POST['total_harga']??0);
    $bayar  =(float)($_POST['bayar']??0);
    $kmb    =(float)($_POST['kembalian']??0);
    $catatan=trim($_POST['catatan']??'');
    $cart   =json_decode($_POST['cart']??'[]',true);

    if(!$nama||empty($cart)){header('Location: transaksi.php?error=Data+tidak+lengkap');exit;}
    if($bayar<$total){header('Location: transaksi.php?error=Jumlah+bayar+kurang');exit;}

    $kode='TRX-'.date('Ymd').'-'.strtoupper(substr(uniqid(),7,5));

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO transaksi (kode_transaksi,nama_pembeli,total_harga,bayar,kembalian,catatan,user_id) VALUES(?,?,?,?,?,?,?)")
            ->execute([$kode,$nama,$total,$bayar,$kmb,$catatan,$user['id']]);
        $trxId=$pdo->lastInsertId();

        foreach($cart as $item){
        $obatId =(int)$item['id'];
        $jumlah =(int)$item['jumlah'];
        $harga  =(float)$item['harga'];
        $subtot =(float)$item['subtotal'];
        $namaObat=trim($item['nama']);

        $stok=$pdo->prepare("SELECT stok FROM obat WHERE id=? FOR UPDATE");
        $stok->execute([$obatId]);
        $st=$stok->fetch();
        if(!$st||$st['stok']<$jumlah){
            $pdo->rollBack();
            header('Location: transaksi.php?error=Stok+'.urlencode($namaObat).'+tidak+mencukupi');exit;
        }
        $pdo->prepare("UPDATE obat SET stok=stok-? WHERE id=?")->execute([$jumlah,$obatId]);
        $pdo->prepare("INSERT INTO detail_transaksi (transaksi_id,obat_id,nama_obat,harga_satuan,jumlah,subtotal) VALUES(?,?,?,?,?,?)")
            ->execute([$trxId,$obatId,$namaObat,$harga,$jumlah,$subtot]);
        }
        $pdo->commit();
        header('Location: transaksi.php?success=Transaksi+'.urlencode($kode).'+berhasil+disimpan');exit;
    } catch(Exception $e){
        $pdo->rollBack();
        header('Location: transaksi.php?error=Terjadi+kesalahan:+'.urlencode($e->getMessage()));exit;
    }
}

if($action==='hapus'){
    $id=(int)($_POST['id']??0);
    $details=$pdo->prepare("SELECT obat_id,jumlah FROM detail_transaksi WHERE transaksi_id=?");
    $details->execute([$id]);
    $pdo->beginTransaction();
    try{
        foreach($details->fetchAll() as $d){
        $pdo->prepare("UPDATE obat SET stok=stok+? WHERE id=?")->execute([$d['jumlah'],$d['obat_id']]);
        }
        $pdo->prepare("DELETE FROM transaksi WHERE id=?")->execute([$id]);
        $pdo->commit();
        header('Location: transaksi.php?success=Transaksi+berhasil+dihapus,+stok+dikembalikan');exit;
    } catch(Exception $e){
        $pdo->rollBack();
        header('Location: transaksi.php?error=Gagal+hapus:+'.urlencode($e->getMessage()));exit;
    }
}

header('Location: ../apotek/transaksi.php');exit;