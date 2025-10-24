Bilet satın alırken sitede "ses ve videoları engelle"'yi kapatırsanız bilet başarıyla alınırsa recep ivedik gülüyor.
İyi yolculuklar dileriz...
## Kurulum (Development)
Kurulum (Docker ile - Tavsiye Edilen)

Bu projeyi çalıştırmanın en kolay yolu Docker kullanmaktır. Bilgisayarınızda Docker ve Docker Compose kurulu olmalıdır.

    Projeyi klonlayın:
    git clone https://github.com/arifemrekarabiyik/bilet-satin-alma.git
    cd bilet-satin-alma



Docker konteynerlerini başlatın:

    docker compose up --build -d


Veritabanını Kurun (En Önemli Adım): Konteyner ilk başladığında veritabanı boştur. Veritabanını, tabloları ve test kullanıcılarını oluşturmak için tarayıcınızdan aşağıdaki adrese gidin:

    http://localhost/setup.php

(Not: Eğer docker-compose.yml dosyanızda portu değiştirdiyseniz, 1234 gibi, http://localhost:1234/setup.php adresini kullanın.)

Ekranda "Kurulum başarıyla tamamlandı!" mesajını gördüğünüzde GÜVENLİK İÇİN setup.php dosyasını silin. (Dosyayı yerel klasörünüzden silmeniz yeterlidir).

Kullanıma Hazır! Artık http://localhost/ adresinden siteye erişebilir ve aşağıdaki test kullanıcılarıyla giriş yapabilirsiniz:

    Admin: admin / admin123

    Firma: kamil / kamil

    Yolcu: arif / arif

 XAMPP ile çalıştırma

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
