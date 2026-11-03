# M5-C.1 — Legacy Category Mapping Analysis (source-code evidence only)

> **KURAL:** Bu raporda bir eşleşme yalnızca **doğrudan legacy kaynak kodu kanıtı** varsa
> `CONFIRMED` yazılır. Ürün adı örüntüsü, sayı aralığı benzerliği veya istatistiksel
> uygunluk **KANIT DEĞİLDİR**.

---

## 1. Scope

Amaç: `urun.kategori` değerlerinin (1, 2, 3, 9, 10, 11) gerçek `kategori.id` kayıtlarıyla
(53–64) nasıl ilişkilendirildiğini **legacy PHP kaynak kodundan** kanıtlamaya çalışmak.

Kapsam dışı: DB bağlantısı, SQL çalıştırma, dosya değiştirme/taşıma/kopyalama/silme,
yeni DB'ye yazma, commit/push. Bu görevde hiçbiri yapılmadı.

Yöntem: makinede legacy PHP kaynağı arandı (recursive, salt-okunur); bulunan her dosya
satır satır okundu; dump (`u0506662_best.sql`) **yalnızca destekleyici** veri kaynağı
olarak kullanıldı.

## 2. Inspected Legacy Files

| # | Dosya | Konum | Boyut | Durum |
|---|---|---|---|---|
| 1 | `index.php` | `C:\Users\PC\Downloads\index.php` | 12.298 B | **OKUNDU — legacy Akan Best ana sayfası** |
| 2 | `config.php` | `C:\Users\PC\Downloads\config.php` | 313 B | **OKUNDU — legacy DB bağlantısı** |
| 3 | `inc/config.php` | — | — | **YOK** (index.php satır 1 include ediyor; dosya mevcut değil) |
| 4 | `inc/fonksiyon.php` | — | — | **YOK** (satır 2; `klasor()`, `resimbas()` burada) |
| 5 | `urunler.php` | — | — | **YOK — kritik eksik** |
| 6 | `detay.php` | — | — | **YOK** |
| 7 | `header.php`, `footer.php` | — | — | **YOK** (satır 35, 439) |
| 8 | `legacy-uploads.zip` | `tools/legacy-export/` | 9.249.783 B | İçerik listelendi: 57 JPG + boş `auth.php`; **kod yok** |
| 9 | `u0506662_best.sql` | `C:\Users\PC\Downloads\` | 9.411.624 B | **Yalnızca destekleyici** (35 tablo şeması + veri) |

**Arama kanıtı:** `C:\Users\PC` recursive `*.php` taramasında proje/`Vitrivia` dışında
**yalnızca 3 dosya** bulundu: `Desktop\audit-readonly.php`, `Downloads\config.php`,
`Downloads\index.php`. `urunler.php`, `detay.php`, `fonksiyon.php`, `header.php`,
`footer.php`, `kategori.php` adlarında dosya **bulunamadı**. Yalnızca `C:\` sürücüsü
mevcut (tek PSDrive). Arşivler: `legacy-uploads.zip` (kod yok) + ilgisiz `DSManager.rar`.

> **SONUÇ:** Legacy uygulamanın **kategori filtreleme ve ürün detay mantığını içeren
> dosyalar (urunler.php, detay.php, inc/fonksiyon.php) bu makinede YOK.** Elimizdeki tek
> legacy kaynak `index.php` (ana sayfa) ve `config.php` (DB bağlantısı).

## 3. `urun.kategori` Usage

- **Legacy PHP kaynak kodunda `urun.kategori` (veya `urun` tablosuna yapılan herhangi bir
  sorgu) BULUNAMADI.** `index.php` içindeki **tek** DB sorgusu `kategori` tablosunadır
  (satır 275); `urun` tablosu index.php'de hiç sorgulanmıyor.
- PHP kaynağında `urun.kategori` için **0 (sıfır) kullanım** → semantik **kanıtlanamadı**.
- Destekleyici (KANIT DEĞİL) dump verisi: `urun.kategori` değerleri {1, 2, 3, 9, 10, 11};
  dağılım 24 / 31 / 23 / 1 / 1 / 1 = 81 ürün.
- Dump'ta `urun` şeması: `kategori` varchar(5) NOT NULL, FK/INDEX **yok** (satır 51562).
  Bu, `urun.kategori`nin veritabanı seviyesinde **referans bütünlüğü olmadığını** gösterir;
  ilişki yalnızca uygulama kodunda tanımlıdır — ve o kod elimizde yok.

## 4. `kategori.id` Usage

- `index.php` **satır 280** — kategori menüsü bağlantısı:
  `<a href="urunler.php<?=klasor();?>?kid=<?=$kk["id"]?>">`
  → Menü, `kategori` tablosunun **`id`** değerini `kid` parametresi olarak `urunler.php`ye
  aktarıyor. Yani **URL'deki kategori tanımlayıcısı = `kategori.id`** (53–64).
- `index.php` **satır 281** — kategori görseli ve adı:
  `uploads/<?=resimbas($kk["resim"])?>` ve `alt="<?=$kk[$_SESSION["dil"]."baslik"]?>"`
  → `kategori.resim` ve `kategori.baslik` (dil önekli) kullanılıyor.
- **KANIT:** Bu, `kategori.id`nin legacy arayüzde kategori kimliği olarak kullanıldığını
  **doğrular**. Ancak `urun.kategori` ile `kategori.id` arasında bir eşitlik/ilişki
  **kurduğunu kanıtlamaz** (ürün tarafı sorgusu yok).
- Destekleyici: `kategori.id` kümesi {53…64}, `mesaj AUTO_INCREMENT=65` (dump satır 52061)
  ve 12 satır ile tutarlı — yani 1–52 aralığı bu tabloda **hiç var olmamış/yok**.

## 5. `kategori.sira` Usage

- `index.php` **satır 275** — kategori menüsü sorgusu:
  `<? $kks=mysql_query("select * from kategori order by sira asc");`
  → **`ORDER BY sira ASC`** kanıtlı. Yani `kategori.sira` bu legacy kodda
  **sıralama (display order)** amacıyla kullanılıyor.
- **`kategori.sira` ile `urun.kategori` arasında bir ilişki kuran kod satırı BULUNAMADI.**
  (`sira` yalnızca `ORDER BY` içinde; bir `WHERE kategori = ...sira...` veya
  `urun.kategori = kategori.sira` ifadesi hiçbir okunan dosyada yok.)
- Görev kuralı 7 gereği: ilişki yalnızca kaynak kanıtıyla kabul edilir → **bu ilişki
  kanıtlanmamıştır, UNRESOLVED kalır.**
- Destekleyici (KANIT DEĞİL): `kategori.sira` değer kümesi {1,2,3,4,5,6,7,8,9,10,11,12}
  olup `urun.kategori` kümesi {1,2,3,9,10,11} bunun **alt kümesidir**. Bu yalnızca bir
  hipotez gerekçesidir; ürün adı/sayı örüntüsü gibi **kanıt sayılmaz**.
## 6. `urunler.php` Findings

- **Dosya mevcut değil** (`C:\Users\PC` recursive arama: bulunamadı). Bu nedenle
  ürün listeleme/filtreleme mantığı **incelenemedi**.
- Kanıtlanan tek şey, `index.php` satır 280'deki bağlantının bu dosyaya
  **`?kid=<kategori.id>`** parametresini gönderdiğidir.
- Mantıksal çıkarım (KANIT DEĞİL, kaynak kod yok): URL'deki `kid` bir `kategori.id`
  (53–64) iken `urun.kategori` değerleri {1,2,3,9,10,11} olduğundan, `urunler.php`
  ya bir **çevirim (translation)** yapıyor olmalı ya da mevcut veri ile eşleşme
  sağlayamıyor (yani liste boş dönüyor) olmalıdır. **Her iki durum da doğrulanamadı.**
- `klasor()` fonksiyonu (satır 280 içinde çağrılıyor) `inc/fonksiyon.php`de olup
  **mevcut değil** → parametrenin tam üretim biçimi (dil yolu) doğrulanamadı.

## 7. `detay.php` Findings

- **Dosya mevcut değil** → ürün detay sorgusu incelenemedi.
- `index.php` içinde ürün detay bağlantıları **sabit kodlanmış**:
  `C:\Users\PC\Downloads\index.php` satır **140, 154, 168**:
  `<a href="/detay.php?id=695">DETAY</a>`
- **Kritik gözlem:** `id=695`, dump'taki `urun.id` aralığının (**21–143**) tamamen
  **dışındadır**. Yani ana sayfadaki bu bağlantılar gerçek ürün kayıtlarına işaret
  etmiyor (hardcoded/legacy artık). Bu, legacy şablonun veri ile senkron olmadığını
  gösteren bağımsız bir bulgudur ve **kategori mapping hakkında kanıt üretmez**.
- `detay.php`nin `id` parametresini `urun.id` olarak kullandığı **varsayılmamalıdır**;
  kanıt yoktur.

## 8. Category Filtering / Query Logic

Legacy kaynakta bulunan **tüm** SQL ifadeleri (tam liste):

| Dosya | Satır | SQL | Amaç |
|---|---|---|---|
| `Downloads\index.php` | 275 | `select * from kategori order by sira asc` | Ana sayfa kategori menüsü |
| `Downloads\index.php` | 276 | `mysql_fetch_array($kks)` | Menü satırlarını döngüleme |
| `Downloads\config.php` | 6 | `mysql_connect($dbhost,$dbuser,$dbpass)` | Eski MySQL API bağlantısı |
| `Downloads\config.php` | 9 | `mysql_select_db($dbname,$baglan)` | DB seçimi |

- **`WHERE kategori = ...` ifadesi: 0 (sıfır) kez.**
- **`JOIN kategori`: 0 kez.**
- **`WHERE id = ...` (kategori bağlamında): 0 kez.**
- **`urun` tablosuna yapılan sorgu: 0 kez.**
- Kategori seçimi **yalnızca `?kid=<id>` URL parametresi** ile yapılıyor (satır 280);
  parametrenin işlenmesi adı geçen eksik dosyalarda.
- Dump içinde `urunler.php`, `detay.php`, `kid=` dizeleri **hiç geçmiyor**
  (0 eşleşme) → DB tarafında da ek kanıt yok.

**Sonuç:** Kategori filtreleme mantığı bu makinedeki kaynaklarla **kanıtlanamadı**.

## 9. Proven Mapping Evidence

**Kanıtlanan tek olgu (kaynak kodlu):**

> Legacy ana sayfa, kategori listesini `kategori` tablosundan **`ORDER BY sira ASC`** ile
> çeker ve her kategori bağlantısında **`kategori.id`** değerini
> `urunler.php?kid=<id>` olarak kullanır.
> — `C:\Users\PC\Downloads\index.php` satır **275** ve **280**.

Bu olgu şunları **kanıtlar**:
1. `kategori.sira` legacy kodda **sıralama** alanıdır.
2. Kategori kimliği olarak arayüzde kullanılan alan **`kategori.id`**dir.
3. `kategori.resim` alanı kategori görseli olarak kullanılır (satır 281, `resimbas()`).

Bu olgu şunları **KANITLAMAZ**:
- `urun.kategori` değerlerinin `kategori.id`ye **doğrudan** karşılık geldiğini
  (değer kümeleri kesişmiyor: {1,2,3,9,10,11} ∩ {53…64} = ∅).
- `urun.kategori` değerlerinin `kategori.sira`ya karşılık geldiğini
  (`sira` hiçbir yerde `urun` sorgusunda görünmüyor; `urunler.php` yok).
- Herhangi bir ürün → kategori eşleşmesini.

**PROVEN MAPPING TABLOSU**

| Legacy urun.kategori | Legacy kategori.id | Kategori adı | Kanıt | Güven |
|---|---:|---|---|---|
| 1 | — | — | `index.php:280` yalnızca `kategori.id` URL kullanımını gösterir; `urun.kategori` semantiği hiçbir kaynakta yok | **UNRESOLVED** |
| 2 | — | — | aynı | **UNRESOLVED** |
| 3 | — | — | aynı | **UNRESOLVED** |
| 9 | — | — | aynı | **UNRESOLVED** |
| 10 | — | — | aynı | **UNRESOLVED** |
| 11 | — | — | aynı | **UNRESOLVED** |

**CONFIRMED satır sayısı: 0 / 6.**
## 10. Unresolved Mappings

| Legacy değer | Ürün sayısı | Durum | Neden çözülemedi |
|---|---:|---|---|
| 1 | 24 | **UNRESOLVED** | `urun.kategori`yi okuyan/işleyen kaynak kod yok |
| 2 | 31 | **UNRESOLVED** | aynı |
| 3 | 23 | **UNRESOLVED** | aynı |
| 9 | 1 | **UNRESOLVED** | aynı |
| 10 | 1 | **UNRESOLVED** | aynı |
| 11 | 1 | **UNRESOLVED** | aynı |
| **TOPLAM** | **81** | 6/6 çözümsüz | — |

Ek olarak çözümsüz kalan yapısal gözlemler (destekleyici, kanıt değil):
- `kkategori.ustkat` değerleri {45,46,49,50,51,52} → bu id'ler `kategori` tablosunda
  **yok** (yalnızca 53–64 var). Legacy DB'de **orphan referanslar** mevcut; bu da
  `kategori` tablosunun geçmişte yeniden oluşturulduğunu (id aralığının değiştiğini)
  düşündürür — ancak `urun.kategori`nin eski numaralandırmaya işaret ettiği
  **kanıtlanamaz**.
- `kategori.ust` alanı 12/12 satırda `NULL`, `kategori.kategori` alanı `0`/boş →
  tablonun kendi hiyerarşi alanları **kullanılmıyor** (düz yapı).

## 11. Exact Source References

| # | Dosya | Satır | Kod (birebir) |
|---|---|---|---|
| R1 | `C:\Users\PC\Downloads\index.php` | 1 | `<?php include_once "inc/config.php";` |
| R2 | `C:\Users\PC\Downloads\index.php` | 2 | `include "inc/fonksiyon.php";` |
| R3 | `C:\Users\PC\Downloads\index.php` | 35 | `include "header.php";` |
| R4 | `C:\Users\PC\Downloads\index.php` | 140 | `<a href="/detay.php?id=695">DETAY</a>` |
| R5 | `C:\Users\PC\Downloads\index.php` | 154 | `<a href="/detay.php?id=695">DETAY</a>` |
| R6 | `C:\Users\PC\Downloads\index.php` | 168 | `<a href="/detay.php?id=695">DETAY</a>` |
| R7 | `C:\Users\PC\Downloads\index.php` | 275 | `<? $kks=mysql_query("select * from kategori order by sira asc");` |
| R8 | `C:\Users\PC\Downloads\index.php` | 276 | `while ($kk=mysql_fetch_array($kks)) {` |
| R9 | `C:\Users\PC\Downloads\index.php` | 280 | `<a href="urunler.php<?=klasor();?>?kid=<?=$kk["id"]?>">` |
| R10 | `C:\Users\PC\Downloads\index.php` | 281 | `<img class="img-in" src="uploads/<?=resimbas($kk["resim"])?>" alt="<?=$kk[$_SESSION["dil"]."baslik"]?>">` |
| R11 | `C:\Users\PC\Downloads\index.php` | 439 | `include "footer.php";` |
| R12 | `C:\Users\PC\Downloads\config.php` | 6 | `$baglan = mysql_connect($dbhost,$dbuser,$dbpass);` |
| R13 | `C:\Users\PC\Downloads\config.php` | 9 | `mysql_select_db($dbname,$baglan) or die ("Veritabani Secilemedi.");` |
| R14 | `u0506662_best.sql` | 49828 | `INSERT INTO `kategori` (`id`, `baslik`, `icerik`, `seo`, `enbaslik`, `frbaslik`, `sira`, `resim`, ...) VALUES` |
| R15 | `u0506662_best.sql` | 51562 | `` `kategori` varchar(5) NOT NULL `` (`urun` tablosu; FK/index yok) |
| R16 | `u0506662_best.sql` | 52061 | `MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;` (`kategori`) |

**Yokluk kanıtı (aranan, bulunamayan):** `urunler.php`, `detay.php`, `inc/fonksiyon.php`,
`inc/config.php`, `header.php`, `footer.php` — `C:\Users\PC` recursive `*.php` taraması
(proje ve `Vitrivia` hariç) yalnızca 3 dosya döndürdü: `Desktop\audit-readonly.php`,
`Downloads\config.php`, `Downloads\index.php`. Dump içinde `urunler.php` / `detay.php` /
`kid=` dizeleri 0 kez geçmektedir.

## 12. Recommended Next Step

**Zorunlu (insan):** Kategori filtreleme mantığını içeren legacy dosyaları temin etmek.
Bunlar olmadan mapping **kanıtlanamaz** ve M5-D başlatılamaz.

1. Natro `public_html` (legacy kök) altından **salt-okunur** olarak şu dosyaları indir:
   `urunler.php`, `detay.php`, `inc/fonksiyon.php`, `inc/config.php`, `header.php`,
   `footer.php` (varsa `kategori.php`, `sayfa.php`).
2. Bu dosyaları proje dışı bir klasörde saklayıp (örn. `tools/legacy-export/legacy-src/`)
   M5-C.2 görevi ile Cline'a ver — Cline yalnızca okur ve mapping'i kanıtlamaya çalışır.
3. Alternatif (kanıt yolları):
   - phpMyAdmin → `urunler.php` içindeki sorgunun canlı davranışını görmek yerine,
     **legacy sitede bir kategori sayfasını açıp** URL'nin `kid` değerini ve dönen ürün
     sayısını not etmek (kanıt: hangi `kid` hangi ürünleri listeliyor) — bu,
     `kid` → ürün kümesini ampirik olarak gösterir; ancak mapping kararı yine insanda.
   - `urun` tablosunda `kategori` değerlerinin hangi `kategori.id`ye karşılık geldiğini
     **ürün sayısı eşleşmesiyle** doğrulamak: her `kid` (53–64) için legacy sayfada
     listelenen ürün sayısını kaydetmek → {24, 31, 23, 1, 1, 1} dağılımıyla
     karşılaştırmak. Bu ampirik kanıttır, **kaynak kod kanıtı değildir**; insan onayıyla
     `CONFIRMED` yapılabilir.
4. Ancak kesin kural: kaynak kod geldiğinde, `urunler.php` içindeki `WHERE kategori = ...`
   (veya eşdeğeri) satırı bulunursa mapping o satırla **CONFIRMED** işaretlenir.

---

### Görev kısıtlarına uyum

| Kısıt | Durum |
|---|---|
| DB bağlantısı | **YOK** (bağlanılmadı) |
| SQL çalıştırma | **YOK** (dump ve kaynaklar salt-okunur okundu) |
| Dosya değiştirme | **YOK** (yalnızca bu rapor dosyası oluşturuldu) |
| Dosya taşıma/kopyalama/silme | **YOK** |
| Yeni DB'ye veri yazma | **YOK** |
| Commit / push | **YOK** |
| Tahminle CONFIRMED | **YOK** (0/6 CONFIRMED) |
| `U4-urun-images.txt` | **KULLANILMADI** |

**VERDICT: PASS** (analiz tamamlandı; mapping kanıtlanamadı → 6/6 UNRESOLVED, insan girdisi gerekli).