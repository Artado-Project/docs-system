CREATE DATABASE IF NOT EXISTS artado_docs;
USE artado_docs;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'book',
    display_order INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
);

-- Default Settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('site_title', 'Artado Developer Documentation'),
('hero_title', 'Build Beautiful Artado Apps'),
('hero_desc', 'Premium documentation and resources for building next-generation applications.'),
('favicon_url', 'favicon.svg')
ON DUPLICATE KEY UPDATE setting_value=setting_value;

-- Default admin user (password: admin123)
-- Note: In a real app, use password_hash()
INSERT INTO users (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') ON DUPLICATE KEY UPDATE id=id;

-- Initial Data
INSERT INTO categories (name, slug, description, icon) VALUES 
('Platform Tanıtımı', 'introduction', 'Artado platformu ve bileşenlerine genel bakış.', 'info-circle'),
('Kılavuzlar', 'guidelines', 'Harika uygulamalar oluşturmak için tasarım ve geliştirme kılavuzları.', 'list-check'),
('Eğitimler', 'tutorials', 'Başlamanıza yardımcı olacak adım adım kılavuzlar.', 'graduation-cap'),
('API Referansı', 'api', 'Çeşitli platform API\'leri için detaylı dokümantasyon.', 'code')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO pages (category_id, title, slug, content) VALUES 
(1, 'Artado Nedir?', 'what-is-artado', '<h2>Artado Platformu</h2><p>Artado, modern bir masaüstü ortamı ve geliştirici platformudur. GTK kütüphanesi üzerine inşa edilmiştir ve güzel uygulamalar oluşturmak için kapsamlı bir araç seti sağlar.</p><h3>Temel Bileşenler</h3><ul><li>GTK: Widget araç seti</li><li>Libadwaita: Premium UI bileşenleri</li><li>Pango: Metin işleme</li><li>Cairo: 2D grafikler</li></ul>'),
(1, 'Başlangıç', 'getting-started', '<h2>Başlangıç</h2><p>Artado için geliştirmeye başlamak için, <b>Artado Builder</b> veya Flatpak uzantılı <b>VS Code</b> kullanarak modern bir geliştirme ortamı kurmanızı öneririz.</p><div style="background: var(--sidebar-bg); padding: 1rem; border-left: 4px solid var(--primary-color); margin: 1rem 0;">Geliştirmeye hazır mısınız? Eğitimler bölümüne göz atın!</div>'),
(2, 'İnsan Arayüzü Kılavuzu', 'hig', '<h2>Artado HIG</h2><p>Artado İnsan Arayüzü Kılavuzu (HIG), Artado platformunda doğru hissettiren uygulamalar oluşturmanıza yardımcı olacak tasarım desenleri ve ilkeleri sağlar.</p>'),
(3, 'Python ile İlk Uygulama', 'first-app-python', '<h2>İlk Uygulamanız</h2><p>Python ile bir Artado uygulaması oluşturmak oldukça basittir. İşte basit bir örnek:</p><pre style="background:#2d2d2d; color:#fff; padding:1rem; border-radius:8px; overflow-x:auto;">import gi\ngi.require_version("Gtk", "4.0")\nfrom gi.repository import Gtk</pre>')
ON DUPLICATE KEY UPDATE id=id;
