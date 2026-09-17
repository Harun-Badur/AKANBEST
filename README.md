# Akan Best Ambalaj

## Durum

M0 tamam: framework'süz PHP iskeleti, App\\ autoloader, basit ortam yapılandırması ve health endpoint.
Router, Request/Response, View, Database, Auth, Admin ve iş özellikleri yoktur.
M1 çalışması mimar onayını bekler.

## Stack

PHP 8.3 / MariaDB (ileriki aşamalar) / Apache ve .htaccess / vanilla HTML, CSS, JavaScript.
Framework, Composer, Node veya üçüncü taraf paket bağımlılığı yoktur.
M0 veritabanına bağlanmaz; DB ayarları yalnızca placeholder'dır.

## Dosya yapısı

Proje kökü: `C:\Users\PC\AppData\Local\Cline\Akanbest\akanbest-website`

```text
akanbest-website/
├── app/
│   ├── bootstrap.php       # App\\ -> app/ autoloader ve ortam yüklemesi
│   └── Core/Env.php        # Statik KEY=VALUE parser
├── config/config.php      # app ve kullanılmayan db ayarları
├── public/
│   ├── index.php          # Front controller: health veya 404
│   └── .htaccess
├── storage/logs/.gitkeep
├── .env.example
├── .env                   # Yalnızca lokal, Git dışında
├── .gitignore
└── README.md
```

## Lokal çalıştırma (Windows PowerShell)

PHP runtime: `C:\Users\PC\AppData\Local\Cline\Akanbest\tools\php8.3\php.exe`

İlk kurulumda, mevcut `.env` dosyasının üzerine yazmadan:

```powershell
Set-Location 'C:\Users\PC\AppData\Local\Cline\Akanbest\akanbest-website'
if (-not (Test-Path '.env')) { Copy-Item '.env.example' '.env' }
& 'C:\Users\PC\AppData\Local\Cline\Akanbest\tools\php8.3\php.exe' -S 127.0.0.1:8000 -t public public/index.php
```

`http://127.0.0.1:8000/health` ve `/healthz`: HTTP 200 JSON.
`/` ve diğer yollar: HTTP 404 plain text. Sunucuyu Ctrl+C ile kapatın.
PHP dahili sunucusu yalnızca lokal geliştirme içindir; Apache `.htaccess` dosyasını işlemez.

Apache için document root yalnızca projenin `public` dizini olmalıdır.
`mod_rewrite`, `mod_headers` ve ilgili `.htaccess` direktiflerine izin gereklidir.
Apache/cPanel üzerinde M0 kapsamında kurulum veya test yapılmaz.

## Ortam ve güvenlik

- Secrets Git'e girmez. `.env`, loglar ve yüklenen dosyalar Git dışında tutulur.
- `.env.example` yalnızca örnek değerler içerir; gerçek kimlik bilgileri eklenmez.
- Env parser boş ve `#` yorum satırlarını atlar, ilk `=` karakterinde böler,
  eşleşen dış tırnakları kaldırır. Değişken interpolasyonu ve satır içi yorum işleme yoktur.
- Ortam değerleri sınıf belleğinde tutulur; süreç ortamı değiştirilmez.
- `APP_DEBUG` boolean olarak ayrıştırılır; hata gösterimi yalnızca `APP_ENV=local` ise açıktır.
- `.env` yoksa ortam `production`, debug `false` olur.
- Hatalar `storage/logs/app.log` dosyasına yazılır; bu dizin runtime tarafından yazılabilir olmalıdır.
- Health yalnızca uygulama/PHP açılışını gösterir; MariaDB sağlığını kontrol etmez.
- Production, legacy DB ve Natro ile M0 kapsamında etkileşim kurulmaz.
- Git repository lokaldir; remote ve push yoktur.
