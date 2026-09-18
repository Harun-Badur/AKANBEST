# M5-B Import Plan (PLAN ONLY — import yok, SQL çalıştırma yok, dosya taşıma yok)

Kapsam: legacy `urun` + `kategori` → yeni sistem (`categories`, `products`,
`product_images`). Bu doküman plandır; çalıştırılabilir script içermez.
Hedef kolon adları `database/schema.sql` dosyasından birebir alınmıştır.
Doğrulanmış gerçekler V1–V7, doğrulanmamış girdiler U1–U7 olarak işaretlidir.

## 0. Terminoloji ve ilkeler

- `legacy_id`: `urun.id` (izlenebilirlik anahtarı; yeni `id` ile eşleşmez).
- Uydurma veri YASAK: karşılığı doğrulanmamış alan boş/null bırakılır.
- Otomatik birleştirme YASAK: şüpheli kayıt MANUAL REVIEW'e düşer.
- Tekrar çalıştırılabilirlik: aynı girdi → aynı çıktı (deterministic, random yok).

## 1. CATEGORY MAPPING (legacy `kategori` → `categories`)

Hedef kolonlar (schema.sql satır 20–36): `id` AUTO, `parent_id` NULL,
`slug` VARCHAR(191) UNIQUE NOT NULL, `name` VARCHAR(255) NOT NULL,
`description` TEXT NULL, `seo_title` NULL, `seo_description` NULL,
`sort_order` INT DEFAULT 0, `is_active` TINYINT(1) DEFAULT 1.

| Durum | Kural |
|---|---|
| Aynı isimli (temiz) | `name` = düzeltilmiş ad; `slug` = bölüm 6 kuralı; `description` = NULL; `sort_order` = legacy sıra varsa o, yoksa 0; `parent_id` = U1 doğrulanana kadar NULL |
| Encoding bozuk ad | Önce bölüm 5 encoding kuralı uygulanır, sonra aynı-isimli kuralı |
| Yeni sistemde karşılığı olmayan ad | Yeni satır açılır (silinmez, birleştirilmez) |
| Legacy'de kaldırılan | Yeni sistemde silme YOK; dokunulmaz |
| Çoklu legacy → tek yeni | Otomatik birleştirme YOK; MANUAL REVIEW listesine yazılır |
| Eşleşmeyen | MANUAL REVIEW; `products.category_id` NOT NULL olduğu için eşleşmeyen kategorinin ürünü import edilmez |

`kategori` kaynak kolonları (ad, sıra, parent, resim): **DOĞRULANMADI** (U1).
`seo_title` / `seo_description`: legacy karşılığı YOK → NULL kalır.
`parent_id`: U1 (parent yapısı) doğrulanana kadar tüm yeni kategoriler kök
seviyede açılır; hiyerarşi M5-C sonrası insan kararıyla kurulur.

## 2. PRODUCT MAPPING (`urun` → `products`)

Hedef kolonlar (schema.sql satır 38–58): `id` AUTO, `category_id` NOT NULL,
`slug` VARCHAR(191) UNIQUE NOT NULL, `code` VARCHAR(100) NULL,
`name` VARCHAR(255) NOT NULL, `short_description` TEXT NULL,
`long_description` LONGTEXT NULL, `specs_json` JSON NULL,
`variants_json` JSON NULL, `is_active` 1, `is_featured` 0, `sort_order` 0.

| Legacy alan | Hedef | Kural |
|---|---|---|
| `baslik` (encoding düzeltilmiş, trim) | `name` | Boşsa MANUAL REVIEW, import edilmez |
| — (türetilir) | `slug` | Bölüm 6 kuralı |
| — | `code` | Bölüm 7 kuralı (öneri NULL) |
| `icerik` (encoding düzeltilmiş) | `long_description` | HTML ise olduğu gibi saklanır; WYSIWYG yok (M6); strip YOK |
| — | `short_description` | Legacy karşılığı YOK → NULL |
| `kategori` | `category_id` | Bölüm 1 mapping tablosuyla; eşleşmezse MANUAL REVIEW |
| `sira` | `sort_order` | Sayısal değilse 0 + MANUAL REVIEW notu |
| — | `specs_json`, `variants_json` | Legacy karşılığı YOK → NULL, uydurma YOK |
| — | `is_active` | 1 (varsayılan); pasif işareti doğrulanmadı |
| — | `is_featured` | 0 (varsayılan) |
| `fiyat`, `enfiyat`, `frfiyat`, `indirim` | **IMPORT EDİLMEYECEK** | Yeni şemada fiyat kolonu yok (V7); akıbet KARAR NOKTASI 13.5 |
| `enbaslik`, `frbaslik` | **IMPORT EDİLMEYECEK** | TR only (V7) |

