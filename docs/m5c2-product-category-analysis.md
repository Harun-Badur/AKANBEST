# M5-C.2 — Minimal Legacy Mapping Check (urunler.php + detay.php only)

Kapsam: Yalnızca `tools/legacy-source/urunler.php` (6.470 B) ve `tools/legacy-source/detay.php` (6.249 B) incelendi.
DB bağlantısı, SQL çalıştırma, dosya değiştirme, başka dosya tarama, commit/push: **yapılmadı**.

---

## 1. Bulunan ilişkiler

### F-1 — Legacy ürün listesi `urun` tablosunu DEĞİL, `yazi` tablosunu sorguluyor (Kritik)
`urunler.php` satır 53 ve 105, kategori filtresini `select * from yazi where kategori=...` ile yapıyor; her iki dosyada `urun` tablosuna yapılan sorgu sayısı **0**'dır.
Bu, `urun.kategori → kategori.id` hedef sorusunun ön kabulünün geçersiz olabileceğini gösterir: legacy arayüz `urun` verisini hiç kullanmıyor.

### F-2 — `kid` parametresi `kategori.id`dir (aynı dosya içi kanıt)
`urunler.php` satır 74–79 menüyü `select * from kategori order by sira asc` ile üretip bağlantıyı `?kid=<?=$kk["id"]?>` olarak kuruyor; yani `kid` doğrudan `kategori.id` (53–64) değeridir.
Aynı dosyanın satır 53/105'i bu `kid`i `yazi.kategori` ile karşılaştırdığı için **`yazi.kategori` = `kategori.id`** ilişkisi kanıtlanmıştır.

### F-3 — Detay sayfası da `yazi` üzerinden çalışıyor
`detay.php` satır 18 ve 143, kaydı `select * from yazi where id=...` ile çekiyor; `urun` tablosu burada da hiç sorgulanmıyor.
Dolayısıyla detay sayfasındaki kategori bağlamı `$sayfa2["kategori"]` (yani `yazi.kategori`) üzerinden yürüyor (satır 22).

### F-4 — Görsel ayracı virgül ve yol öneki `uploads/` (aynı dosya kanıtı)
`detay.php` satır 145 `explode(",",$yazi["resim"])` ile virgül ayracını doğruluyor; satır 149 görseli `uploads/<?=$rex[$i]?>` biçiminde basıyor.
Bu, `yazi.resim` için ayraç ve yol biçimini kanıtlar; `urun.resim` hakkında kanıt üretmez.

### F-5 — Kategori menüsü sıralaması `sira` ile
`urunler.php` satır 74, 155, 186 ve `detay.php` satır 80 menüyü `order by sira asc` ile sıralıyor; `kategori.sira` yalnızca **sıralama** amacıyla kullanılıyor.
`kategori.sira` ile herhangi bir `kategori`/`urun` eşleştirmesi kuran `WHERE` ifadesi bu iki dosyada **yok**.

### F-6 — `urun.kategori` için kaynak kullanımı sıfır
İki dosyanın tamamında `urun.kategori`, `JOIN kategori`, `WHERE kategori` (tablo önekli) ifadeleri **0** kez geçiyor.
Bu nedenle `urun.kategori → kategori.id` eşleşmesi bu dosyalarla **doğrulanamaz**.

## 2. Dosya / satır

