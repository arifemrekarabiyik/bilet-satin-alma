<?php
require 'database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
$message = '';

if (isset($_GET['delete_firma'])) {
    $firma_id = $_GET['delete_firma'];
    $sefer_sayisi = $pdo->prepare("SELECT COUNT(*) FROM seferler WHERE firma_id = ?");
    $sefer_sayisi->execute([$firma_id]);
    if ($sefer_sayisi->fetchColumn() > 0) {
        $message = '<div class="alert alert-danger">Bu firmayı silemezsiniz! Önce firmaya ait tüm seferleri silmelisiniz.</div>';
    } else {
        $pdo->prepare("DELETE FROM users WHERE firma_id = ? AND role = 'firma'")->execute([$firma_id]);
        $pdo->prepare("DELETE FROM firmalar WHERE id = ?")->execute([$firma_id]);
        $message = '<div class="alert alert-success">Firma ve bağlı yetkilileri başarıyla silindi.</div>';
        header("Location: admin_panel.php");
        exit;
    }
}

if (isset($_GET['delete_kupon'])) {
    $kupon_id = $_GET['delete_kupon'];
    try {
        $pdo->prepare("DELETE FROM kuponlar WHERE id = ?")->execute([$kupon_id]);
        $message = '<div class="alert alert-success">Kupon başarıyla silindi.</div>';
        header("Location: admin_panel.php"); 
        exit;
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Kupon silinirken bir hata oluştu.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['add_firma'])) {
        $firma_adi = trim($_POST['firma_adi']);
        if (!empty($firma_adi)) {
            try {
                $pdo->prepare("INSERT INTO firmalar (firma_adi) VALUES (?)")->execute([$firma_adi]);
                $message = '<div class="alert alert-success">Firma başarıyla eklendi.</div>';
            } catch (PDOException $e) { $message = '<div class="alert alert-danger">Hata: Bu firma adı zaten kayıtlı.</div>'; }
        } else { $message = '<div class="alert alert-danger">Firma adı boş bırakılamaz.</div>'; }
    }
    
    elseif (isset($_POST['add_user'])) {
        $username = trim($_POST['username']); $email = trim($_POST['email']); $password = $_POST['password']; $firma_id = $_POST['firma_id'];
        if (empty($username) || empty($email) || empty($password) || empty($firma_id)) { $message = '<div class="alert alert-danger">Lütfen yetkili için tüm alanları doldurun.</div>'; }
        else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?"); $stmt->execute([$username, $email]);
            if ($stmt->fetch()) { $message = '<div class="alert alert-danger">Bu kullanıcı adı veya e-posta zaten kullanılıyor.</div>'; }
            else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (username, email, password, role, firma_id) VALUES (?, ?, ?, 'firma', ?)")->execute([$username, $email, $hashed_password, $firma_id]);
                $message = '<div class="alert alert-success">Firma yetkilisi başarıyla oluşturuldu.</div>';
            }
        }
    }
    
    elseif (isset($_POST['add_bakiye'])) {
        $yolcu_id = $_POST['yolcu_id']; $miktar = $_POST['miktar'];
        if (!empty($yolcu_id) && is_numeric($miktar) && $miktar > 0) {
            $pdo->prepare("UPDATE users SET bakiye = bakiye + ? WHERE id = ? AND role = 'yolcu'")->execute([$miktar, $yolcu_id]);
            $message = '<div class="alert alert-success">Bakiye başarıyla eklendi.</div>';
        } else { $message = '<div class="alert alert-danger">Geçersiz kullanıcı veya miktar.</div>'; }
    }
    
    elseif (isset($_POST['add_kupon'])) {
        $kupon_kodu = strtoupper(trim($_POST['kupon_kodu'])); $indirim_orani = $_POST['indirim_orani']; $son_kullanim_tarihi = $_POST['son_kullanim_tarihi'];
        if(!empty($kupon_kodu) && !empty($indirim_orani) && !empty($son_kullanim_tarihi)){
            try {
                $pdo->prepare("INSERT INTO kuponlar (kupon_kodu, indirim_orani, son_kullanim_tarihi) VALUES (?, ?, ?)")->execute([$kupon_kodu, $indirim_orani, $son_kullanim_tarihi]);
                $message = '<div class="alert alert-success">Global Kupon başarıyla eklendi.</div>';
            } catch(PDOException $e) { $message = '<div class="alert alert-danger">Bu kupon kodu zaten mevcut.</div>'; }
        } else { $message = '<div class="alert alert-danger">Lütfen kupon için tüm alanları doldurun.</div>'; }
    }
}


