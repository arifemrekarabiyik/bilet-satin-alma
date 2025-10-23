<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);


require 'database.php';

try {
    echo "Veritabanı kurulumu başlatıldı...<br><hr>";

    
    $pdo->exec("DROP TABLE IF EXISTS biletler");
    $pdo->exec("DROP TABLE IF EXISTS seferler");
    $pdo->exec("DROP TABLE IF EXISTS kuponlar");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("DROP TABLE IF EXISTS firmalar");
    echo "Eski tablolar başarıyla temizlendi.<br><hr>";

    echo "Yeni tablolar oluşturuluyor...<br>";

    
    $pdo->exec("CREATE TABLE firmalar (id INTEGER PRIMARY KEY, firma_adi VARCHAR(100) NOT NULL UNIQUE)");
    
    $pdo->exec("
        CREATE TABLE kuponlar (
            id INTEGER PRIMARY KEY AUTOINCREMENT, 
            kupon_kodu VARCHAR(50) NOT NULL UNIQUE, 
            indirim_orani INTEGER NOT NULL, 
            son_kullanim_tarihi DATE NOT NULL, 
            aktif_mi INTEGER NOT NULL DEFAULT 1,
            firma_id INTEGER DEFAULT NULL 
        )
    ");

    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            role VARCHAR(20) NOT NULL,
            bakiye REAL NOT NULL DEFAULT 0.0,
            firma_id INTEGER,
            FOREIGN KEY (firma_id) REFERENCES firmalar(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE seferler (
            id INTEGER PRIMARY KEY,
            kalkis_yeri VARCHAR(100) NOT NULL,
            varis_yeri VARCHAR(100) NOT NULL,
            sefer_tarihi DATETIME NOT NULL,
            fiyat REAL NOT NULL,
            kapasite INTEGER NOT NULL DEFAULT 30,
            firma_id INTEGER NOT NULL,
            FOREIGN KEY (firma_id) REFERENCES firmalar(id)
        )
    ");
    

    $pdo->exec("
        CREATE TABLE biletler (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            yolcu_id INTEGER NOT NULL,
            sefer_id INTEGER NOT NULL,
            koltuk_no INTEGER NOT NULL,
            alis_tarihi TIMESTAMP NOT NULL, 
            FOREIGN KEY (yolcu_id) REFERENCES users(id),
            FOREIGN KEY (sefer_id) REFERENCES seferler(id)
        )
    ");


    echo "Tüm tablolar en güncel halleriyle başarıyla oluşturuldu.<br><hr>";

    
    echo "Örnek veriler ekleniyor...<br>";
    

    $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)")
        ->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin@site.com', 'admin']);
    $pdo->prepare("INSERT INTO firmalar (firma_adi) VALUES (?)")->execute(['Kamil Zort']);
    $firma_id = $pdo->lastInsertId(); 
    $pdo->prepare("INSERT INTO users (username, password, email, role, firma_id) VALUES (?, ?, ?, ?, ?)")
        ->execute(['kamil', password_hash('kamil', PASSWORD_DEFAULT), 'kamil@kamilzort.com', 'firma', $firma_id]);
    $pdo->prepare("INSERT INTO users (username, password, email, role, bakiye) VALUES (?, ?, ?, ?, ?)")
        ->execute(['arif', password_hash('arif', PASSWORD_DEFAULT), 'arif@mail.com', 'yolcu', 1500.0]);
    $pdo->prepare("INSERT INTO kuponlar (kupon_kodu, indirim_orani, son_kullanim_tarihi) VALUES (?, ?, ?)")
        ->execute(['BÖHÖHÖYT', 25, '2025-12-31']);
    echo "Örnek kullanıcılar, firma ve global kupon eklendi.<br>";
    $seferler_data = [
        ['İstanbul', 'Ankara', date('Y-m-d H:i:s', strtotime('+1 day 10:00')), 650.00, 30],
        ['İzmir', 'Antalya', date('Y-m-d H:i:s', strtotime('+2 days 22:30')), 720.50, 42],
        ['Ankara', 'İstanbul', date('Y-m-d H:i:s', strtotime('+3 days 14:00')), 650.00, 30]
    ];
    $stmt = $pdo->prepare("INSERT INTO seferler (kalkis_yeri, varis_yeri, sefer_tarihi, fiyat, kapasite, firma_id) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($seferler_data as $sefer) {
        $stmt->execute([$sefer[0], $sefer[1], $sefer[2], $sefer[3], $sefer[4], $firma_id]);
    }
    echo "3 adet örnek sefer başarıyla 'Kamil Zort' firmasına eklendi.<br>";
    echo "<hr>Kurulum başarıyla tamamlandı!<br>";
    echo "<b>Admin Girişi:</b> admin / admin123<br>";
    echo "<b>Firma Yetkilisi Girişi:</b> kamil / kamil<br>";
    echo "<b>Yolcu Girişi:</b> arif / arif<br><br>";
    echo "<b>GÜVENLİK UYARISI:</b> Bu 'setup.php' dosyasını şimdi sunucudan silin!";

} catch (PDOException $e) {
    die("HATA: " . $e->getMessage());
}
?>