Bilet satın alırken sitede "ses ve videoları engelle"'yi kapatırsanız bilet başarıyla alınırsa recep ivedik gülüyor.
İyi yolculuklar dileriz...
## Kurulum (Development)

Projenin çalışabilmesi için bir veritabanına ihtiyacı vardır. Veritabanını ve test kullanıcılarını oluşturmak için lütfen aşağıdaki adımları izleyin:

1.  Projeyi klonlayın veya indirin.
2.  Dosyaları XAMPP, WAMP veya benzeri bir PHP sunucusunun `htdocs` klasörüne taşıyın.
3.  Sunucunuzu çalıştırın (Apache vb.).
4.  Tarayıcınızdan `http://localhost/PROJE_KLASOR_ADI/setup.php` adresine gidin. (Örn: `http://localhost/otobus_projesi/setup.php`)
5.  Ekranda "Kurulum başarıyla tamamlandı!" mesajını gördüğünüzde veritabanınız ve test kullanıcılarınız hazır olacaktır.
6.  **GÜVENLİK:** Kurulumu tamamladıktan sonra `setup.php` dosyasını sunucunuzdan **mutlaka silin**.

## Test Kullanıcıları

Veritabanı oluşturulduğunda aşağıdaki test kullanıcıları otomatik olarak eklenir:

* **Rol:** Admin (Tüm siteyi yönetir)
    * **Kullanıcı Adı:** `admin`
    * **Şifre:** `admin123`

* **Rol:** Firma Yetkilisi (Kendi firmasının seferlerini ve kuponlarını yönetir)
    * **Kullanıcı Adı:** `kamil`
    * **Şifre:** `kamil`

* **Rol:** Yolcu (Bilet alır, bakiye yükler)
    * **Kullanıcı Adı:** `arif`
    * **Şifre:** `arif`
