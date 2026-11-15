<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';
require_once '../config/session.php';
requireLogin();

$user   = currentUser();
$action = $_POST['action'] ?? '';

if ($action === 'update') {
    $nama  = trim($_POST['nama']  ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$nama || !$email) {
        header('Location: profile.php?error=Field+wajib+diisi'); exit;
    }

    $cek = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $cek->execute([$email, $user['id']]);
    if ($cek->fetch()) {
        header('Location: profile.php?error=Email+sudah+digunakan'); exit;
    }

    $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?")
        ->execute([$nama, $email, $user['id']]);

    $_SESSION['user_nama']  = $nama;
    $_SESSION['user_email'] = $email;

    header('Location: profile.php?success=Profil+berhasil+diperbarui'); exit;
}

if ($action === 'password') {
    $lama    = trim($_POST['lama']    ?? '');
    $baru    = trim($_POST['baru']    ?? '');
    $konfirm = trim($_POST['konfirm'] ?? '');

    if (!$lama || !$baru || !$konfirm) {
        header('Location: profile.php?error=Semua+field+password+wajib+diisi'); exit;
    }

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $dbU = $stmt->fetch();

    $passwordLamaValid = $dbU && (
        password_verify($lama, $dbU['password']) ||
        $lama === $dbU['password']
    );

    if (!$passwordLamaValid) {
        header('Location: profile.php?error=Password+lama+salah'); exit;
    }
    if (strlen($baru) < 6) {
        header('Location: profile.php?error=Password+baru+minimal+6+karakter'); exit;
    }
    if ($baru !== $konfirm) {
        header('Location: profile.php?error=Konfirmasi+password+tidak+cocok'); exit;
    }

    $hashedBaru = password_hash($baru, PASSWORD_DEFAULT);

    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
        ->execute([$hashedBaru, $user['id']]);

    header('Location: profile.php?success=Password+berhasil+diubah'); exit;
}

header('Location: profile.php'); exit;