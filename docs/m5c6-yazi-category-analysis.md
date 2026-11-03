# M5-C.6 — YAZI/KATEGORI RELATION CHECK

Kapsam: Yalnızca `urunler.php` (224 satır), `detay.php` (213 satır), `fonksiyon.php` (400 satır) okundu.
DB bağlantısı, SQL çalıştırma, dosya değiştirme, başka dosya okuma, commit/push: **yapılmadı**.

---

## 1. Arama kanıtı (özet tablo)

| Dize | urunler.php | detay.php | fonksiyon.php |
|---|---:|---:|---:|
| `yazi` | 2 | 3 | **0** |
| `kategori.id` (nokta notasyonu) | 0 | 0 | 0 |
| `kid` | 6 | 1 | 0 |
| `WHERE kategori` / `where kategori` | 2 | 0 | 5 |
| `JOIN kategori` / `join kategori` | **0** | **0** | **0** |

**Not:** `kategori.id` nokta notasyonu hiçbir dosyada geçmez; ilişki `$kk["id"]` (dizi erişimi) üzerinden kurulur.

## 2. Bulunan ilişki — ZİNCİR KANITLANDI ✅

### Halka 1 — `kategori.id` değeri (`$kk["id"]`) `kategori` tablosundan gelir

| Dosya | Satır | Kod (birebir) |
|---|---|---|
| `urunler.php` | 74 | `<? $kks=mysql_query("select * from kategori order by sira asc");` |
| `urunler.php` | 75 | `while ($kk=mysql_fetch_array($kks)) {` |
| `detay.php` | 80 | `<? $kks=mysql_query("select * from kategori order by sira asc");` |
| `detay.php` | 81 | `while ($kk=mysql_fetch_array($kks)) {` |

→ `$kk`, **`kategori`** tablosunun satırıdır; `$kk["id"]` = **`kategori.id`**.

### Halka 2 — `kategori.id` → URL parametresi `kid`

| Dosya | Satır | Kod (birebir) |
|---|---|---|
| `urunler.php` | 79 | `<a href="<?=klasor();?>?kid=<?=$kk["id"]?>" class="nav-item-inner-heading plus ">` |
| `urunler.php` | 160 | `<a href="<?=klasor();?>?kid=<?=$kk["id"]?>" class="nav-item-inner-heading plus ">` |
| `urunler.php` | 191 | `<a href="<?=klasor();?>?kid=<?=$kk["id"]?>">` |
| `detay.php` | 86 | `<a href="urunler.php<?=klasor();?>?kid=<?=$kk["id"]?>" class="nav-item-inner-heading plus active-nav">` |

→ `kid` parametresi **doğrudan `kategori.id`** değerini taşır.

### Halka 3 — `kid` → `yazi.kategori` filtresi

| Dosya | Satır | Kod (birebir) |
|---|---|---|
| `urunler.php` | 52 | `if ((is_numeric($_GET["kid"])) and ($_GET["kid"]!="")) {` |
| `urunler.php` | 53 | `$urun=mysql_fetch_array(mysql_query("select * from yazi where kategori=".k1($_GET["kid"])." order by sira asc "));` |
| `urunler.php` | 105 | `<? $kks=mysql_query("select * from yazi where kategori=".k1($_GET["kid"])." order by sira asc ");` |

→ `$_GET["kid"]` değeri **`yazi.kategori`** kolonuyla `WHERE` koşulunda karşılaştırılır.

## 3. Zincir özeti

```
kategori tablosu ($kk)
   └─ $kk["id"]            → kategori.id        [urunler.php:74/75, detay.php:80/81]
        └─ ?kid=$kk["id"]  → URL parametresi kid [urunler.php:79/160/191, detay.php:86]
             └─ $_GET["kid"]  (is_numeric ile doğrulanır)  [urunler.php:52]
                  └─ yazi WHERE kategori=$_GET["kid"]      [urunler.php:53, 105]
```

**Sonuç:** `kategori.id → kid → yazi.kategori` zinciri **eksiksiz ve doğrudan kaynak kodla kurulmuştur**.

## 4. CONFIRMED / UNRESOLVED

### CONFIRMED
| İfade | Kanıt (dosya:satır) | Güven |
|---|---|---|
| `kategori.id` → `kid` parametresi | `urunler.php:74,75,79` + `160,191`; `detay.php:80,81,86` | **CONFIRMED** |
| `kid` → `yazi.kategori` WHERE filtresi | `urunler.php:52,53,105` | **CONFIRMED** |
| **`yazi.kategori` = `kategori.id`** (zincir sonucu) | `urunler.php:79` → `53`/`105` (aynı dosya, doğrudan) | **CONFIRMED** |
| `kid` sayısal doğrulamadan geçer (`is_numeric`) | `urunler.php:52` | **CONFIRMED** |
| `JOIN kategori` hiç kullanılmaz (ilişki JOIN'siz, WHERE ile) | 3 dosyada 0 eşleşme | **CONFIRMED** |

### UNRESOLVED
| İfade | Neden | Durum |
|---|---|---|
| `urun.kategori` = `kategori.id` | `urun` tablosu bu 3 dosyada yalnızca `fonksiyon.php:241/243/248/250`de geçer; oradaki `$kategori` **fonksiyon parametresidir**, kaynağı bu dosyalarda yok | **UNRESOLVED** |
| Hedef mapping `1,2,3,9,10,11 → 53–64` | Yukarıdaki nedenle | **UNRESOLVED (0/6)** |
| `urun.kategori` = `yazi.kategori` | Bu iki alanı birbirine bağlayan hiçbir satır yok | **UNRESOLVED** |

**Kritik ayrım:** `yazi.kategori = kategori.id` **CONFIRMED**; `urun.kategori = kategori.id` **UNRESOLVED**. Bu ikisi aynı şey değildir.

## 5. Eksik dosya

- **Bu zincir için eksik dosya YOKTUR** — `kategori.id → kid → yazi.kategori` ilişkisi mevcut 3 dosyayla tam olarak kanıtlanmıştır.
- `urun.kategori` sorusu (M5-C.4/C.5'ten devam eden) için gereken **tek dosya**: `onceki_sonraki_urun()` fonksiyonunu çağıran ve `$kategori` argümanını üreten dosya — bu 3 dosyada **mevcut değildir**. Aday: legacy kökteki sayfa şablonları (bu görevde okunmadı, aranmadı).

## 6. Verdict

**PASS — CONFIRMED (zincir için)**

`kategori.id → kid → yazi.kategori` zinciri **doğrudan kaynak kod kanıtıyla CONFIRMED** edilmiştir (5 bağımsız satır kanıtı, JOIN'siz WHERE ilişkisi).
Buna karşılık `urun.kategori → kategori.id` hedef eşleşmesi **UNRESOLVED** kalır (0/6); `urun` tablosu incelenen sayfalarda kullanılmamaktadır.

---
Kısıt uyumu: yalnızca 3 dosya okundu · DB bağlantısı YOK · SQL çalıştırma YOK · dosya değiştirme YOK · tahmin YOK · ürün isminden/sira'dan mapping çıkarımı YOK · commit/push YOK.