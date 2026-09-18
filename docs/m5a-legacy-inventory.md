# M5-A Legacy Inventory (sadece analiz — kod yok)

Keşif kökü: `C:\Users\PC\AppData\Local\Cline\Akanbest\` (recursive).
`tools\` atlandı. `akanbest-website\` içinde legacy aranmadı (M0'da temizlendi);
beklenmedik legacy kalıntısı görülmedi. DB bağlantısı kurulmadı; SQL çalıştırılmadı.

## 1. LEGACY CODE LOCATION: NOT FOUND

- Kök dışında (`tools\` ve `akanbest-website\` hariç) *.php dosyası: **0**.
- `akanbest-website\` içinde 34 PHP dosyası mevcut — tamamı M0–M4 yeni sistem kodu.
- Anahtar kelime taraması (`urun`, `baslik`, `resim`, `kategori`, `mysql_`, `detay`):
  - `urun` / `baslik` / `resim` / `mysql_` / `detay`: **0 dosya**.
  - `kategori`: 6 dosya — tamamı M4 yeni sistem kodu
    (`CategoryController.php`, `dashboard.php`, `categories/{create,edit,index}.php`,
    `partials/header.php`); Türkçe UI metni ("Kategoriler") + `categories` tablo adı.
- Sonuç: **"LEGACY CODE LOCAL: NOT FOUND"** → C durumu.

## 2. resim/resim2 FORMAT: N-A

- Legacy kod bulunamadığı için upload kodu (`$_FILES` / `move_uploaded_file`),
  SQL INSERT/UPDATE kalıbı ve `<img src>` prefix analizi yapılamadı.
- Kanıt satırı yok (kaynak yok). Format sınıflandırması: **N-A**.
- `product_images.image_path VARCHAR(500)` hedef kolonu schema'da mevcut
  (satır 63); değer formatı Natro manuel incelemesinde doğrulanmalıdır.

## 3. IMAGE FILE INVENTORY: NOT FOUND

- `akanbest-website\` altında recursive tarama (jpg/jpeg/png/webp/gif): **0 dosya**.
- Klasör/uzantı tablosu ve örnek ad listesi üretilemedi.
- Sonuç: **"IMAGE FILES LOCAL: NOT FOUND"**.

## 4. MATCH ANALYSIS

- DB değerleri okunabildi mi: **NO** (DB erişimi yok; SELECT dahil çalıştırılmadı).
- Kod tabanlı örtüşme: **yok** — karşılaştırılacak legacy upload klasörü ve
  dosya adı deseni lokalde mevcut değil.

## 5. kategori RELATION

- Legacy kodda `urun.kategori` JOIN/WHERE kanıtı: **yok** (kaynak yok).
- Yeni sistem statik ilişkisi (schema.sql satır 57):
  `products.category_id → categories.id ON DELETE RESTRICT`.
- Legacy kategori adlarının yeni `categories.id` değerlerine eşlenmesi için
  **mapping tablosu gerekir** (M5-B insan görevi).

## 6. NEW SYSTEM MAPPING (schema.sql gerçek kolon adlarıyla)

| Legacy alan | Hedef | Not |
|---|---|---|
| `urun.baslik` | `products.name` (satır 43, VARCHAR(255) NOT NULL) | TR only |
| `urun.icerik` | `products.long_description` (satır 45, LONGTEXT NULL) | plain-text; WYSIWYG yok (M6) |
| `urun.kategori` | `categories.id` → `products.category_id` (satır 40, INT UNSIGNED NOT NULL) | mapping tablosu gerekir |
| `urun.resim` / `resim2` | `product_images.image_path` (satır 63) + `is_primary` (satır 65) + `sort_order` (satır 66) | max 2; `resim`→primary(1), `resim2`→secondary(0); format Natro'da doğrulanacak |
| `urun.sira` | `products.sort_order` (satır 50, INT DEFAULT 0) | doğrudan |
| `fiyat` / `enfiyat` / `frfiyat` / `indirim` | karşılık yok / doğrulama gerekli | Yeni sistemde fiyat kolonu yok (teklif modeli); atlanır |
| `enbaslik` / `frbaslik` | karşılık yok / doğrulama gerekli | Çoklu dil yok (TR only); atlanır |
| legacy slug/kod alanı | `products.slug` (satır 41, UNIQUE) / `products.code` (satır 42, NULL+INDEX) | Legacy slug formatı bilinmiyor; `Slug::generate()` + duplicate kontrolü gerekir |
| legacy SEO alanları | `products.short_description` (satır 44) / `specs_json` (satır 46) / `variants_json` (satır 47) | Karşılık yok / doğrulama gerekli — legacy içerik yapısı Natro'da incelenmeli |

Riskler: legacy encoding bozukluğu (TR karakterler), slug çakışmaları
(UNIQUE `uk_products_slug`), `category_id` NOT NULL olduğu için eşleşemeyen
kategorilerde import durmalıdır.

## 7. DB TESTS

NOT RUN / BLOCKED (local cannot access Natro DB) | mock: NO.

## 8. VERDICT: C

Gerekçe: kökte legacy PHP kodu yok (kök-dışı *.php: 0), `akanbest-website\`
içinde legacy kalıntısı yok, lokal görsel dosyası yok (0). `resim/resim2`
formatı ve kategori eşleşmesi lokal kaynaklardan kesin tespit edilemedi.
Kanıt özeti: tarama kökü doğrulandı, 34 yeni-sistem dosyası listelendi,
6 anahtar kelime tarandı, 5 görsel uzantısı tarandı (0 sonuç).

## 9. NEXT STEP INPUTS (M5-B için insan görevleri)

1. Natro cPanel File Manager'da legacy upload klasörünü tespit et
   (adaylar: `uploads/`, `resimler/`, `images/`); tam yolu ve dosya sayısını not et.
2. phpMyAdmin'de legacy tablo yapısını dışa aktar (sadece yapı):
   `SHOW CREATE TABLE urun;` çıktısı (sütun adları + tipleri).
3. Örnek SELECT (ilk 3 satır, `resim`/`resim2`/`kategori` değerleri görünsün):
   `SELECT id, baslik, kategori, resim, resim2, sira FROM urun LIMIT 3;`
4. `resim`/`resim2` değerlerinin tam metnini kopyala (filename mi, relative path mi,
   full URL mi belli olsun).
5. Legacy `kategori` tablosu varsa yapısını + 10 satır örneğini al
   (mapping tablosu için).
6. Kök dışı olasılık: bu makinede başka dizinde legacy yedek/FTP kopyası varsa
   tam yolunu bildir (kök kendiliğinden genişletilmedi).
