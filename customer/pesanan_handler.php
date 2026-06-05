<?php
require_once 'guard.php';
requireCustomer();
$cust = customerData();
require_once '../config/db.php';

$action = $_POST['action'] ?? '';

if ($action === 'buat') {
    $nama    = trim($_POST['nama_penerima'] ?? '');
    $metode  = $_POST['metode_bayar'] === 'transfer' ? 'transfer' : 'cash';
    $total   = (float)($_POST['total_harga'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');
    $cart    = json_decode($_POST['cart'] ?? '[]', true);

    if (!$nama || empty($cart)) {
        header('Location: katalog.php?error=Data+tidak+lengkap'); exit;
    }

    // Generate kode pesanan
    $kode = 'PSN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 5));

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO pesanan (kode_pesanan,user_id,metode_bayar,status,total_harga,catatan) VALUES(?,?,?,'menunggu',?,?)")
            ->execute([$kode, $cust['id'], $metode, $total, $catatan]);
        $pesId = $pdo->lastInsertId();

        foreach ($cart as $item) {
            $obatId  = (int)$item['id'];
            $jumlah  = (int)$item['qty'];
            $harga   = (float)$item['harga'];
            $namaObat= trim($item['nama']);
            $subtot  = $harga * $jumlah;

            // Cek & kurangi stok
            $st = $pdo->prepare("SELECT stok FROM obat WHERE id=? FOR UPDATE");
            $st->execute([$obatId]);
            $stok = $st->fetchColumn();
            if ($stok === false || $stok < $jumlah) {
                $pdo->rollBack();
                header('Location: katalog.php?error=Stok+'.urlencode($namaObat).'+tidak+mencukupi'); exit;
            }
            $pdo->prepare("UPDATE obat SET stok=stok-? WHERE id=?")->execute([$jumlah, $obatId]);
            $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id,obat_id,nama_obat,harga_satuan,jumlah,subtotal) VALUES(?,?,?,?,?,?)")
                ->execute([$pesId, $obatId, $namaObat, $harga, $jumlah, $subtot]);
        }
        $pdo->commit();
        header("Location: katalog.php?kode=$kode"); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: katalog.php?error=Terjadi+kesalahan'); exit;
    }
}

if ($action === 'batal') {
    $id = (int)($_POST['id'] ?? 0);

    $p = $pdo->prepare("SELECT * FROM pesanan WHERE id=? AND user_id=? AND status='menunggu'");
    $p->execute([$id, $cust['id']]);
    $pes = $p->fetch();

    if (!$pes) {
        header('Location: pesanan.php?error=Pesanan+tidak+ditemukan+atau+tidak+bisa+dibatalkan'); exit;
    }

    $pdo->beginTransaction();
    try {
        $det = $pdo->prepare("SELECT obat_id,jumlah FROM detail_pesanan WHERE pesanan_id=?");
        $det->execute([$id]);
        foreach ($det->fetchAll() as $d) {
            $pdo->prepare("UPDATE obat SET stok=stok+? WHERE id=?")->execute([$d['jumlah'], $d['obat_id']]);
        }
        $pdo->prepare("UPDATE pesanan SET status='dibatalkan' WHERE id=?")->execute([$id]);
        $pdo->commit();
        header('Location: pesanan.php?success=Pesanan+berhasil+dibatalkan'); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: pesanan.php?error=Gagal+membatalkan+pesanan'); exit;
    }
}

header('Location: ../customer/katalog.php'); exit;