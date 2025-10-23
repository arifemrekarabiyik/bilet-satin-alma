# Apache ve PHP 8.2 içeren resmi PHP imajını temel alıyoruz.
FROM php:8.2-apache

# --- GÜNCELLEME BURADA ---
# pdo_sqlite eklentisini kurmadan önce, bağımlı olduğu sistem kütüphanesini kuruyoruz.
# apt-get update: Paket listesini günceller.
# apt-get install -y libsqlite3-dev: Gerekli kütüphaneyi kurar.
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite

# Proje dosyalarını (mevcut klasördeki her şeyi) web sunucusunun ana dizinine kopyalıyoruz.
COPY . /var/www/html/

# Veritabanı dosyasının oluşturulabilmesi için 'database' klasörüne yazma izni veriyoruz.
RUN chown -R www-data:www-data /var/www/html/database