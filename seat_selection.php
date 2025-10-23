<?php
require 'database.php';
include 'header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'yolcu') { header("Location: login.php"); exit; }
if (!isset($_GET['sefer_id'])) { header("Location: index.php"); exit; }

$sefer_id = $_GET['sefer_id'];


$stmt_sefer = $pdo->prepare("SELECT s.*, f.firma_adi FROM seferler s JOIN firmalar f ON s.firma_id = f.id WHERE s.id = ?");
$stmt_sefer->execute([$sefer_id]);
$sefer = $stmt_sefer->fetch(PDO::FETCH_ASSOC);

if (!$sefer) { die("Sefer bulunamadı!"); }


$stmt_koltuklar = $pdo->prepare("SELECT koltuk_no FROM biletler WHERE sefer_id = ?");
$stmt_koltuklar->execute([$sefer_id]);
$dolu_koltuklar = $stmt_koltuklar->fetchAll(PDO::FETCH_COLUMN, 0);


$toplam_koltuk = $sefer['kapasite'];
?>
<!DOCTYPE html>
<html lang="tr">
<head><meta charset="UTF-8"><title>Koltuk Seçimi</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
    <h2>Koltuk Seçimi</h2>
    <p><strong>Firma:</strong> <?= htmlspecialchars($sefer['firma_adi']) ?>, <strong>Güzergah:</strong> <?= htmlspecialchars($sefer['kalkis_yeri']) ?> -> <?= htmlspecialchars($sefer['varis_yeri']) ?></p>

    <form action="purchase.php" method="POST">
        <input type="hidden" name="sefer_id" value="<?= $sefer['id'] ?>">

        <h4>Lütfen koltuğunuzu seçiniz: (Toplam Kapasite: <?= $toplam_koltuk ?>)</h4>
        <div class="bus-layout">
            <?php for ($i = 1; $i <= $toplam_koltuk; $i++): ?>
                <?php
                    $is_occupied = in_array($i, $dolu_koltuklar);
                    
                    if ($i % 3 === 0) {
                        echo '<div class="aisle"></div>'; 
                    }
                ?>
                <div class="seat-container">
                    <?php if ($is_occupied): ?>
                        <div class="seat occupied"><?= $i ?></div>
                    <?php else: ?>
                        <input type="radio" id="koltuk<?= $i ?>" name="koltuk_no" value="<?= $i ?>" required>
                        <label for="koltuk<?= $i ?>" class="seat available"><?= $i ?></label>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
        
        <hr>
        <div class="form-group" style="margin-top:20px;">
            <label for="kupon_kodu">İndirim Kuponu (varsa):</label>
            <input type="text" name="kupon_kodu" id="kupon_kodu" placeholder="Kupon Kodunu Girin">
        </div>
        <button type="submit" class="btn">Onayla ve Satın Al</button>
    </form>
</div>
</body>
</html>