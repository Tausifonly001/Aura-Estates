<?php
try {
    $db = new PDO('mysql:host=localhost;port=3306;dbname=aura_estates;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $db->exec("DROP TABLE IF EXISTS site_content");
    $db->exec("CREATE TABLE site_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page VARCHAR(100) NOT NULL,
        section VARCHAR(100) NOT NULL,
        key_name VARCHAR(100) NOT NULL,
        value LONGTEXT,
        type ENUM('text','textarea','image','json') DEFAULT 'text',
        sort_order INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_page_sec_key (page, section, key_name),
        INDEX idx_page (page)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "site_content table ready.\n";

    function seed($db, $page, $section, $key, $value, $type = 'text', $order = 0) {
        $stmt = $db->prepare("INSERT IGNORE INTO site_content (page, section, key_name, value, type, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$page, $section, $key, $value, $type, $order]);
    }

    // Home page
    seed($db, 'home', 'hero', 'tagline', 'Bespoke property management defined by clarity');
    seed($db, 'home', 'hero', 'body', 'Spaces crafted through warmth, materiality, and clarity.');
    seed($db, 'home', 'about', 'heading', 'About the Studio');
    seed($db, 'home', 'about', 'body', 'Founded in 2020, Aura Estates is a deliberately focused practice. Every property is managed with the same thinking that shapes the first sketch — all the way through to the last detail on site.', 'textarea');
    seed($db, 'home', 'about', 'quote', 'We founded Aura Estates with the belief that property management should feel seamless, transparent, and deeply human. Every relationship is approached with clarity, warmth, and careful attention to detail — creating experiences that feel refined, functional, and made to last.', 'textarea');
    seed($db, 'home', 'about', 'attribution', 'The Aura Estates Team');
    // About page
    seed($db, 'about', 'hero', 'heading', 'About the Studio');
    seed($db, 'about', 'hero', 'body', 'Thoughtful property management, refined through years of practice.');
    // Services page
    seed($db, 'services', 'hero', 'heading', 'What We Do');
    seed($db, 'services', 'hero', 'body', 'Integrated property services. One team, full lifecycle.');
    // Contact page
    seed($db, 'contact', 'hero', 'heading', 'Get in Touch');
    seed($db, 'contact', 'hero', 'body', 'Let us hear from you.');
    seed($db, 'contact', 'info', 'email', 'hello@auraestates.com');
    seed($db, 'contact', 'info', 'phone', '+1 (555) 000-0000');
    seed($db, 'contact', 'info', 'address', '123 Luxury Lane, Beverly Hills, CA 90210');

    echo "Default content seeded.\n";
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
