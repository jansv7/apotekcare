<?php
session_start();
require_once '../config/db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'logout') {
    session_destroy();
    header('Location: ../apotek/index.php?msg=Berhasil+keluar');
    exit;
}

if ($action === 'login') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $from     = $_POST['from'] ?? 'staff'; 

    $errorUrl = $from === 'customer'
        ? '../customer/login.php'
        : '../apotek/index.php'; 

    if (!$email || !$password) {
        header("Location: $errorUrl?error=Semua+field+wajib+diisi"); exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_nama']  = $user['nama'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_hp']    = $user['no_hp'] ?? '';

        if ($user['role'] === 'customer') {
            header('Location: ../customer/katalog.php'); exit;
        } else {
            header('Location: ../apotek/dashboard.php'); exit; 
        }
    }

    header("Location: $errorUrl?error=Email+atau+password+salah"); exit;
}


if ($action === 'register') {
    $nama    = trim($_POST['nama']     ?? '');
    $email   = trim($_POST['email']    ?? '');
    $password= trim($_POST['password'] ?? '');
    $konfirm = trim($_POST['konfirm']  ?? '');

    if (!$nama || !$email || !$password || !$konfirm) {
        header('Location: ../apotek/index.php?tab=register&error=Semua+field+wajib+diisi'); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../apotek/index.php?tab=register&error=Format+email+tidak+valid'); exit;
    }
    if (strlen($password) < 6) {
        header('Location: ../apotek/index.php?tab=register&error=Password+minimal+6+karakter'); exit;
    }
    if ($password !== $konfirm) {
        header('Location: ../apotek/index.php?tab=register&error=Password+tidak+cocok'); exit;
    }
    $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $cek->execute([$email]);
    if ($cek->fetch()) {
        header('Location: ../apotek/index.php?tab=register&error=Email+sudah+terdaftar'); exit;
    }
    $pdo->prepare("INSERT INTO users (nama, email, password, role) VALUES (?,?,?,'apoteker')")
        ->execute([$nama, $email, $password]);
    header('Location: ../apotek/index.php?msg=Akun+berhasil+dibuat,+silakan+login'); exit;
}

if ($action === 'register_customer') {
    $nama    = trim($_POST['nama']     ?? '');
    $email   = trim($_POST['email']    ?? '');
    $hp      = trim($_POST['no_hp']    ?? '');
    $password= trim($_POST['password'] ?? '');
    $konfirm = trim($_POST['konfirm']  ?? '');

    if (!$nama || !$email || !$hp || !$password || !$konfirm) {
        header('Location: ../customer/login.php?tab=register&error=Semua+field+wajib+diisi'); exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../customer/login.php?tab=register&error=Format+email+tidak+valid'); exit;
    }
    if (strlen($password) < 6) {
        header('Location: ../customer/login.php?tab=register&error=Password+minimal+6+karakter'); exit;
    }
    if ($password !== $konfirm) {
        header('Location: ../customer/login.php?tab=register&error=Password+tidak+cocok'); exit;
    }
    $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $cek->execute([$email]);
    if ($cek->fetch()) {
        header('Location: ../customer/login.php?tab=register&error=Email+sudah+terdaftar'); exit;
    }
    $pdo->prepare("INSERT INTO users (nama, email, no_hp, password, role) VALUES (?,?,?,?,'customer')")
        ->execute([$nama, $email, $hp, $password]);
    header('Location: ../customer/login.php?msg=Akun+berhasil+dibuat,+silakan+login'); exit;
}

if ($action === 'logout_customer') {
    session_destroy();
    header('Location: ../customer/login.php?msg=Berhasil+keluar');
    exit;
}

header('Location: ../index.php'); exit;