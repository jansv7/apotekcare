<?php
session_start();
require_once '../config/db.php';
require_once '../config/session.php';
requireLogin();
$user=currentUser();
$action=$_POST['action']??'';

if($action==='update'){
    $nama=trim($_POST['nama']??'');$email=trim($_POST['email']??'');
    if(!$nama||!$email){header('Location: profile.php?error=Field+wajib+diisi');exit;}
    $cek=$pdo->prepare("SELECT id FROM users WHERE email=? AND id!=?");$cek->execute([$email,$user['id']]);
    if($cek->fetch()){header('Location: profile.php?error=Email+sudah+digunakan');exit;}
    $pdo->prepare("UPDATE users SET nama=?,email=? WHERE id=?")->execute([$nama,$email,$user['id']]);
    $_SESSION['user_nama']=$nama;$_SESSION['user_email']=$email;
    header('Location: profile.php?success=Profil+berhasil+diperbarui');exit;
}

if($action==='password'){
    $lama=$_POST['lama']??'';$baru=$_POST['baru']??'';$konfirm=$_POST['konfirm']??'';
    $dbU=$pdo->prepare("SELECT password FROM users WHERE id=?");$dbU->execute([$user['id']]);$dbU=$dbU->fetch();
    if(!lama !== $dbU['password']){header('Location: profile.php?error=Password+lama+salah');exit;}
    if(strlen($baru)<6){header('Location: profile.php?error=Password+baru+minimal+6+karakter');exit;}
    if($baru!==$konfirm){header('Location: profile.php?error=Konfirmasi+password+tidak+cocok');exit;}
    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$baru,$user['id']]);
    header('Location: profile.php?success=Password+berhasil+diubah');exit;
}
header('Location: ../apotek/profile.php');exit;