## 3. IMAGE MAPPING (`resim` + `resim2` → `product_images`)

Hedef kolonlar (schema.sql satır 60–72): `product_id` NOT NULL,
`image_path` VARCHAR(500) NOT NULL, `alt_text` NULL, `is_primary` DEFAULT 0,
`sort_order` DEFAULT 0.

1. `resim` ve `resim2` değerleri `,` karakterinden bölünür; her parça trim edilir;
   boş parçalar atılır (V4).
2. Her dosya adı için ayrı `product_images` satırı açılır.
3. `image_path` stratejisi: `'uploads/<dosyaadi>'` (yeni docroot altındaki
   `public/uploads/` karşılığı; fiziksel kopyalama M5-C insan adımıdır).
4. `alt_text` = ürün adı (encoding düzeltilmiş).
5. Eşleşme kuralı: dosya adı U5 listesindeki (uploads/ CSV) adla birebir
   (case-sensitive) eşleşmelidir.
6. Dosyası bulunamayan görsel için satır açılMAZ; ürün "eksik görselli" olarak
   MANUAL REVIEW'e düşer. **404 üreten path KABUL EDİLEMEZ.**
7. Beklenmeyen uzantı (jpg/jpeg/png/webp/gif dışı) → MANUAL REVIEW.

## 4. PRIMARY IMAGE

1. Ürünün mevcut (dosyası doğrulanan) görsel listesi `resim`→`resim2` sırasıyla
   dizilir; `,` içi sıra korunur.
2. Listedeki ilk mevcut görsel `is_primary=1`, sonrakiler `is_primary=0` alır.
3. `sort_order`: listedeki sıra (0, 1, 2…).
4. Hiçbir görseli bulunamayan ürün: `product_images` satırı açılmaz, ürün
   MANUAL REVIEW'e düşer (görselsiz ürün 404 üretmez).

## 5. ENCODING

Gözlemlenen mojibake imzaları (V5): `Ä±` (ı), `ÄŸ` (ğ), `Ã‡` (Ç),
`Ã¼` (ü), `Ã¶` (ö), `ÅŸ` (ş) — UTF-8 baytlarının ISO-8859-1 okunması hipotezi.

Kural (round-trip doğrulamalı, tahmin YOK):

1. Metinde mojibake imzası YOKSA → metin olduğu gibi alınır.
2. İmza VARSA → aday düzeltme = baytları ISO-8859-1 kabul edip UTF-8'e çevir;
   geri dönüşüm orijinali **birebir** üretiyorsa düzeltme uygulanır.
3. Round-trip birebir değilse → **MANUAL REVIEW**; tahmin YASAK.
4. Kural `baslik`, `icerik` ve kategori adlarına aynı şekilde uygulanır.

## 6. SLUG

1. Taban: mevcut `app/Core/Slug.php` → `Slug::generate()` (TR transliterasyon).
2. Sıra: legacy_id ascending; ilk kullanımda taban slug aynen alınır.
3. Çakışma: taban + `-` + `legacy_id` (örn. `koli-bandi-47`). Random YOK.
4. Aynı girdi her zaman aynı çıktı (idempotent, tekrar çalıştırılabilir).
5. İzlenebilirlik tablosu M5-C girdisidir: `legacy_id → planlanan slug`.
6. Kategori slug'ları için aynı kural `Category::slugExists()` API'siyle uygulanır.

## 7. CODE

Öneri: **NULL** (`products.code` NULLABLE, schema.sql satır 42).
Gerekçe: legacy'de doğrulanmış kod karşılığı yok; uydurma kod izlenebilirliği bozar.
Deterministic fallback (`AB-{legacy_id}`) yalnızca **İNSAN ONAYI** ile
kullanılır → **KARAR NOKTASI** (bölüm 13.2). Bu planda seçim yapılmaz.

## 8. DUPLICATE DETECTION (otomatik birleştirme YOK)

| Sinyal | Eşik | Aksiyon |
|---|---|---|
| Aynı `baslik` (encoding-normalize), farklı `id` | ≥2 satır | MANUAL REVIEW |
| Aynı dosya adı, farklı ürün | ≥2 ürün | MANUAL REVIEW |
| Aynı ürün adı, farklı `kategori` | ≥2 satır | MANUAL REVIEW |

Tespit dry-run metriğidir (bölüm 10); karar insana aittir.

## 9. MISSING DATA RULES (import edilmez / MANUAL REVIEW)

