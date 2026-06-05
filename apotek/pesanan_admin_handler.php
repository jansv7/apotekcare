<?php
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

if (($_GET['action'] ?? '') === 'detail') {
    requireLogin();
    $id  = (int)($_GET['id'] ?? 0);
    $pes = $pdo->prepare("SELECT p.*,u.nama as nama_customer,u.no_hp FROM pesanan p LEFT JOIN users u ON p.user_id=u.id WHERE p.id=?");
    $pes->execute([$id]); $pes = $pes->fetch();
    $det = $pdo->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id=?");
    $det->execute([$id]);
    $pes['detail'] = $det->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($pes); exit;
}

requireLogin();
$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $id     = (int)($_POST['id']     ?? 0);
    $status = $_POST['status'] ?? '';
    $valid  = ['menunggu','diproses','siap_diambil','selesai','dibatalkan'];

    if (!$id || !in_array($status, $valid)) {
        header('Location: pesanan_admin.php?error=Data+tidak+valid'); exit;
    }

    $curStatusStmt = $pdo->prepare("SELECT status FROM pesanan WHERE id=?");
    $curStatusStmt->execute([$id]);
    $cur = $curStatusStmt->fetchColumn();

    $pdo->beginTransaction();
    try {
        if ($status === 'dibatalkan' && $cur !== 'dibatalkan') {
            $det = $pdo->prepare("SELECT obat_id,jumlah FROM detail_pesanan WHERE pesanan_id=?");
            $det->execute([$id]);
            foreach ($det->fetchAll() as $d) {
                $pdo->prepare("UPDATE obat SET stok=stok+? WHERE id=?")->execute([$d['jumlah'], $d['obat_id']]);
            }
        }

        if ($status === 'selesai' && $cur !== 'selesai') {
            $pes = $pdo->prepare("SELECT p.*, u.nama as nama_customer FROM pesanan p LEFT JOIN users u ON p.user_id=u.id WHERE p.id=?");
            $pes->execute([$id]);
            $dtPes = $pes->fetch();

            $kodeTrx = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 5));
            $catatan = "Pesanan Online: " . $dtPes['kode_pesanan']; 
            if (!empty($dtPes['catatan'])) {
                $catatan .= " (" . $dtPes['catatan'] . ")";
            }

            $pdo->prepare("INSERT INTO transaksi (kode_transaksi, nama_pembeli, total_harga, bayar, kembalian, catatan, user_id) VALUES (?,?,?,?,?,?,?)")
                ->execute([$kodeTrx, $dtPes['nama_customer'], $dtPes['total_harga'], $dtPes['total_harga'], 0, $catatan, $_SESSION['user_id']]);
            
            $trxId = $pdo->lastInsertId();

            $det = $pdo->prepare("SELECT * FROM detail_pesanan WHERE pesanan_id=?");
            $det->execute([$id]);
            
            $stmtDet = $pdo->prepare("INSERT INTO detail_transaksi (transaksi_id, obat_id, nama_obat, harga_satuan, jumlah, subtotal) VALUES (?,?,?,?,?,?)");
            foreach ($det->fetchAll() as $d) {
                $stmtDet->execute([$trxId, $d['obat_id'], $d['nama_obat'], $d['harga_satuan'], $d['jumlah'], $d['subtotal']]);
            }
        }

        $pdo->prepare("UPDATE pesanan SET status=? WHERE id=?")->execute([$status, $id]);

        $pdo->commit(); 
        header('Location: pesanan_admin.php?success=Status+pesanan+berhasil+diperbarui'); exit;

    } catch (Exception $e) {
        $pdo->rollBack(); 
        header('Location: pesanan_admin.php?error=Terjadi+kesalahan:+'.urlencode($e->getMessage())); exit;
    }
}

header('Location: ../apotek/pesanan_admin.php'); exit;