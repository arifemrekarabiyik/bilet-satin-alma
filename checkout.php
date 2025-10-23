<?php
require 'database.php';
include 'header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'yolcu') {
    header("Location: login.php");
    exit;
}
if (!isset($_GET['sefer_id'])) {
    header("Location: index.php");
    exit;
}

$sefer_id = $_GET['sefer_id'];
$stmt = $pdo->prepare("SELECT s.*, f.firma_adi FROM seferler s JOIN firmalar f ON s.firma_id = f.id WHERE s.id = ?");
$stmt->execute([$sefer_id]);
$sefer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sefer) {
    die("Sefer bulunamadı!");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="UTF-8"><title>Bilet Onay</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <h2>Bilet Satın Almayı Onayla</h2>
        <h3>Sefer Detayları</h3>
        <p><strong>Firma:</strong> <?= htmlspecialchars($sefer['firma_adi']) ?></p>
        <p><strong>Güzergah:</strong> <?= htmlspecialchars($sefer['kalkis_yeri']) ?> -> <?= htmlspecialchars($sefer['varis_yeri']) ?></p>
        <p><strong>Tarih:</strong> <?= date('d.m.Y H:i', strtotime($sefer['sefer_tarihi'])) ?></p>
        <p><strong>Fiyat:</strong> <?= number_format($sefer['fiyat'], 2) ?> TL</p>
        <hr>
        <form action="purchase.php" method="POST">
            <input type="hidden" name="sefer_id" value="<?= $sefer['id'] ?>">
            <div class="form-group">
                <label for="kupon_kodu">İndirim Kuponu (varsa):</label>
                <input type="text" name="kupon_kodu" id="kupon_kodu" placeholder="Örn: GEMINI25">
            </div>
            <button type="submit" class="btn">Onayla ve Satın Al</button>
        </form>
    </div>
</body>
</html>