# Veritabanı Runbook (yalnızca insan adımları)

Bu dosya **insan tarafından uygulanacak adımları** tarif eder. Şemayı otomatik
uygulayan hiçbir kod/script yoktur ve olmayacaktır. `database/schema.sql`
yalnızca `CREATE TABLE IF NOT EXISTS` içerir; idempotenttir ve mevcut tabloları
değiştirmez. Yerel makinede MariaDB/MySQL kurulmaz (sahip kararı, 2026-09-17).

## 1. Staging veritabanı oluşturma (cPanel)

1. cPanel → **MySQL® Databases** (MariaDB) bölümüne girin.
2. "Create New Database" ile örn. `uXXXXXX_akanbest_stage` adında bir veritabanı oluşturun.
3. "MySQL Users" altında staging için ayrı bir kullanıcı oluşturun; parolayı parola yöneticisinde saklayın.
4. Kullanıcıyı veritabanına eklerken **ALL PRIVILEGES** verin (yalnızca staging).
5. Kimlik bilgilerini staging sunucusundaki `.env` dosyasına girin; Git'e asla eklemeyin.

## 2. Şemayı uygulama (phpMyAdmin, insan adımı)

1. cPanel → **phpMyAdmin**'i açın ve sol menüden staging veritabanını seçin.
2. Üstteki **SQL** ya da **Import** sekmesine gidin.
3. `database/schema.sql` dosyasının tamamını içe aktarın (Import → Choose File → Go).
4. Çıktıda hata olmadığını doğrulayın. Dosya idempotenttir; yeniden çalıştırmak
   mevcut tabloları değiştirmez (yalnızca eksikleri oluşturur).

## 3. Doğrulama sorguları (phpMyAdmin → SQL)

Aşağıdaki sorguların sonuçlarını kayıt altına alın:

```sql
-- 9 tablo listelenmelidir
SHOW TABLES;

-- Tablo motoru/charset kontrolü (hepsi InnoDB / utf8mb4_unicode_ci olmalı)
SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME;

-- FK sayısı: categories 1, products 1, product_images 1, quote_items 2 = toplam 5
SELECT TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE();

-- Unique indexler: admins.email, categories.slug, products.slug, pages.slug, settings.key
SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND INDEX_NAME LIKE 'uk_%'
GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE;
```

Beklenen: 9 tablo, 5 foreign key, 5 unique index; tüm motorlar InnoDB.

## 4. Production kapısı (insan onayı)

1. Production veritabanı (u0506662_best) **tamamen izoledir**; bu runbook'un
   1–3. adımları production üzerinde çalıştırılmaz.
2. Production uygulaması için şema gerekli olduğunda: önce staging'de 3. bölümdeki
   doğrulamalar PASS olmalı, ardından **yazılı sahip onayı** alınmalıdır.
3. Onay olmadan production veritabanına hiçbir DDL uygulanmaz.
4. Uygulama öncesi phpMyAdmin Export ile yedek alın (yapı + veri) ve arşivleyin.
5. Uygulamayı production phpMyAdmin üzerinde, 2. bölümdeki adımlarla yapın.

## 5. Rollback notu

- `CREATE TABLE IF NOT EXISTS` şeması geri alma komutu içermez (bilinçli sözleşme).
- Bir tablo yanlış oluşturulduysa: veri varsa `INSERT ... SELECT` ile veriyi taşıyın,
  ardından tabloyu **elle** (phpMyAdmin Operations/Drop) kaldırıp düzeltilmiş DDL'i
  uygulayın. Bu adımlar bilinçli insan kararıdır; otomatikleştirilmez.
- İlk canlı uygulama öncesi alınan yedek, geri dönüş noktasıdır.

## 6. Admin hesabı seed (insan adımı)

1. Staging sunucusunda, app deposunun bir kopyasında:
   `php scripts/gen-admin-hash.php "<güçlü-parola>"` komutunu çalıştırın.
   (Script yalnızca CLI'da çalışır; veritabanına bağlanmaz.)
2. Çıktıdaki hash'i panoya kopyalayın; komut geçmişini temizleyin.
3. phpMyAdmin → staging veritabanı → SQL sekmesi:

```sql
INSERT INTO `admins` (`name`, `email`, `password`, `created_at`, `updated_at`)
VALUES ('<gerçek-ad>', '<staging-admin@example.com>', '<HASH>', NOW(), NOW());
```

4. Parolayı asla düz metin olarak not etmeyin; yalnızca hash saklanır.
5. İlk girişten sonra parolayı panelden değiştirin (yeni hash üretip UPDATE ile).
