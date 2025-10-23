<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'database.php'; 

$error = $_GET['error'] ?? '';
$message = '';
if ($error === 'insufficient_balance') { $message = '<div class="alert alert-danger">Bilet almak için bakiyeniz yetersiz.</div>'; }
elseif ($error === 'purchase_failed') { $message = '<div class="alert alert-danger">Satın alma sırasında bir hata oluştu.</div>'; }

$kalkis = $_GET['kalkis'] ?? '';
$varis = $_GET['varis'] ?? '';

$sql = "SELECT s.*, f.firma_adi FROM seferler s JOIN firmalar f ON s.firma_id = f.id WHERE 1=1";
$params = [];
if (!empty($kalkis)) { $sql .= " AND s.kalkis_yeri LIKE ?"; $params[] = '%' . $kalkis . '%'; }
if (!empty($varis)) { $sql .= " AND s.varis_yeri LIKE ?"; $params[] = '%' . $varis . '%'; }
$sql .= " ORDER BY s.sefer_tarihi ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$seferler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Otobüs Seferleri</title>
    <link rel="stylesheet" href="style.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container">
        <?php include 'header.php'; ?>

        <h2>Sefer Ara</h2>
        <?= $message ?>
        <form method="GET" action="index.php" style="display:flex; gap: 15px; margin-bottom: 20px;">
            <input type="text" name="kalkis" placeholder="Kalkış Yeri" value="<?= htmlspecialchars($kalkis) ?>" style="flex:1;">
            <input type="text" name="varis" placeholder="Varış Yeri" value="<?= htmlspecialchars($varis) ?>" style="flex:1;">
            <button type="submit" class="btn">Ara</button>
        </form>

        <table>
            <thead><tr><th>Firma</th><th>Kalkış</th><th>Varış</th><th>Tarih</th><th>Fiyat</th><th>İşlem</th></tr></thead>
            <tbody>
                <?php if (count($seferler) > 0): ?>
                    <?php foreach ($seferler as $sefer): ?>
                        <tr>
                            <td><?= htmlspecialchars($sefer['firma_adi']) ?></td>
                            <td><?= htmlspecialchars($sefer['kalkis_yeri']) ?></td>
                            <td><?= htmlspecialchars($sefer['varis_yeri']) ?></td>
                            <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($sefer['sefer_tarihi']))) ?></td>
                            <td><?= htmlspecialchars(number_format($sefer['fiyat'], 2)) ?> TL</td>
                            <td>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'yolcu'): ?>
                                    <a href="seat_selection.php?sefer_id=<?= $sefer['id'] ?>" class="btn">Bilet Al</a>
                                <?php else: ?>
                                    <a href="#" onclick="showLoginAlert()" class="btn">Bilet Al</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">Aradığınız kriterlere uygun sefer bulunamadı.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function showLoginAlert() {
            alert("Bilet alabilmek için lütfen önce sisteme giriş yapın.");
            window.location.href = "login.php";
        }
    </script>
    </body>
</html>