$firmalar = $pdo->query("SELECT * FROM firmalar ORDER BY firma_adi")->fetchAll(PDO::FETCH_ASSOC);
$yolcular = $pdo->query("SELECT id, username, bakiye FROM users WHERE role = 'yolcu' ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);

$kupon_sql = "
    SELECT k.*, f.firma_adi 
    FROM kuponlar k
    LEFT JOIN firmalar f ON k.firma_id = f.id
    ORDER BY k.son_kullanim_tarihi DESC
";
$kuponlar = $pdo->query($kupon_sql)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <?php
        include 'header.php'; 
        ?>
        
        <?= $message ?>
        
        <h3>Firma Yönetimi</h3>
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <h4>Yeni Firma Oluştur</h4>
            <form method="POST" action="admin_panel.php">
                <div class="form-group"><label>Firma Adı:</label><input type="text" name="firma_adi" required></div>
                <button type="submit" name="add_firma" class="btn">Firma Ekle</button>
            </form>
            <h4 style="margin-top:20px;">Mevcut Firmalar</h4>
            <table><thead><tr><th>Firma Adı</th><th>İşlem</th></tr></thead>
                <tbody>
                    <?php foreach ($firmalar as $firma): ?>
                    <tr>
                        <td><?= htmlspecialchars($firma['firma_adi']) ?></td>
                        <td><a href="admin_panel.php?delete_firma=<?= $firma['id'] ?>" class="btn btn-danger" onclick="return confirm('Bu firmayı ve (varsa) bağlı yetkililerini silmek istediğinizden emin misiniz?')">Sil</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3>Firma Yetkilisi Yönetimi</h3>
        <form method="POST" action="admin_panel.php" style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <div class="form-group"><label>Hangi Firma İçin?</label>
                <select name="firma_id" required><option value="">-- Firma Seçin --</option>
                    <?php foreach ($firmalar as $firma): ?><option value="<?= $firma['id'] ?>"><?= htmlspecialchars($firma['firma_adi']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Yetkili Kullanıcı Adı:</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Yetkili E-posta:</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Yetkili Parolası:</label><input type="password" name="password" required></div>
            <button type="submit" name="add_user" class="btn">Yetkiliyi Oluştur</button>
        </form>

        <h3>Yolcu Bakiye Yönetimi</h3>
        <form method="POST" action="admin_panel.php" style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
            <div class="form-group"><label>Yolcu Seçin:</label>
                <select name="yolcu_id" required><option value="">-- Yolcu Seçin --</option>
                    <?php foreach ($yolcular as $yolcu): ?><option value="<?= $yolcu['id'] ?>"><?= htmlspecialchars($yolcu['username']) ?> (Mevcut: <?= number_format($yolcu['bakiye'], 2) ?> TL)</option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Eklenecek Miktar (TL):</label><input type="number" step="0.01" name="miktar" required></div>
            <button type="submit" name="add_bakiye" class="btn">Bakiyeyi Ekle</button>
        </form>

        <h3>Kupon Yönetimi (Tüm Sistem)</h3>
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px;">
            <h4>Yeni GLOBAL Kupon Oluştur</h4>
            <form method="POST" action="admin_panel.php">
                <div class="form-group"><label>Kupon Kodu:</label><input type="text" name="kupon_kodu" required></div>
                <div class="form-group"><label>İndirim Oranı (%):</label><input type="number" name="indirim_orani" min="1" max="100" required></div>
                <div class="form-group"><label>Son Kullanım Tarihi:</label><input type="date" name="son_kullanim_tarihi" required></div>
                <button type="submit" name="add_kupon" class="btn">Global Kupon Ekle</button>
            </form>
            <h4 style="margin-top:20px;">Mevcut Tüm Kuponlar</h4>
            <table>
                <thead><tr><th>Kod</th><th>İndirim</th><th>Son Geçerlilik</th><th>Ait Olduğu Firma</th><th>Durum</th><th>İşlem</th></tr></thead>
                <tbody>
                    <?php foreach ($kuponlar as $kupon): ?>
                    <tr>
                        <td><?= htmlspecialchars($kupon['kupon_kodu']) ?></td>
                        <td>%<?= $kupon['indirim_orani'] ?></td>
                        <td><?= date('d.m.Y', strtotime($kupon['son_kullanim_tarihi'])) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($kupon['firma_adi'] ?? 'Global (Admin)') ?></strong>
                        </td>
                        <td><?= $kupon['aktif_mi'] ? 'Aktif' : 'Pasif' ?></td>
                        <td>
                            <a href="admin_panel.php?delete_kupon=<?= $kupon['id'] ?>" 
                               class="btn btn-danger" 
                               style="padding: 5px 10px; font-size: 12px;" 
                               onclick="return confirm('Bu kuponu kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>