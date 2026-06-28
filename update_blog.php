<?php
require_once __DIR__ . '/src/config/database.php';
$database = new Database();
$db = $database->getConnection();

try {
    $db->exec("CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        excerpt TEXT,
        content TEXT NOT NULL,
        author VARCHAR(100) DEFAULT 'Aura Estates',
        category VARCHAR(100) DEFAULT 'General',
        cover_image VARCHAR(500) DEFAULT NULL,
        status ENUM('draft', 'published') DEFAULT 'draft',
        published_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insert sample posts if empty
    $count = $db->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn();
    if ($count == 0) {
        $db->exec("INSERT INTO blog_posts (title, slug, excerpt, content, author, category, cover_image, status, published_at) VALUES
            ('Embodied carbon in heritage buildings', 'embodied-carbon-heritage', 'A recent study comparing the full lifecycle emissions of a heritage retrofit versus a new-build replacement found that the retrofit option saved over 40% in total carbon emissions.', '<p>When we think about sustainable construction, heritage buildings rarely come to mind...</p><p>At Aura Estates, we specialise in managing heritage and listed properties. Our approach has always been to preserve and adapt rather than replace.</p>', 'Lukas Walker', 'Project Stories', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80&w=1200', 'published', '2026-05-15 10:00:00'),
            ('Why we still draw by hand before we draw on screen', 'draw-by-hand', 'Hand drawing is not about nostalgia. It is about thinking at the right speed. When you draw by hand, your hand moves at roughly the same pace as your thoughts.', '<p>In an age of sophisticated BIM software and parametric design tools, the question arises: why do we still draw by hand?</p><p>Hand drawing is not about nostalgia. It is about thinking at the right speed.</p>', 'Hanna Bennett', 'Design Insights', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&q=80&w=1200', 'published', '2026-04-28 10:00:00')");
        echo "Blog table created and sample posts inserted.";
    } else {
        echo "Blog table already exists with $count posts.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>