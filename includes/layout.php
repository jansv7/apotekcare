<?php
require_once __DIR__ . '/../config/session.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — ApotekCare</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Lora:ital@1&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../includes/style.css">
<link rel="icon" href="../foto/Logo.png">

</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sb-logo">
        <div class="cross">
        <img src="../foto/Logo.png" alt="Logo" style="width:40px;height:40px;object-fit:contain;">
        </div>
        <div class="brand">Apotek<span>Care</span></div>
    </div>

    <div class="nav-label">Menu</div>
    <a href="dashboard.php"  class="nav-item <?= ($activePage??'')==='dashboard' ?'active':'' ?>"> Dashboard</a>
    <a href="obat.php"       class="nav-item <?= ($activePage??'')==='obat'      ?'active':'' ?>"> Data Obat</a>
    <a href="transaksi.php"  class="nav-item <?= ($activePage??'')==='transaksi' ?'active':'' ?>"> Transaksi</a>
    <a href="pesanan_admin.php"  class="nav-item <?= ($activePage??'')==='pesanan' ?'active':'' ?>"> Kelola Pesanan</a>

    <div class="nav-label">Akun</div>
    <a href="profile.php"    class="nav-item <?= ($activePage??'')==='profile'   ?'active':'' ?>"> Profil Saya</a>

    <div class="sb-footer">
        <div class="user-pill">
        <div class="avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div>
        <div>
            <div class="uname"><?= htmlspecialchars($user['nama']) ?></div>
            <div class="urole"><?= htmlspecialchars($user['role']) ?></div>
        </div>
        </div>
        <form action="../auth/handler.php" method="POST">
        <input type="hidden" name="action" value="logout"/>
        <button class="logout-btn">⎋ Keluar</button>
        </form>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px">
        <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        <span class="topbar-title"><?= htmlspecialchars($pageTitle??'Dashboard') ?></span>
        </div>
        <div class="topbar-right">
        <span class="topbar-chip"><?= htmlspecialchars($user['role']) ?></span>
        <span style="font-size:.82rem;color:var(--muted);font-weight:600"><?= htmlspecialchars($user['nama']) ?></span>
        </div>
    </div>
    <div class="page">