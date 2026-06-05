<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function requireCustomer(): void {
    if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
        header('Location: login.php');
        exit;
    }
}

function customerData(): array {
    return [
        'id'    => $_SESSION['user_id']    ?? null,
        'nama'  => $_SESSION['user_nama']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'hp'    => $_SESSION['user_hp']    ?? '',
    ];
}