| Bulgu | Dosya | Satır | Kod (kısa) | Durum |
|---|---|---|---|---|
| F-1 | `urunler.php` | 53 | `select * from yazi where kategori=".k1($_GET["kid"])." order by sira asc` | CONFIRMED (yazi kullanımı) |
| F-1 | `urunler.php` | 105 | `select * from yazi where kategori=".k1($_GET["kid"])." order by sira asc` | CONFIRMED (yazi kullanımı) |
| F-2 | `urunler.php` | 74, 79 | `select * from kategori order by sira asc` + `?kid=<?=$kk["id"]?>` | CONFIRMED (`kid`=`kategori.id`) |
| F-3 | `detay.php` | 18 | `select * from yazi where id=".$id` | CONFIRMED (yazi kullanımı) |
| F-3 | `detay.php` | 143 | `select * from yazi where id=".k1($_GET["id"])` | CONFIRMED (yazi kullanımı) |
| F-3 | `detay.php` | 22 | `$kik = $sayfa2[$_SESSION["dil"]."kategori"]` | CONFIRMED (kategori bağlamı yazi'dan) |
| F-4 | `detay.php` | 145, 149 | `explode(",",$yazi["resim"])` / `uploads/<?=$rex[$i]?>` | CONFIRMED (virgül + yol) |
| F-5 | `urunler.php` | 74, 155, 186 | `order by sira asc` | CONFIRMED (yalnızca sıralama) |
| F-5 | `detay.php` | 80 | `order by sira asc` | CONFIRMED (yalnızca sıralama) |
| F-6 | her iki dosya | — | `urun` tablosu sorgusu yok | CONFIRMED (yokluk kanıtı) |

## 3. CONFIRMED / UNRESOLVED

### CONFIRMED
| İlişki | Kanıt | Güven |
|---|---|---|
| `yazi.kategori` = `kategori.id` (53–64) | `urunler.php` 74/79 → 53/105 | **CONFIRMED** |
| Legacy ürün liste/detay kaynağı = `yazi` tablosu | `urunler.php` 53/105, `detay.php` 18/143 | **CONFIRMED** |
| `kategori.sira` yalnızca sıralama alanı | `urunler.php` 74/155/186, `detay.php` 80 | **CONFIRMED** |
| `resim` alanı virgülle ayrılı, yol `uploads/<dosya>` | `detay.php` 145/149 | **CONFIRMED** |
| `urun` tablosu bu sayfalarda kullanılmıyor | her iki dosya, 0 sorgu | **CONFIRMED** |

### UNRESOLVED (hedef mapping)
| Legacy `urun.kategori` | Legacy `kategori.id` | Kanıt | Güven |
|---|---:|---|---|
| 1 | ? | `urun` tablosu bu dosyalarda hiç sorgulanmıyor | **UNRESOLVED** |
| 2 | ? | aynı | **UNRESOLVED** |
| 3 | ? | aynı | **UNRESOLVED** |
| 9 | ? | aynı | **UNRESOLVED** |
| 10 | ? | aynı | **UNRESOLVED** |
| 11 | ? | aynı | **UNRESOLVED** |

**CONFIRMED sayısı: 0 / 6** (hedef `urun.kategori → kategori.id` için).
**Ek not:** Hedef soru `urun` tablosu varsayımına dayanıyor; kanıt, legacy arayüzün `yazi` tablosuyla çalıştığını gösteriyor.

## 4. Gerekli olabilecek eksik dosya

- **`inc/fonksiyon.php`** — `k1()` (SQL kaçış/doğrulama) ve `klasor()` (dil yolu üretimi) fonksiyonlarını içerir; `urunler.php` 2/53/79/105, `detay.php` 2/12/86/143 satırlarında çağrılıyor.
- **`inc/config.php`** — `urunler.php` 1, `detay.php` 1 satırlarında include ediliyor (DB bağlantısı).
- **`header.php`, `footer.php`** — `urunler.php` 34/223, `detay.php` 49/212 satırlarında include ediliyor (menü/görsel bağlamı için).

Bu dosyalar bu görevde **aranmadı** ve **okunmadı** (kural gereği).

## 5. Verdict

**PASS** — İki dosya istendiği gibi incelendi; hedef `urun.kategori → kategori.id` eşleşmesi için **kaynak kod kanıtı bulunamadı (0/6 CONFIRMED)**.
Buna karşılık `yazi.kategori = kategori.id` ilişkisi ve `urun` tablosunun bu sayfalarda kullanılmadığı **kanıtlandı**; bu, M5-C kapsamındaki hedef varsayımın gözden geçirilmesini gerektirir.

---
Kısıt uyumu: DB bağlantısı YOK · SQL çalıştırma YOK · dosya değiştirme YOK · başka dosya tarama YOK · commit/push YOK.