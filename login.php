<?php

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require 'database.php';


if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') { header("Location: admin_panel.php"); exit; }
    elseif ($_SESSION['role'] === 'firma') { header("Location: firma_panel.php"); exit; }
    elseif ($_SESSION['role'] === 'yolcu') { header("Location: my_tickets.php"); exit; }
}

$error = '';
$info_message = '';


if (isset($_GET['redirect_message']) && $_GET['redirect_message'] === 'login_required') {
    $info_message = "İşlem yapabilmek için lütfen önce sisteme giriş yapın.";
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    
    if ($user && password_verify($_POST['password'], $user['password'])) {
        
        
        session_regenerate_id(true);
        
        
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['username'] = $user['username']; 
        $_SESSION['role'] = $user['role'];
        
        
        if ($user['role'] === 'admin') { header("Location: admin_panel.php"); }
        elseif ($user['role'] === 'firma') { $_SESSION['firma_id'] = $user['firma_id']; header("Location: firma_panel.php"); }
        elseif ($user['role'] === 'yolcu') { header("Location: my_tickets.php"); }
        else { header("Location: index.php"); }
        exit; 
        
    } else { 
        $error = "Geçersiz kullanıcı adı veya parola!"; 
    }
}


?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page-body">
    <div class="container" style="max-width: 600px; margin: 0;">
        
        <h1 class="brand-title">İVEDİ<span class="orange-text">BİLET</span></h1>
        <h2 style="text-align:center; border:none;">Sisteme Giriş</h2>
        
        <?php if (!empty($info_message)): ?>
            <div class="alert alert-info"><?= $info_message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Kullanıcı Adı:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Parola:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Giriş Yap</button>
        </form>

        <p style="text-align: center; margin-top: 20px;">Hesabınız yok mu? <a href="register.php">Hemen Kayıt Olun</a></p>
    </div>
</body>
</html>