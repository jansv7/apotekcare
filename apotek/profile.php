<?php
$pageTitle='Profil Saya'; $activePage='profile';
require_once '../config/db.php';
require_once '../includes/layout.php';
$user=currentUser();
$dbU=$pdo->prepare("SELECT * FROM users WHERE id=?");$dbU->execute([$user['id']]);$dbU=$dbU->fetch();
?>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start">
    <div class="card" style="text-align:center">
        <div style="width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,var(--green),#34d399);display:grid;place-items:center;font-size:1.7rem;font-weight:800;color:#fff;margin:0 auto 14px">
        <?= strtoupper(substr($dbU['nama'],0,1)) ?>
        </div>
        <div style="font-weight:800;font-size:1.05rem;margin-bottom:4px"><?= htmlspecialchars($dbU['nama']) ?></div>
        <div style="color:var(--muted);font-size:.83rem;margin-bottom:12px"><?= htmlspecialchars($dbU['email']) ?></div>
        <span class="badge <?= $dbU['role']==='admin'?'b-green':'b-blue' ?>"><?= $dbU['role'] ?></span>
        <div style="margin-top:14px;font-size:.75rem;color:var(--muted)">Bergabung: <?= date('d M Y',strtotime($dbU['created_at'])) ?></div>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px">
        <div class="card">
        <div class="card-title">👤 Edit Profil</div>
        <form action="profile_handler.php" method="POST">
            <input type="hidden" name="action" value="update"/>
            <div class="form-row">
            <div class="fg"><label>Nama Lengkap</label><input type="text" name="nama" value="<?= htmlspecialchars($dbU['nama']) ?>" required/></div>
            <div class="fg"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($dbU['email']) ?>" required/></div>
            </div>
            <button type="submit" class="btn btn-green"> Simpan Perubahan</button>
        </form>
        </div>
        <div class="card">
        <div class="card-title">🔒 Ganti Password</div>
        <form action="profile_handler.php" method="POST">
            <input type="hidden" name="action" value="password"/>
            <div class="fg"><label>Password Lama</label><input type="password" name="lama" required/></div>
            <div class="form-row">
            <div class="fg"><label>Password Baru</label><input type="password" name="baru" required/></div>
            <div class="fg"><label>Konfirmasi</label><input type="password" name="konfirm" required/></div>
            </div>
            <button type="submit" class="btn btn-green"> Update Password</button>
        </form>
        </div>
    </div>
</div>

<?php require_once '../includes/layout_end.php'; ?>