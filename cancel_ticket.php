<?php
session_start();
require 'database.php';

date_default_timezone_set('Europe/Istanbul');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'yolcu' || !isset($_GET['bilet_id'])) {
    header("Location: login.php");
    exit;
}

$bilet_id = $_GET['bilet_id'];
$yolcu_id = $_SESSION['user_id'];

try {
    
    $pdo->beginTransaction();

    
    $sql = "
        SELECT s.sefer_tarihi, s.fiyat 
        FROM biletler b
        JOIN seferler s ON b.sefer_id = s.id
        WHERE b.id = ? AND b.yolcu_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bilet_id, $yolcu_id]);
    $bilet_bilgileri = $stmt->fetch(PDO::FETCH_ASSOC);

    
    if (!$bilet_bilgileri) {
        throw new Exception("Bilet bulunamadı veya yetkiniz yok.");
    }

    $sefer_zamani = strtotime($bilet_bilgileri['sefer_tarihi']);
    if (($sefer_zamani - time()) <= 3600) {
        
        throw new Exception("İptal süresi geçmiş.");
    }

   
    $iade_tutari = $bilet_bilgileri['fiyat'];
    $stmt_iade = $pdo->prepare("UPDATE users SET bakiye = bakiye + ? WHERE id = ?");
    $stmt_iade->execute([$iade_tutari, $yolcu_id]);

    
    $stmt_sil = $pdo->prepare("DELETE FROM biletler WHERE id = ?");
    $stmt_sil->execute([$bilet_id]);
    
    
    $pdo->commit();

    
    header("Location: my_tickets.php?status=cancelled");
    exit;

} catch (Exception $e) {
    
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    header("Location: my_tickets.php?status=cancel_failed");
    exit;
}
?>