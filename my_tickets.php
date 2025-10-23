<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'database.php'; 


date_default_timezone_set('Europe/Istanbul');



if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'yolcu') {
    header("Location: login.php");
    exit;
}

$yolcu_id = $_SESSION['user_id'];
$sql = "
    SELECT 
        b.id AS bilet_id,
        b.alis_tarihi, b.koltuk_no,
        s.sefer_tarihi, s.fiyat, s.kalkis_yeri, s.varis_yeri,
        f.firma_adi
    FROM biletler AS b
    JOIN seferler AS s ON b.sefer_id = s.id
    JOIN firmalar AS f ON s.firma_id = f.id
    WHERE b.yolcu_id = ? ORDER BY s.sefer_tarihi DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$yolcu_id]);
$biletler = $stmt->fetchAll(PDO::FETCH_ASSOC);


$message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') { $message = '<div class="alert alert-success">Bilet başarıyla satın alındı! BÖHÖHÖHYT</div>'; }
    if ($_GET['status'] === 'cancelled') { $message = '<div class="alert alert-success">Biletiniz başarıyla iptal edildi ve ücret iadesi yapıldı.</div>'; }
    if ($_GET['status'] === 'cancel_failed') { $message = '<div class="alert alert-danger">Bilet iptal edilemedi. Sefer saatine 1 saatten az kalmış olabilir.</div>'; }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Biletlerim</title>
    <link rel="stylesheet" href="style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container">
        <?php include 'header.php'; ?> 
        
        <h2>Satın Aldığım Biletler</h2>
        <?= $message ?>
        <table>
            <thead><tr>
                <th>Firma</th><th>Güzergah</th><th>Koltuk No</th><th>Sefer Tarihi</th><th>İşlemler</th>
            </tr></thead>
            <tbody>
                <?php foreach ($biletler as $bilet): ?>
                <tr>
                    <td><?= htmlspecialchars($bilet['firma_adi']) ?></td>
                    <td><?= htmlspecialchars($bilet['kalkis_yeri']) ?> &rarr; <?= htmlspecialchars($bilet['varis_yeri']) ?></td>
                    <td><strong><?= htmlspecialchars($bilet['koltuk_no']) ?></strong></td>
                    <td><?= htmlspecialchars(date('d F Y, H:i', strtotime($bilet['sefer_tarihi']))) ?></td>
                    <td>
                        <a href="generate_ticket_pdf.php?bilet_id=<?= $bilet['bilet_id'] ?>" class="btn" target="_blank" style="margin-right: 5px;">PDF</a>
                        
                        <?php
                            
                            $sefer_zamani = strtotime($bilet['sefer_tarihi']);
                            $simdiki_zaman = time();
                            $kalan_saniye = $sefer_zamani - $simdiki_zaman;
                            
                            if ($kalan_saniye > 3600): 
                        ?>
                            <a href="cancel_ticket.php?bilet_id=<?= $bilet['bilet_id'] ?>" class="btn btn-danger" onclick="return confirm('Bu bileti iptal etmek istediğinizden emin misiniz? Ücret iadesi anında hesabınıza yapılacaktır.')">İptal Et</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <audio id="purchaseSound" src="sounds/gulme_sesi.mp3" preload="auto"></audio>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            
            if (status === 'success') {
                const purchaseSound = document.getElementById('purchaseSound');
                if (purchaseSound) {
                    purchaseSound.play().catch(error => {
                        console.log("Tarayıcı, sesi otomatik başlatmayı engelledi.");
                    });
                }
            }
        });
    </script>
    
</body>
</html>