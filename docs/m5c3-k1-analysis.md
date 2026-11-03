# M5-C.3 — k1() Function Check

Kapsam: Yalnızca `tools/legacy-source/fonksiyon.php` (8.866 B) okundu.
DB bağlantısı, SQL çalıştırma, dosya değiştirme, başka dosya okuma, commit/push: **yapılmadı**.

---

## 1. k1() davranışı (5 madde)

1. **Tanım:** `fonksiyon.php` satır **109** — `function k1($text){ ... }`; tek parametre alır ve **string döndürür**.
2. **Dönüşüm:** Satır **110–111** gkod=array("'") / dkod=array("&acute;") tanımlanır; satır **113** `str_replace($gkod,$dkod,$text)` ile **yalnızca tek tırnak (`'`) karakteri `&acute;` olarak değiştirilir**.
3. **Doğrulama yok:** Fonksiyon `intval()`, `(int)`, `is_numeric()` veya tip zorlaması **yapmaz**; sayısal girdi olduğu gibi (string olarak) döner.
4. **Kaçış kapsamı sınırlı:** Yalnızca `'` hedeflenir; çift tırnak, `\`, `;`, `--`, `%` gibi karakterler ve diğer enjeksiyon vektörleri **elenmez** (satır 113'te tek replace kuralı vardır).
5. **Kullanım bağlamı:** Çağıran dosyalarda (`urunler.php` 53/105, `detay.php` 12/143) sorgu dizesine doğrudan **birleştirilir**; sayısal doğrulama ayrı bir kontrolle yapılır (`urunler.php` 52: `is_numeric($_GET["kid"])`, `detay.php` 17: `is_numeric($_GET["id"])`).

> **Özet:** `k1()` gerçek bir kaçış/esckape fonksiyonu değildir; yalnızca tek tırnak karakterini HTML entity'sine çeviren **zayıf bir filtre**dir ve tip dönüşümü yapmaz.

## 2. Aynı dosyada bulunan `urun.kategori` kullanımı (ÖNEMLİ)

Bu dosya, `urun` tablosunu **kategori koşuluyla** sorgulayan ilk ve tek kanıtı içerir:

| Satır | Kod (kısa) |
|---|---|
| 241 | `select id from urun where kategori=$kategori and sira > $sira order by sira asc limit 1` |
| 243 | `select id from urun where kategori=$kategori order by sira asc limit 1` |
| 248 | `select id from urun where kategori=$kategori and sira < $sira order by sira desc limit 1` |
| 250 | `select id from urun where kategori=$kategori order by sira desc limit 1` |

- Bu 4 sorgu `onceki_sonraki_urun($id,$oncekisonraki,$sira,$kategori)` fonksiyonu içindedir (satır **238–254**).
- `$kategori` burada bir **fonksiyon parametresidir**; değerinin nereden geldiği **bu dosyada tanımlı değildir**.
- Ayrıca satır **350**: `select baslik from kategori where id=$id` (`blogkat_adi()`), `kategori.id` ile doğrudan arama yapıldığını gösterir.

## 3. CONFIRMED / UNRESOLVED

### CONFIRMED
| İfade | Kanıt | Güven |
|---|---|---|
| `k1()` yalnızca `'` → `&acute;` değiştirir, tip dönüşümü yapmaz | `fonksiyon.php` 109–115 | **CONFIRMED** |
| `urun.kategori` kolonu `WHERE kategori=$kategori` ile filtrelenir | `fonksiyon.php` 241/243/248/250 | **CONFIRMED** |
| `kategori.id` ile doğrudan kayıt araması vardır | `fonksiyon.php` 350 | **CONFIRMED** |
| `resim` alanı virgülle ayrılır, ilk dolu token döner | `fonksiyon.php` 228–237 (`resimbas()`) | **CONFIRMED** |

### UNRESOLVED
| Hedef | Neden | Güven |
|---|---|---|
| `urun.kategori (1,2,3,9,10,11) → kategori.id (53–64)` | `$kategori` değerinin kaynağı bu dosyada yok; çağıran kod incelenmedi (kural gereği) | **UNRESOLVED** |
| `kategori.sira = kategori.id` varsayımı | Bu dosyada destekleyen hiçbir ifade yok | **UNRESOLVED** |

**Hedef mapping için CONFIRMED sayısı: 0 / 6.**

## 4. Verdict

**UNRESOLVED** — `k1()` davranışı net olarak belirlendi (tek tırnak → `&acute;`, tip dönüşümü yok); ancak `urun.kategori` değerlerinin `kategori.id` ile eşleşmesi bu dosyadan **kanıtlanamaz** çünkü `$kategori` parametresinin değeri dışarıdan gelir ve çağıran kod bu kapsamda değildir.
Kanıtlanan yeni olgu: `urun` tablosu `where kategori=$kategori` ile filtrelenmektedir; bu, `urun.kategori`nin bir **kategori kimliği/değeri** olarak kullanıldığını doğrular ama `kategori.id` olduğunu göstermez.

---
Kısıt uyumu: DB bağlantısı YOK · SQL çalıştırma YOK · dosya değiştirme YOK · başka dosya okuma YOK · tahmin YOK · commit/push YOK.