<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../config/session.php';
requireLogin();

$action = $_POST['action'] ?? '';

/* =====================================================
   CRUD KATEGORI OBAT
   ===================================================== */
if ($action === 'tambah_kategori') {
    $nama = trim($_POST['nama'] ?? '');
    if (!$nama) {
        header('Location: obat.php?error=Nama+kategori+wajib+diisi'); exit;
    }

    $cek = $pdo->prepare("SELECT id FROM kategori_obat WHERE LOWER(nama) = LOWER(?)");
    $cek->execute([$nama]);
    if ($cek->fetch()) {
        header('Location: obat.php?error=Nama+kategori+sudah+ada'); exit;
    }

    $pdo->prepare("INSERT INTO kategori_obat (nama) VALUES (?)")->execute([$nama]);
    header('Location: obat.php?success=Kategori+berhasil+ditambahkan'); exit;
}

if ($action === 'edit_kategori') {
    $id   = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    if (!$id || !$nama) {
        header('Location: obat.php?error=Data+kategori+tidak+lengkap'); exit;
    }

    $cek = $pdo->prepare("SELECT id FROM kategori_obat WHERE LOWER(nama) = LOWER(?) AND id != ?");
    $cek->execute([$nama, $id]);
    if ($cek->fetch()) {
        header('Location: obat.php?error=Nama+kategori+sudah+digunakan'); exit;
    }

    $pdo->prepare("UPDATE kategori_obat SET nama = ? WHERE id = ?")->execute([$nama, $id]);
    header('Location: obat.php?success=Kategori+berhasil+diperbarui'); exit;
}

if ($action === 'hapus_kategori') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header('Location: obat.php?error=ID+kategori+tidak+valid'); exit;
    }

    // Relasi obat.kategori_id menggunakan ON DELETE SET NULL, sehingga obat terkait otomatis menjadi tanpa kategori
    $pdo->prepare("DELETE FROM kategori_obat WHERE id = ?")->execute([$id]);
    header('Location: obat.php?success=Kategori+berhasil+dihapus'); exit;
}

/* =====================================================
   CRUD OBAT
   ===================================================== */
if ($action === 'tambah') {
    $nama    = trim($_POST['nama'] ?? '');
    $kat     = ($_POST['kategori_id'] ?? '') !== '' ? (int)$_POST['kategori_id'] : null;
    $satuan  = trim($_POST['satuan'] ?? 'tablet');
    $hbeli   = (float)($_POST['harga_beli'] ?? 0);
    $hjual   = (float)($_POST['harga_jual'] ?? 0);
    $stok    = (int)($_POST['stok'] ?? 0);
    $stokmin = (int)($_POST['stok_minimum'] ?? 10);
    $exp     = !empty($_POST['tanggal_expired']) ? $_POST['tanggal_expired'] : null;
    $desk    = trim($_POST['deskripsi'] ?? '');

    if (!$nama) {
        header('Location: obat.php?error=Nama+obat+wajib+diisi'); exit;
    }
    if ($hbeli < 0 || $hjual < 0 || $stok < 0 || $stokmin < 0) {
        header('Location: obat.php?error=Harga+dan+stok+tidak+boleh+negatif'); exit;
    }

    $maxNum = (int)$pdo->query("SELECT MAX(CAST(SUBSTRING(kode_obat, 5) AS UNSIGNED)) FROM obat WHERE kode_obat LIKE 'OBT-%'")->fetchColumn();
    $next   = $maxNum + 1;
    $kode   = 'OBT-' . str_pad($next, 3, '0', STR_PAD_LEFT);

    $pdo->prepare("INSERT INTO obat (kode_obat, nama, kategori_id, satuan, harga_beli, harga_jual, stok, stok_minimum, tanggal_expired, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$kode, $nama, $kat, $satuan, $hbeli, $hjual, $stok, $stokmin, $exp, $desk]);

    header('Location: obat.php?success=Obat+berhasil+ditambahkan+dengan+kode+' . $kode); exit;
}

if ($action === 'edit') {
    $id      = (int)($_POST['id'] ?? 0);
    $kode    = trim($_POST['kode_obat'] ?? '');
    $nama    = trim($_POST['nama'] ?? '');
    $kat     = ($_POST['kategori_id'] ?? '') !== '' ? (int)$_POST['kategori_id'] : null;
    $satuan  = trim($_POST['satuan'] ?? 'tablet');
    $hbeli   = (float)($_POST['harga_beli'] ?? 0);
    $hjual   = (float)($_POST['harga_jual'] ?? 0);
    $stok    = (int)($_POST['stok'] ?? 0);
    $stokmin = (int)($_POST['stok_minimum'] ?? 10);
    $exp     = !empty($_POST['tanggal_expired']) ? $_POST['tanggal_expired'] : null;
    $desk    = trim($_POST['deskripsi'] ?? '');

    if (!$id || !$kode || !$nama) {
        header('Location: obat.php?error=Data+tidak+lengkap'); exit;
    }
    if ($hbeli < 0 || $hjual < 0 || $stok < 0 || $stokmin < 0) {
        header('Location: obat.php?error=Harga+dan+stok+tidak+boleh+negatif'); exit;
    }

    $cek = $pdo->prepare("SELECT id FROM obat WHERE kode_obat = ? AND id != ?");
    $cek->execute([$kode, $id]);
    if ($cek->fetch()) {
        header('Location: obat.php?error=Kode+sudah+dipakai+obat+lain'); exit;
    }

    $pdo->prepare("UPDATE obat SET kode_obat = ?, nama = ?, kategori_id = ?, satuan = ?, harga_beli = ?, harga_jual = ?, stok = ?, stok_minimum = ?, tanggal_expired = ?, deskripsi = ? WHERE id = ?")
        ->execute([$kode, $nama, $kat, $satuan, $hbeli, $hjual, $stok, $stokmin, $exp, $desk, $id]);

    header('Location: obat.php?success=Obat+berhasil+diperbarui'); exit;
}

if ($action === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);

    $cekTrx = $pdo->prepare("SELECT COUNT(*) FROM detail_transaksi WHERE obat_id = ?");
    $cekTrx->execute([$id]);
    if ($cekTrx->fetchColumn() > 0) {
        header('Location: obat.php?error=Obat+tidak+bisa+dihapus,+sudah+ada+dalam+transaksi'); exit;
    }

    $cekPsn = $pdo->prepare("SELECT COUNT(*) FROM detail_pesanan WHERE obat_id = ?");
    $cekPsn->execute([$id]);
    if ($cekPsn->fetchColumn() > 0) {
        header('Location: obat.php?error=Obat+tidak+bisa+dihapus,+sudah+ada+dalam+pesanan+online'); exit;
    }

    $pdo->prepare("DELETE FROM obat WHERE id = ?")->execute([$id]);
    header('Location: obat.php?success=Obat+berhasil+dihapus'); exit;
}

header('Location: obat.php'); exit;