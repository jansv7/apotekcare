<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function requireLogin(): void {
    if (empty($_SESSION['user_id']) || $_SESSION['user_role'] === 'customer') {
        header('Location: /UAS%20Project/apotek/index.php?error=Akses+ditolak');
        exit;
    }
}
function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }
function currentUser(): array {
    return [
        'id'    => $_SESSION['user_id']    ?? null,
        'nama'  => $_SESSION['user_nama']  ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role']  ?? 'apoteker',
    ];
}