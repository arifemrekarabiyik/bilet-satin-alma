<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'database.php'; 

$bakiye_goster = '';
if (isset($_SESSION['role']) && $_SESSION['role'] === 'yolcu' && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT bakiye FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_bakiye = $stmt->fetchColumn();
    $bakiye_goster = ' (Bakiye: ' . number_format($user_bakiye, 2) . ' TL)';
}
?>

<header class="site-header">
    <div class="navbar">
        <div class="navbar-left">
            <h1><a href="index.php" style="text-decoration:none; color: #ff9500ff;">"Seferlerimiz"</a></h1>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'yolcu'): ?>
                <a href="my_tickets.php" class="btn btn-secondary">Aldığım Biletler</a>
            <?php endif; ?>
        </div>

        <div class="navbar-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'firma'): ?>
                    <a href="firma_panel.php">Firma Paneli</a>
                <?php elseif ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin_panel.php">Admin Paneli</a>
                <?php endif; ?>
                <a href="logout.php">Çıkış Yap (<?= htmlspecialchars($_SESSION['username']) . $bakiye_goster ?>)</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-auth">Kayıt Ol</a>
                <a href="login.php" class="btn btn-auth">Giriş Yap</a>
            <?php endif; ?>
        </div>
    </div>
</header>