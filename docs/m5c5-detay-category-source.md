# M5-C.5 — detay.php CATEGORY SOURCE CHECK

Kapsam: Yalnızca `tools/legacy-source/detay.php` (6.249 B, 213 satır) okundu.
DB bağlantısı, SQL çalıştırma, dosya değiştirme, başka dosya okuma, commit/push: **yapılmadı**.

---

## 1. Arama kanıtı (tam liste)

| Aranan dize | Eşleşme | Satırlar |
|---|---:|---|
| `onceki_sonraki_urun` | **0** | — |
| `kategori` | 2 | 22, 80 |
| `$kategori` | **0** | — |
| `from urun` | **0** | — |
| `from yazi` | 2 | 18, 143 |
| `$_GET` | 3 | 12, 17, 143 |
| toplam satır | — | 213 |

**Kesin sonuç:** `detay.php` içinde `onceki_sonraki_urun()` çağrısı **YOKTUR (0 eşleşme)**.
Bu nedenle fonksiyona gönderilen bir `$kategori` değeri de yoktur ve takip edilemez.

## 2. `detay.php`nin kategori ile ilgili tüm kullanımları

| Satır | Kod (birebir) | Anlam |
|---|---|---|
| 18 | `$sayfa2=mysql_fetch_array(mysql_query("select * from yazi where id=".$id));` | Detay kaydı **`yazi`** tablosundan çekilir (id ile) |
| 22 | `$kik = $sayfa2[$_SESSION["dil"]."kategori"];` | `yazi.kategori` (dil önekli) `$kik` değişkenine atanır |
| 80 | `<? $kks=mysql_query("select * from kategori order by sira asc");` | Sol menü: **`kategori`** tablosu, yalnızca sıralama ile |
| 86 | `<a href="urunler.php<?=klasor();?>?kid=<?=$kk["id"]?>">` | Menü linki: `kid` = `kategori.id` |
| 143 | `$yazi=mysql_fetch_array(mysql_query("select * from yazi where id=".k1($_GET["id"])));` | Görseller için kayıt yine **`yazi`** tablosundan |
| 145 | `$rex = explode(",",$yazi["resim"]);` | Görsel ayracı **virgül** (kanıt) |
| 149 | `<picture><img src="uploads/<?=$rex[$i]?>" ...>` | Görsel yolu öneki **`uploads/`** (kanıt) |

## 3. `$kategori` / hedef mapping durumu

### CONFIRMED
| İfade | Kanıt |
|---|---|
| `detay.php` içinde `onceki_sonraki_urun()` çağrısı **yok** | 0 eşleşme (tüm dosya tarandı) |
| `detay.php` kaydı **`yazi`** tablosundan okur, `urun` tablosundan değil | satır 18, 143; `from urun` 0 eşleşme |
| `detay.php` ürün detay kaydının kategori değerini **`yazi.kategori`**den alır | satır 22 |
| Sol kategori menüsü `kategori` tablosundan, `kid` = `kategori.id` olarak üretilir | satır 80, 86 |
| Görsel ayracı **virgül**, yol öneki **`uploads/`** | satır 145, 149 |

### UNRESOLVED
| Hedef | Neden |
|---|---|
| `urun.kategori (1,2,3,9,10,11) → kategori.id (53–64)` | Bu dosyada `urun` tablosu hiç sorgulanmıyor; `$kategori` değişkeni yok |
| `$kategori`nin kaynağı | Fonksiyon çağrısı bu dosyada yok → takip edilemez |
| `yazi.kategori` → `kategori.id` | Satır 22 değeri `$kik`e atanıyor ancak `$kik` **başka hiçbir satırda kullanılmıyor** (ölü değişken); ilişki kurulmuyor |

**Hedef mapping için CONFIRMED sayısı: 0 / 6.**

## 4. Ölü değişken bulgusu

- `$kik` (satır 22) yalnızca **1 kez** geçmektedir (atama satırı). Dosyanın kalanında hiç kullanılmadığı için `yazi.kategori`nin arayüzde nasıl tüketildiği **bu dosyadan anlaşılamaz**.

## 5. Verdict

**UNRESOLVED** — `detay.php` içinde `onceki_sonraki_urun()` çağrısı bulunmamaktadır (0 eşleşme) ve `$kategori` değişkeni hiç geçmemektedir; dolayısıyla kategori parametresinin kaynağı **bu dosyadan kanıtlanamaz**.
Ek olarak `detay.php` de `urun` tablosunu kullanmaz (0 sorgu); veriyi `yazi` tablosundan okur. Bu, `urun.kategori → kategori.id` hedefinin varsayımını daha da zayıflatır.

---
Kısıt uyumu: DB bağlantısı YOK · SQL çalıştırma YOK · dosya değiştirme YOK · başka dosya okuma YOK · tahmin YOK · commit/push YOK.