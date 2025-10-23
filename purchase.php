<?php
session_start();
require 'database.php';


date_default_timezone_set('Europe/Istanbul');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'yolcu' || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['koltuk_no'])) {
    header("Location: index.php?error=invalid_request");
    exit;
}

$sefer_id = $_POST['sefer_id'];
$yolcu_id = $_SESSION['user_id'];
$koltuk_no = $_POST['koltuk_no'];
$kupon_kodu = isset($_POST['kupon_kodu']) ? strtoupper(trim($_POST['kupon_kodu'])) : '';
$indirim_orani = 0;

try {
    
    
    $stmt_sefer = $pdo->prepare("SELECT fiyat, firma_id FROM seferler WHERE id = ?");
    $stmt_sefer->execute([$sefer_id]);
    $sefer = $stmt_sefer->fetch(PDO::FETCH_ASSOC);

    if (!$sefer) { throw new Exception("Sefer bulunamadı."); }
    
    $seferin_firma_id = $sefer['firma_id'];
    $bilet_fiyati = $sefer['fiyat'];

    
    if (!empty($kupon_kodu)) {
        $sql_kupon = "
            SELECT indirim_orani FROM kuponlar 
            WHERE kupon_kodu = ? 
              AND aktif_mi = 1 
              AND son_kullanim_tarihi >= date('now')
              AND (firma_id = ? OR firma_id IS NULL)
        ";
        $stmt_kupon = $pdo->prepare($sql_kupon);
        $stmt_kupon->execute([$kupon_kodu, $seferin_firma_id]);
        
        $kupon = $stmt_kupon->fetch(PDO::FETCH_ASSOC);
        if ($kupon) { 
            $indirim_orani = $kupon['indirim_orani']; 
        }
    }

    
    $stmt_check = $pdo->prepare("SELECT id FROM biletler WHERE sefer_id = ? AND koltuk_no = ?");
    $stmt_check->execute([$sefer_id, $koltuk_no]);
    if ($stmt_check->fetch()) {
        header("Location: seat_selection.php?sefer_id=$sefer_id&error=seat_taken");
        exit;
    }
    
    
    $son_fiyat = round($bilet_fiyati * (1 - ($indirim_orani / 100.0)), 2);

    $pdo->beginTransaction();

    
    $update_stmt = $pdo->prepare(
        "UPDATE users SET bakiye = bakiye - ? WHERE id = ? AND bakiye >= ?"
    );
    $update_stmt->execute([$son_fiyat, $yolcu_id, $son_fiyat]);

    if ($update_stmt->rowCount() > 0) {
        
        
        $su_anki_tarih = date('Y-m-d H:i:s');
        

        
        $stmt_insert = $pdo->prepare(
            "INSERT INTO biletler (yolcu_id, sefer_id, koltuk_no, alis_tarihi) VALUES (?, ?, ?, ?)"
        );
        $stmt_insert->execute([$yolcu_id, $sefer_id, $koltuk_no, $su_anki_tarih]); 
        
        
        $pdo->commit();
        header("Location: my_tickets.php?status=success");
        exit;

    } else {
        $pdo->rollBack();
        header("Location: index.php?error=insufficient_balance");
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    header("Location: index.php?error=purchase_failed&reason=" . urlencode($e->getMessage()));
    exit;
}
?>