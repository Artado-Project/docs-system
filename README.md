<div align="center">
  <img src="assets/img/logo.svg" alt="Artado Logo" width="120" height="120">
  <h1> Artado Docs</h1>
  <p><strong>Modern, Hızlı ve Şık Dokümantasyon Yönetim Sistemi</strong></p>

  [![Lisans](https://img.shields.io/badge/Lisans-MIT-yellow.svg)](#)
  [![Versiyon](https://img.shields.io/badge/Versiyon-1.0.0-green.svg)](#)
  [![PHP](https://img.shields.io/badge/Dil-PHP-777bb4.svg)](https://www.php.net/)
  [![MySQL](https://img.shields.io/badge/Veritabani-MySQL-4479a1.svg)](https://www.mysql.com/)
</div>

---

##  Proje Hakkında

Artado Docs, yazılım projeleriniz, API dökümanlarınız veya kurumsal rehberleriniz için tasarlanmış profesyonel bir içerik yönetim sistemidir. Kullanıcı dostu arayüzü, güçlü yönetici paneli ve modern özellikleriyle dokümantasyon sürecinizi baştan sona kolaylaştırır.

> [!IMPORTANT]
> Bu sistem tamamen **özelleştirilebilir** ve **responsive** yapıdadır. Masaüstü, tablet ve mobil cihazlarda kusursuz bir deneyim sunar.

---

##  Öne Çıkan Özellikler

- ** Modern Tasarım:** Glassmorphism ve minimalizm odaklı, göze hitap eden premium arayüz.
- ** Dinamik Tema:** Tek tıkla karanlık ve aydınlık mod arasında geçiş yapabilme.
- ** Markdown Düzenleyici:** Dahili SimpleMDE editörü ile zengin metin içerikleri (kod blokları, tablolar, listeler).
- ** Gelişmiş Çeviri:** Google Translate API entegrasyonu ile anlık çoklu dil desteği (EN, DE, FR, RU).
- ** Akıllı Arama:** Site genelinde hızlı ve etkili içerik arama motoru.
- ** %100 Mobil Uyumlu:** Hareket halindeki kullanıcılar için optimize edilmiş hamburger menü ve TOC yapısı.
- ** Güçlü Yönetici Paneli:** Sayfa ekleme, kategori yönetimi, site ayarları ve kullanıcı kontrolü.
- ** SEO Dostu:** Otomatik sitemap (site haritası) ve robots.txt yönetimi.

---

## 📂 Dosya Yapısı

Proje, temiz bir mimariyle organize edilmiştir:

###  Ana Dizin
| Dosya | Açıklama |
| :--- | :--- |
| `index.php` | Uygulamanın giriş sayfası ve kahraman (hero) alanı. |
| `docs.php` | İçeriklerin dinamik olarak işlendiği ve gösterildiği ana sayfa. |
| `admin.php` | Tüm içeriklerin ve ayarların yönetildiği merkezi panel. |
| `login.php` | Güvenli yönetici girişi sayfası. |
| `search.php` | Kullanıcıların içerik içinde arama yapmasını sağlar. |
| `setup.php` | Veritabanı tablolarını ve ilk ayarları kuran sihirbaz. |

###  Alt Klasörler
- **`/assets`**: Projenin statik dosyaları.
  - `/assets/style.css`: Tüm görsel tasarımı kontrol eden ana stil dosyası.
  - `/assets/img/`: Logolar (`logo.svg`), ikonlar (`favicon.svg`) ve görseller.
- **`/includes`**: Çekirdek PHP bileşenleri.
  - `db.php`: Veritabanı bağlantı konfigürasyonu.
  - `header.php`: Navigasyon ve SEO meta etiketlerini içeren üst kısım.
  - `footer.php`: Alt kısım ve JavaScript (Karanlık Mod, Çeviri) mantığı.
  - `lang.php`: Dil yönetim sistemi.
  - `utils.php`: Markdown işleme ve metin araçları.
- **`/translations`**: Arayüz metinlerinin kaynak dosyaları (`tr.php`).

---

##  Hızlı Kurulum

1.  **Dosyaları Yükleyin:** Proje dosyalarını sunucunuzun kök dizinine kopyalayın.
2.  **Veritabanı Oluşturun:** MySQL üzerinden boş bir veritabanı açın.
3.  **Bağlantıyı Yapılandırın:** `includes/db.php` dosyasını kendi veritabanı bilgilerinizle güncelleyin.
4.  **Kuruluma Başlayın:** Tarayıcınızdan `site-adresiniz.com/setup.php` sayfasını açın.
5.  **Tadını Çıkarın:** Kurulum bittikten sonra `admin`/`admin123` bilgileriyle giriş yapabilirsiniz.

---

##  Markdown Kullanım Rehberi

İçeriklerinizi aşağıdaki formatları kullanarak zenginleştirebilirsiniz:

###  Tablo Örneği
```markdown
| Özellik | Durum | Açıklama |
| :--- | :--- | :--- |
| SEO | ✅ | Full |
| Hız | 🚀 | Yüksek |
| Responsive | 📱 | Tam |
```

###  Kod Bloğu
````markdown
```php
echo "Artado Docs'a Hoş Geldiniz!";
```
````

---

##  Güvenlik ve Performans

- **PDO Bağlantısı:** SQL injection saldırılarına karşı tam koruma.
- **Şifreleme:** Kullanıcı şifreleri modern `password_hash` yöntemiyle saklanır.
- **Lazy Loading:** Sayfa hızını artırmak için ikonlar ve fontlar optimize edilmiş şekilde yüklenir.

---

##  Lisans

Bu proje **Artado Project** ekosisteminin bir parçasıdır. Tüm hakları saklıdır.

---
