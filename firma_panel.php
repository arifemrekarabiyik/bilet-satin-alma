<?php
require 'database.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'firma') {
    header("Location: login.php");
    exit;
}
$firma_id = $_SESSION['firma_id'];
$message = '';


if (isset($_GET['delete_kupon'])) {
    $kupon_id = $_GET['delete_kupon'];
    try {
        
        $stmt = $pdo->prepare("DELETE FROM kuponlar WHERE id = ? AND firma_id = ?");
        $stmt->execute([$kupon_id, $firma_id]);
        
        if ($stmt->rowCount() > 0) {
            $message = '<div class="alert alert-success">Kupon başarıyla silindi.</div>';
        } else {
            
            $message = '<div class="alert alert-danger">Kupon silinemedi veya bu kuponu silme yetkiniz yok.</div>';
        }
        
        header("Location: firma_panel.php");
        
        exit;
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Kupon silinirken bir hata oluştu.</div>';
    }
}




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_kupon_firma'])) {
    $kupon_kodu = strtoupper(trim($_POST['kupon_kodu']));
    $indirim_orani = $_POST['indirim_orani'];
    $son_kullanim_tarihi = $_POST['son_kullanim_tarihi'];

    if(!empty($kupon_kodu) && !empty($indirim_orani) && !empty($son_kullanim_tarihi)){
        try {
            $sql = "INSERT INTO kuponlar (kupon_kodu, indirim_orani, son_kullanim_tarihi, firma_id) VALUES (?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$kupon_kodu, $indirim_orani, $son_kullanim_tarihi, $firma_id]);
            $message = '<div class="alert alert-success">Kupon başarıyla eklendi.</div>';
        } catch(PDOException $e) {
            $message = '<div class="alert alert-danger">Bu kupon kodu zaten mevcut.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Lütfen kupon için tüm alanları doldurun.</div>';
    }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sefer'])) {
    $kapasite = $_POST['kapasite'];
    $stmt = $pdo->prepare(
        "INSERT INTO seferler (kalkis_yeri, varis_yeri, sefer_tarihi, fiyat, kapasite, firma_id) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $_POST['kalkis_yeri'], $_POST['varis_yeri'], 
        $_POST['sefer_tarihi'], $_POST['fiyat'], 
        $kapasite, $firma_id
    ]);
    $message = '<div class="alert alert-success">Sefer başarıyla eklendi.</div>';
}


$stmt_list = $pdo->prepare("SELECT * FROM seferler WHERE firma_id = ? ORDER BY sefer_tarihi DESC");
$stmt_list->execute([$firma_id]);
$seferler = $stmt_list->fetchAll(PDO::FETCH_ASSOC);


$stmt_kuponlar = $pdo->prepare("SELECT * FROM kuponlar WHERE firma_id = ? ORDER BY son_kullanim_tarihi DESC");
$stmt_kuponlar->execute([$firma_id]);
$kuponlar = $stmt_kuponlar->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Firma Paneli</title>
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
        <h3>Yeni Sefer Ekle</h3>
        <form method="POST" action="firma_panel.php">
            <div class="form-group"><label>Kalkış Yeri:</label><input type="text" name="kalkis_yeri" required></div>
            <div class="form-group"><label>Varış Yeri:</label><input type="text" name="varis_yeri" required></div>
            <div class="form-group"><label>Sefer Tarihi ve Saati:</label><input type="datetime-local" name="sefer_tarihi" required></div>
            <div class="form-group"><label>Fiyat (TL):</label><input type="number" step="0.01" name="fiyat" required></div>
            <div class="form-group">
                <label for="kapasite">Araç Kapasitesi (Koltuk Sayısı):</label>
                <input type="number" id="kapasite" name="kapasite" min="20" max="60" value="30" required>
            </div>
            <button type="submit" name="add_sefer" class="btn">Sefer Ekle</button>
        </form>
        
        <hr style="margin: 30px 0;">

        <h3>Firma Kupon Yönetimi</h3>
        <div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px;">
            <h4>Yeni Kupon Oluştur (Sadece Sizin Seferlerinizde Geçerli Olur)</h4>
            <form method="POST" action="firma_panel.php">
                <div class="form-group"><label>Kupon Kodu:</label><input type="text" name="kupon_kodu" required></div>
                <div class="form-group"><label>İndirim Oranı (%):</label><input type="number" name="indirim_orani" min="1" max="100" required></div>
                <div class="form-group"><label>Son Kullanım Tarihi:</label><input type="date" name="son_kullanim_tarihi" required></div>
                <button type="submit" name="add_kupon_firma" class="btn">Kupon Ekle</button>
            </form>
            <h4 style="margin-top:20px;">Firmanıza Ait Kuponlar</h4>
            <table>
                <thead><tr><th>Kod</th><th>İndirim</th><th>Son Geçerlilik</th><th>Durum</th><th>İşlem</th></tr></thead>
                <tbody>
                    <?php foreach ($kuponlar as $kupon): ?>
                    <tr>
                        <td><?= htmlspecialchars($kupon['kupon_kodu']) ?></td><td>%<?= $kupon['indirim_orani'] ?></td>
                        <td><?= date('d.m.Y', strtotime($kupon['son_kullanim_tarihi'])) ?></td>
                        <td><?= $kupon['aktif_mi'] ? 'Aktif' : 'Pasif' ?></td>
                        <td>
                            <a href="firma_panel.php?delete_kupon=<?= $kupon['id'] ?>" 
                               class="btn btn-danger" 
                               style="padding: 5px 10px; font-size: 12px;" 
                               onclick="return confirm('Bu kuponu kalıcı olarak silmek istediğinizden emin misiniz?')">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <hr style="margin: 30px 0;">
        <h3>Firmanıza Ait Seferler</h3>
        <table>
             <thead><tr><th>Kalkış</th><th>Varış</th><th>Tarih</th><th>Fiyat</th><th>Kapasite</th></tr></thead>
             <tbody>
                <?php foreach ($seferler as $sefer): ?>
                <tr>
                    <td><?= htmlspecialchars($sefer['kalkis_yeri']) ?></td>
                    <td><?= htmlspecialchars($sefer['varis_yeri']) ?></td>
                    <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($sefer['sefer_tarihi']))) ?></td>
                    <td><?= htmlspecialchars($sefer['fiyat']) ?> TL</td>
                    <td><?= htmlspecialchars($sefer['kapasite']) ?></td>
                </tr>
                <?php endforeach; ?>
             </tbody>
        </table>

    </div>
</body>
</html>