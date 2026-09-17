# Akan Best Ambalaj

## Durum

M2 tamam: InnoDB/utf8mb4 MariaDB şeması (yalnızca CREATE TABLE IF NOT EXISTS),
saf DSN üretimi, lazy PDO sarmalayıcı ve soyut Model tabanı.
Yerel makinede MariaDB/MySQL KURULMAZ (sahip kararı, 2026-09-17); tools/mariadb yoktur.
Şemayı uygulayan kod/script yoktur; ilk canlı uygulama staging'de insan onayıyla yapılır
(adımlar: docs/db-runbook.md). Veritabanına bağlanılmamıştır; Production/legacy DB izoledir.

## Dosyalar ve sözleşmeler

Proje kökü: `C:\Users\PC\AppData\Local\Cline\Akanbest\akanbest-website`

```text
app/bootstrap.php             App autoloader, Env, Helpers, merkezi handler kurulumu
app/Core/Env.php              KEY=VALUE parser; süreç ortamını değiştirmez
app/Core/Config.php           load(path), get('app.name', default)
app/Core/Helpers.php          e(): ENT_QUOTES / ENT_SUBSTITUTE / UTF-8
app/Core/Request.php          capture, method, path, query, post, header, ip
app/Core/Response.php         body/status/headers; json/html/redirect; send
app/Core/Router.php           get/post; dispatch(Request): Response
app/Core/View.php             render(template, data): string
app/Core/Database.php         buildDsn/connect/pdo + select/insert/update/delete/transaction
app/Models/Model.php          Soyut satır-tablosu tabanı (M4+ somut modeller)
database/schema.sql           9 tablo; yalnızca CREATE TABLE IF NOT EXISTS
docs/db-runbook.md            Staging/production şema adımları (insan adımları)
config/config.php             app ayarları ve kullanılmayan DB placeholder'ları
config/routes.php             [method, pattern, handler] route tablosu
public/index.php              bootstrap -> Config -> Request -> routes -> dispatch -> send
public/.htaccess              M0 dosyası değiştirilmedi
templates/public/placeholder.php
templates/errors/404.php
templates/errors/500.php
storage/logs/app.log           Runtime hata günlüğü; Git dışında
.env.example                  Güvenli örnek değerler
.env                          Yalnızca lokal, Git dışında
```

Route handler sözleşmesi: `function (Request $request, array $params): Response`.
Router kayıt sırasını izler; literal rotalar çakışan parametreli rotadan önce gelir.
`{param}` tek, boş olmayan path segmentini eşler; literal kısımlar regex olarak kaçırılır.
Eşleşmeyen yol 404 HTML; yol eşleşip method eşleşmezse 405 ve `Allow` döner.
GET/POST kayıtları desteklenir; otomatik HEAD/OPTIONS davranışı eklenmemiştir.
Request path query'den ayrılır, bir kez rawurldecode uygulanır ve sondaki slash kaldırılır.
Query/post anahtarsız çağrılırsa tüm dizi, anahtarla çağrılırsa değer veya varsayılan döner.
`ip()` yalnızca REMOTE_ADDR kullanır; X-Forwarded-* güvenilmezdir.

View adları `public/placeholder` biçimindedir; templates kökü dışına çıkamaz.
Veriler EXTR_SKIP ile çıkarılır; hata halinde View'in açtığı buffer temizlenir.
Şablonlarda dinamik HTML metinleri `e()` ile kaçırılır. Layout/CSS sistemi yoktur.

## Route tablosu

| Method | Pattern | Sonuç |
|---|---|---|
| GET | /health | M0 JSON payload korunur; router=M1 eklenir |
| GET | /healthz | Health alias |
| GET | / | 200, M1 placeholder HTML |
| GET | /kernel-test/fail | Yalnızca local: RuntimeException, merkezi 500 |
| GET | /kernel-test/{token} | 200 JSON token |

Kernel test rotaları geçicidir; M8'de kaldırılacaktır.
Local olmayan ortamda fail literal rotası kaydedilmez; aynı yol genel token rotasına eşleşir.
Health yalnızca uygulama/PHP açılışını gösterir; veritabanı kontrolü yapmaz.

## Lokal çalıştırma (PowerShell)

```powershell
Set-Location 'C:\Users\PC\AppData\Local\Cline\Akanbest\akanbest-website'
if (-not (Test-Path '.env')) { Copy-Item '.env.example' '.env' }
& 'C:\Users\PC\AppData\Local\Cline\Akanbest\tools\php8.3\php.exe' -S 127.0.0.1:8000 -t public public/index.php
```

`http://127.0.0.1:8000/health` üzerinden kontrol edin; sunucuyu Ctrl+C ile kapatın.
PHP dahili sunucusu yalnızca lokal geliştirme içindir; .htaccess işlemez.
Apache document root yalnızca public dizini olmalıdır. mod_rewrite, mod_headers ve
ilgili .htaccess direktif izinleri gerekir. Apache/Natro/cPanel üzerinde test yapılmadı.

## Hata yönetimi ve güvenlik

- Secrets Git'e girmez: .env, loglar ve yüklemeler ignore edilir. Remote/push yoktur.
- Env boş ve # yorum satırlarını atlar; ilk = karakterinde böler, eşleşen dış tırnakları kaldırır.
  Interpolasyon ve satır içi yorum işleme yoktur. putenv kullanılmaz.
- APP_DEBUG boolean ayrıştırılır. Ayrıntılı 500 yalnızca APP_ENV=local olduğunda gösterilir.
  Diğer ortamlara exception mesajı/trace verilmez. Eksik .env güvenli production varsayımına döner.
- Ham PHP display_errors kapalıdır; local ayrıntıları merkezi 500 şablonu gösterir.
- PHP hataları ErrorException'a çevrilir; maskelenen hatalara saygı gösterilir.
- Yakalanmamış exception ve shutdown fatal hataları storage/logs/app.log'a formatlı yazılır.
  Trace argümanları kapalı, log mesajlarındaki satır sonları kaçırılmıştır.
- 500 şablonunun kendisi hata verirse jenerik plain-text 500 fallback vardır.
- Handler kaydından önceki parse/startup hataları uygulama tarafından yakalanamaz.
  Bellek tükenmesi, yazılamayan log dizini ve önceden gönderilmiş HTTP başlıkları
  uygulama handler'ının kontrolünü sınırlayabilir. Log dizini yazılabilir olmalıdır.
- Production, legacy DB veya hosting ile etkileşim ve veritabanı bağlantısı yoktur.
