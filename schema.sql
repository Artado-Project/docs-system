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

-- Initial Data
INSERT INTO categories (name, slug, description, icon) VALUES 
('Platform Introduction', 'introduction', 'An overview of the Artado platform and its components.', 'info-circle'),
('Guidelines', 'guidelines', 'Design and development guidelines for creating great apps.', 'list-check'),
('Tutorials', 'tutorials', 'Step-by-step guides to help you get started.', 'graduation-cap'),
('API Reference', 'api', 'Detailed documentation for the various platform APIs.', 'code');

INSERT INTO pages (category_id, title, slug, content) VALUES 
(1, 'What is Artado?', 'what-is-artado', '<h2>The Artado Platform</h2><p>Artado is a modern desktop environment and developer platform. It is built on top of the GTK library and provides a comprehensive set of tools for building beautiful applications.</p><h3>Core Components</h3><ul><li>GTK: The widget toolkit</li><li>Libadwaita: Premium UI components</li><li>Pango: Text rendering</li><li>Cairo: 2D graphics</li></ul>'),
(1, 'Getting Started', 'getting-started', '<h2>Getting Started</h2><p>To start developing for Artado, we recommend setting up a modern development environment using <b>Artado Builder</b> or <b>VS Code</b> with the Flatpak extension.</p><div style="background: var(--sidebar-bg); padding: 1rem; border-left: 4px solid var(--primary-color); margin: 1rem 0;">Ready to build? Check out the Tutorials section!</div>'),
(2, 'Human Interface Guidelines', 'hig', '<h2>Artado HIG</h2><p>The Artado Human Interface Guidelines (HIG) provide design patterns and principles to help you create apps that feel right on Artado.</p>'),
(3, 'First App with Python', 'first-app-python', '<h2>Your First App</h2><p>Building an Artado app with Python is straightforward. Here is a simple example:</p><pre style="background:#2d2d2d; color:#fff; padding:1rem; border-radius:8px; overflow-x:auto;">import gi\ngi.require_version(\"Gtk\", \"4.0\")\nfrom gi.repository import Gtk</pre>');
