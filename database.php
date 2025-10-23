<?php

$db_path = __DIR__ . '/database/otobus.sqlite';

try {
    
    $pdo = new PDO('sqlite:' . $db_path);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanına bağlanılamadı: " . $e->getMessage());
}
?>