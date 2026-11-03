# M5-C.4 — onceki_sonraki_urun CALLER CHECK

Kapsam: Yalnızca `tools/legacy-source/fonksiyon.php` (8.866 B, 400 satır) okundu.
DB bağlantısı, SQL çalıştırma, dosya değiştirme, başka dosya okuma, commit/push: **yapılmadı**.

---

## 1. Arama kanıtı

| Aranan dize | Eşleşme sayısı | Satırlar |
|---|---:|---|
| `onceki_sonraki_urun` | **1** | 238 |
| `$kategori` | 9 | 170, 173, 190, 238, 241, 243, 248, 250 |

**Sonuç:** `onceki_sonraki_urun` dosyada **yalnızca tanım satırında (238)** geçmektedir; bu dosyada **hiçbir çağrı (call site) yoktur**.
Bu nedenle `$kategori` parametresinin gerçek değer kaynağı **bu dosyadan belirlenemez**.

## 2. Bulunan tanım ve gövde

| Satır | Kod (kısa) |
|---|---|
| 238 | `function onceki_sonraki_urun($id,$oncekisonraki,$sira,$kategori) {` |
| 240 | `if ($oncekisonraki=="sonraki") {` |
| 241 | `select id from urun where kategori=$kategori and sira > $sira order by sira asc limit 1` |
| 243 | `select id from urun where kategori=$kategori order by sira asc limit 1` |
| 247 | `} else if ($oncekisonraki=="onceki")  {` |
| 248 | `select id from urun where kategori=$kategori and sira < $sira order by sira desc limit 1` |
| 250 | `select id from urun where kategori=$kategori order by sira desc limit 1` |
| 252 | `return $urun["id"];` |
| 254 | `}` |

Dört parametre: `$id` (ürün id, gövdede **kullanılmıyor**), `$oncekisonraki` (yön: "onceki"/"sonraki"), `$sira` (sıra değeri), `$kategori` (kategori değeri).

## 3. `$kategori` hakkında bu dosyadaki kanıtlar

| # | Bulgu | Satır | Yorum |
|---|---|---|---|
| E-1 | `urun where kategori=$kategori` — değer **tırnaksız** (unquoted) yazılıyor | 241, 243, 248, 250 | `$kategori` **sayısal** bağlamda kullanılıyor; string kategori adı değil |
| E-2 | `onceki_sonraki_urun` içinde `$sira`, `urun.sira` ile karşılaştırılıyor | 241, 243, 248, 250 | Navigasyon **ürün bazlı sıralama** ile yapılıyor; `kategori.sira` burada kullanılmıyor |
| E-3 | Aynı dosyada `menuyaz($kategori,$cizgi)` parametreyi `sayfa` tablosunda kullanıyor ve `$kategori==0` ile karşılaştırıyor | 170, 173, 190 | `$kategori`nin sayısal ve `0` alabilen bir değer olduğu bağımsız olarak doğrulanıyor |
| E-4 | `kategoriadi($id)` fonksiyonu `id!=0` kontrolü yapıp `0` için `"Ana Kategori"` döndürüyor | 204–213 | `0` = kök/ana kategori anlamı; sayısal kimlik kullanımı ile tutarlı |

## 4. `urun.kategori` / `kategori.id` ile doğrudan ilişki

| İfade | Kanıt | Durum |
|---|---|---|
| `urun.kategori` kolonu, `WHERE kategori=$kategori` ile filtrelenir | satır 241, 243, 248, 250 | **CONFIRMED** |
| `$kategori` değeri **sayısaldır** (tırnaksız SQL + `==0` karşılaştırması) | satır 241/243/248/250 + 190 | **CONFIRMED** |
| `urun` navigasyonu `urun.sira` ile yapılır (kategori içi sıralama) | satır 241, 243, 248, 250 | **CONFIRMED** |
| `$kategori` = `kategori.id` | Bu dosyada çağrı yeri **yok**; değer kaynağı görünmüyor | **UNRESOLVED** |
| `$kategori` = `urun.kategori` ile aynı değer kümesi | `urun.kategori` okuma/atama satırı bu dosyada yok | **UNRESOLVED** |
| `kategori.sira` = `kategori.id` varsayımı | Bu dosyada destekleyen ifade yok | **UNRESOLVED** |

> **Not:** `$kategori`nin `urun.kategori` olup olmadığı bu dosyadan çıkarılamaz; ancak kullanım biçimi (`urun where kategori=$kategori`) `$kategori`nin `urun.kategori` ile **aynı alanı hedeflediğini** gösterir. Bu, değerin kaynağına (yani `1,2,3,9,10,11` mi yoksa `53–64` mü olduğuna) dair kanıt üretmez.

## 5. Gerekli olmayan / gerekli dosyalar

- Bu görev kapsamında **çağrı kaynağını bulmak için** gerekli olan dosya: `detay.php` (ürün detayında önceki/sonraki navigasyonu) — ancak **bu görevde okunmadı** (kural: başka dosya okuma yok).
- `fonksiyon.php` içinde `onceki_sonraki_urun` çağrısının bulunmadığı **kesin olarak** doğrulandı (1 eşleşme = tanım).

## 6. Verdict

**UNRESOLVED** — `onceki_sonraki_urun` fonksiyonunun bu dosyada **çağrısı yoktur**; `$kategori` parametresinin değer kaynağı ve `urun.kategori → kategori.id` eşleşmesi bu dosyadan **kanıtlanamaz**.
Kanıtlanan olgular: `urun.kategori` sayısal bir kategori değeriyle filtreleniyor (CONFIRMED) ve kategori içi navigasyon `urun.sira` üzerinden yapılıyor (CONFIRMED).
Hedef mapping için CONFIRMED sayısı: **0 / 6**.

---
Kısıt uyumu: DB bağlantısı YOK · SQL çalıştırma YOK · dosya değiştirme YOK · başka dosya okuma YOK · tahmin YOK · commit/push YOK.