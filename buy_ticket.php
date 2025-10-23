<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'yolcu') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['sefer_id'])) {
    $sefer_id = $_GET['sefer_id'];
    $yolcu_id = $_SESSION['user_id'];

    try {
        
        $stmt_sefer = $pdo->prepare("SELECT fiyat FROM seferler WHERE id = ?");
        $stmt_sefer->execute([$sefer_id]);
        $sefer = $stmt_sefer->fetch(PDO::FETCH_ASSOC);

        $stmt_user = $pdo->prepare("SELECT bakiye FROM users WHERE id = ?");
        $stmt_user->execute([$yolcu_id]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if (!$sefer || !$user) {
            throw new Exception("Sefer veya kullanıcı bulunamadı.");
        }

        $bilet_fiyati = $sefer['fiyat'];
        $kullanici_bakiyesi = $user['bakiye'];

        
        if ($kullanici_bakiyesi < $bilet_fiyati) {
            header("Location: index.php?error=insufficient_balance");
            exit;
        }

       
        $pdo->beginTransaction();

        
        $yeni_bakiye = $kullanici_bakiyesi - $bilet_fiyati;
        $stmt_update = $pdo->prepare("UPDATE users SET bakiye = ? WHERE id = ?");
        $stmt_update->execute([$yeni_bakiye, $yolcu_id]);

        
        $stmt_insert = $pdo->prepare("INSERT INTO biletler (yolcu_id, sefer_id) VALUES (?, ?)");
        $stmt_insert->execute([$yolcu_id, $sefer_id]);

        
        $pdo->commit();

        header("Location: my_tickets.php?status=success");
        exit;

    } catch (Exception $e) {
        
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: index.php?error=purchase_failed");
        exit;
    }
}