1. `name` boş (trim sonrası) → import edilmez.
2. `kategori` eşleşmiyor → ürün import edilmez (`category_id` NOT NULL).
3. Hiçbir görsel dosyası bulunamıyor → ürün görselsiz import edilir + REVIEW.
4. Slug çakışması bölüm 6 ile çözülemiyorsa → REVIEW.
5. `code` çakışması (fallback seçilirse) → REVIEW.
6. Encoding round-trip başarısız → REVIEW.
7. `icerik` bozuk/şüpheli → ürün import edilir, içerik REVIEW notuyla.
8. Beklenmeyen resim formatı → o görsel atlanır + REVIEW.
9. Geçersiz karakter (kontrol karakterleri) → temizlenemezse REVIEW.
10. Duplicate şüphesi (bölüm 8) → REVIEW.

## 10. DRY-RUN SPEC (metrikler + READ-ONLY sorgu metinleri)

Üretilecek metrikler: legacy ürün sayısı, kategori sayısı, eşleşen/eşleşmeyen
ürün, mevcut/eksik görsel, slug çakışması, duplicate şüphesi, encoding problemi,
MANUAL REVIEW toplamı.

Aşağıdaki sorgular **çalıştırılmaz**; M5-C'de insanın koşacağı metindir:

```sql
SELECT COUNT(*) AS urun_sayisi FROM urun;
SELECT COUNT(*) AS kategori_sayisi FROM kategori;
SELECT id, baslik, kategori, resim, resim2, sira FROM urun ORDER BY id LIMIT 10;
SELECT DISTINCT kategori FROM urun ORDER BY kategori;
SELECT k.id FROM kategori k
LEFT JOIN urun u ON u.kategori = k.id
WHERE u.id IS NULL;
SELECT id, baslik FROM urun WHERE TRIM(COALESCE(baslik, '')) = '';
SELECT id, baslik, resim, resim2 FROM urun
WHERE TRIM(COALESCE(resim, '')) = '' AND TRIM(COALESCE(resim2, '')) = '';
SELECT baslik, COUNT(*) AS c FROM urun GROUP BY baslik HAVING c > 1;
```

## 11. M5-C PIPELINE + GİRDİLER

1. **Export (insan, read-only):** U4–U5 CSV'leri phpMyAdmin'den alınır; yazma yok.
2. **Lokal transform (Cline, DB'siz):** CSV → bu plandaki kurallarla import SQL
   metni üretilir; SQL çalıştırılmaz.
3. **İnsan incelemesi:** SQL + izlenebilirlik tablosu + REVIEW listesi mimar
   onayından geçer.
4. **Uygulama (insan):** phpMyAdmin'de tek transaction; hata halinde ROLLBACK.
5. **Doğrulama (insan):** satır sayıları + bölüm 10 sorguları + örnek sayfa
   görüntüleme (404 kontrolü).

M5-C ön koşulları (U1–U7): kategori kolonları + 10 örnek; satır sayıları;
distinct kategori adları; 10 ham resim değeri + boş davranışı; uploads CSV +
orphan bilgisi; çift-boş görsel satır varlığı; `kategori.resim` kullanımı.

## 12. SECURITY/SAFETY

- Credential (parola, DB adı/kullanıcısı, bağlantı dizesi) kod veya dokümana
  YAZILMAZ; parola okunmaz/loglanmaz.
- Production write bu planda YOK; M5-C uygulaması staging-sonrası onaya tabidir.
- Yikici yapi-degistiren/silen SQL komutlari planin hicbir yerinde YOK
  (ne metin olarak komut, ne calistirilabilir icerik).
- Import/migration ÇALIŞTIRILMAZ; dosya taşıma/kopyalama/silme YOK.

## 13. KARAR NOKTALARI

1. `kategori.resim` kullanımı doğrulanırsa hedefsiz kalır (`categories` görsel
   kolonu YOK — schema.sql satır 20–36) → kategori görselleri import dışı +
   mimar onayı gerekir.
2. `code` stratejisi: NULL (öneri) vs `AB-{legacy_id}` fallback → insan seçimi.
3. Kategori parent yapısı bilinmiyor (U1) → ilk import düz liste; hiyerarşi sonraya.
4. Orphan görseller (U5): kopyalanır mı, silinir mi, arşivlenir mi → insan kararı.
5. Fiyat verisinin akıbeti: arşiv CSV olarak saklansın mı, tamamen atılsın mı?

## EK-A. HEDEF KOLON ÇAPRAZ REFERANSI (schema.sql birebir)

categories.slug, categories.name, categories.description, categories.sort_order,
categories.parent_id, categories.seo_title, categories.seo_description,
categories.is_active, products.name, products.slug, products.code,
products.short_description, products.long_description, products.specs_json,
products.variants_json, products.category_id, products.sort_order,
products.is_active, products.is_featured, product_images.image_path,
product_images.is_primary, product_images.sort_order, product_images.alt_text,
product_images.product_id.
HEDEF-DIŞI (schema'da yok): categories.image_path YOK → kategori.resim hedefsiz.
