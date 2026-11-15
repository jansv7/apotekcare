<?php
require_once 'guard.php';
requireCustomer();
$cust = customerData();
require_once '../config/db.php';

$action = $_POST['action'] ?? '';

function prosesUploadBukti(array $file): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Gagal mengunggah file bukti transfer'];
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        return [false, 'Ukuran file maksimal 2MB'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        return [false, 'Format file harus JPG, PNG, atau WEBP'];
    }

    $uploadDir = __DIR__ . '/../uploads/bukti_transfer/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'bukti_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return [false, 'Gagal menyimpan file di server'];
    }

    return [true, $filename];
}

if ($action === 'buat') {
    $nama    = trim($_POST['nama_penerima'] ?? '');
    $metode  = ($_POST['metode_bayar'] ?? '') === 'transfer' ? 'transfer' : 'cash';
    $total   = (float)($_POST['total_harga'] ?? 0);
    $catatan = trim($_POST['catatan'] ?? '');
    $cart    = json_decode($_POST['cart'] ?? '[]', true);

    if (!$nama || empty($cart)) {
        header('Location: katalog.php?error=Data+tidak+lengkap'); exit;
    }

    $buktiFile = null;
    if ($metode === 'transfer' && !empty($_FILES['bukti_transfer']['name'])) {
        [$ok, $res] = prosesUploadBukti($_FILES['bukti_transfer']);
        if (!$ok) {
            header('Location: katalog.php?error=' . urlencode($res)); exit;
        }
        $buktiFile = $res;
    }

    $kode = 'PSN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), 7, 5));

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO pesanan (kode_pesanan, user_id, metode_bayar, status, total_harga, catatan, bukti_transfer) VALUES (?, ?, ?, 'menunggu', ?, ?, ?)")
            ->execute([$kode, $cust['id'], $metode, $total, $catatan, $buktiFile]);
        $pesId = $pdo->lastInsertId();

        foreach ($cart as $item) {
            $obatId   = (int)$item['id'];
            $jumlah   = (int)$item['qty'];
            $harga    = (float)$item['harga'];
            $namaObat = trim($item['nama']);
            $subtot   = $harga * $jumlah;

            $st = $pdo->prepare("SELECT stok FROM obat WHERE id = ? FOR UPDATE");
            $st->execute([$obatId]);
            $stok = $st->fetchColumn();
            if ($stok === false || $stok < $jumlah) {
                $pdo->rollBack();
                if ($buktiFile) {
                    @unlink(__DIR__ . '/../uploads/bukti_transfer/' . basename($buktiFile));
                }
                header('Location: katalog.php?error=Stok+' . urlencode($namaObat) . '+tidak+mencukupi'); exit;
            }
            $pdo->prepare("UPDATE obat SET stok = stok - ? WHERE id = ?")->execute([$jumlah, $obatId]);
            $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id, obat_id, nama_obat, harga_satuan, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$pesId, $obatId, $namaObat, $harga, $jumlah, $subtot]);
        }
        $pdo->commit();
        header("Location: katalog.php?kode=$kode"); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        if ($buktiFile) {
            @unlink(__DIR__ . '/../uploads/bukti_transfer/' . basename($buktiFile));
        }
        header('Location: katalog.php?error=Terjadi+kesalahan'); exit;
    }
}

if ($action === 'upload_bukti') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id = ? AND user_id = ? AND metode_bayar = 'transfer'");
    $stmt->execute([$id, $cust['id']]);
    $pes = $stmt->fetch();

    if (!$pes) {
        header('Location: pesanan.php?error=Pesanan+transfer+tidak+ditemukan'); exit;
    }
    if ($pes['status'] === 'dibatalkan' || $pes['status'] === 'selesai') {
        header('Location: pesanan.php?error=Status+pesanan+tidak+dapat+diubah'); exit;
    }
    if (empty($_FILES['bukti_transfer']['name'])) {
        header('Location: pesanan.php?error=Pilih+file+bukti+transfer+terlebih+dahulu'); exit;
    }

    [$ok, $res] = prosesUploadBukti($_FILES['bukti_transfer']);
    if (!$ok) {
        header('Location: pesanan.php?error=' . urlencode($res)); exit;
    }

    if (!empty($pes['bukti_transfer'])) {
        @unlink(__DIR__ . '/../uploads/bukti_transfer/' . basename($pes['bukti_transfer']));
    }

    $pdo->prepare("UPDATE pesanan SET bukti_transfer = ? WHERE id = ? AND user_id = ?")
        ->execute([$res, $id, $cust['id']]);

    header('Location: pesanan.php?success=Bukti+transfer+berhasil+diunggah'); exit;
}

if ($action === 'batal') {
    $id = (int)($_POST['id'] ?? 0);

    $p = $pdo->prepare("SELECT * FROM pesanan WHERE id = ? AND user_id = ? AND status = 'menunggu'");
    $p->execute([$id, $cust['id']]);
    $pes = $p->fetch();

    if (!$pes) {
        header('Location: pesanan.php?error=Pesanan+tidak+ditemukan+atau+tidak+bisa+dibatalkan'); exit;
    }

    $pdo->beginTransaction();
    try {
        $det = $pdo->prepare("SELECT obat_id, jumlah FROM detail_pesanan WHERE pesanan_id = ?");
        $det->execute([$id]);
        foreach ($det->fetchAll() as $d) {
            $pdo->prepare("UPDATE obat SET stok = stok + ? WHERE id = ?")->execute([$d['jumlah'], $d['obat_id']]);
        }
        $pdo->prepare("UPDATE pesanan SET status = 'dibatalkan' WHERE id = ?")->execute([$id]);
        $pdo->commit();
        header('Location: pesanan.php?success=Pesanan+berhasil+dibatalkan'); exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header('Location: pesanan.php?error=Gagal+membatalkan+pesanan'); exit;
    }
}

header('Location: ../customer/katalog.php'); exit;