<?php
session_start();
require_once __DIR__ . '/../../../../src/core/CsrfProtection.php';
CsrfProtection::generate();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

$isEdit = false;
$post = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'author' => 'Aura Estates',
    'category' => 'General',
    'cover_image' => '',
    'status' => 'draft',
];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $post = $row;
        $isEdit = true;
    }
}

if ($_POST) {
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']) ?: preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
    $slug = trim($slug, '-');
    $excerpt = trim($_POST['excerpt']);
    $content = $_POST['content'];
    $author = trim($_POST['author']) ?: 'Aura Estates';
    $category = trim($_POST['category']) ?: 'General';
    $cover_image = trim($_POST['cover_image']);
    $status = $_POST['status'] === 'published' ? 'published' : 'draft';
    $published_at = $status === 'published' ? ($post['published_at'] ?: date('Y-m-d H:i:s')) : null;

    if (empty($title)) {
        $error = 'Title is required.';
    } else {
        // Check slug uniqueness
        $stmt = $db->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $post['id']]);
        if ($stmt->fetch()) {
            $error = 'A post with this slug already exists.';
            } else if (!$error) {
            if ($isEdit) {
                $stmt = $db->prepare("UPDATE blog_posts SET title=?, slug=?, excerpt=?, content=?, author=?, category=?, cover_image=?, status=?, published_at=? WHERE id=?");
                $stmt->execute([$title, $slug, $excerpt, $content, $author, $category, $cover_image, $status, $published_at, $post['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, author, category, cover_image, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $excerpt, $content, $author, $category, $cover_image, $status, $published_at]);
            }
            header('Location: ../blog.php?updated=1');
            exit;
        }
    }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'New'; ?> Post — Aura Estates Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg: '#f8f8f6', 'bg-alt': '#ffffff', surface: '#ffffff',
                        ink: '#111111', 'ink-secondary': '#6b6b6b', graphite: '#6b6b6b',
                        muted: '#999998', clay: '#e5e5e3', border: '#e5e5e3',
                        'border-light': '#f0f0ee', accent: '#111111', 'accent-hover': '#333333',
                        success: '#2d7d46', warning: '#b8860b', danger: '#b22222',
                    },
                    fontFamily: {
                        sans: ['DM Sans', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,700&family=DM+Sans:opsz,wght@9..40,200;9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../resources/css/neo.css">
</head>
<body class="min-h-screen bg-bg font-sans">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <div class="flex-1 overflow-y-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-4xl font-serif font-light italic text-ink mb-2"><?php echo $isEdit ? 'Edit' : 'New'; ?> Post</h1>
                <p class="text-sm text-graphite font-sans"><?php echo $isEdit ? 'Update' : 'Create'; ?> a journal article</p>
            </div>
            <a href="<?php echo Auth::getBasePrefix(); ?>/admin/blog" class="neo-btn-ghost">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        <?php if (isset($error)): ?>
        <div class="mb-6 p-4 bg-danger/15"><span class="text-danger text-sm font-sans"><?php echo $error; ?></span></div>
        <?php endif; ?>

        <form method="POST" class="max-w-4xl">
            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="neo-card p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite block mb-2">Title *</label>
                        <input type="text" name="title" class="neo-input w-full" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                    </div>
                    <div>
                        <label class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite block mb-2">Slug</label>
                        <input type="text" name="slug" class="neo-input w-full" value="<?php echo htmlspecialchars($post['slug']); ?>" placeholder="auto-generated from title">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite block mb-2">Author</label>
                        <input type="text" name="author" class="neo-input w-full" value="<?php echo htmlspecialchars($post['author']); ?>">
                    </div>
                    <div>
                        <label class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite block mb-2">Category</label>
                        <select name="category" class="neo-input w-full">
                            <?php $cats = ['General', 'Project Stories', 'Design Insights', 'Market Trends', 'Sustainability', 'Company News']; ?>
                            <?php foreach ($cats as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo $post['category'] === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite block mb-2">Cover Image URL</label>
                    <input type="text" name="cover_image" class="neo-input w-full" value="<?php echo htmlspecialchars($post['cover_image']); ?>" placeholder="https://images.unsplash.com/photo-...">
                </div>

                <div>
                    <label class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite block mb-2">Excerpt</label>
                    <textarea name="excerpt" rows="3" class="neo-input w-full resize-y" placeholder="Brief summary for listing pages"><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                </div>

                <div>
                    <label class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite block mb-2">Content (HTML)</label>
                    <textarea name="content" rows="16" class="neo-input w-full resize-y font-mono text-sm leading-relaxed" placeholder="<p>Write your article in HTML...</p>"><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div class="flex items-center gap-4">
                    <label class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite">Status</label>
                    <select name="status" class="neo-input w-auto">
                        <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-clay/20">
                    <button type="submit" class="neo-btn">
                        <i class="fas fa-save mr-2"></i>
                        <?php echo $isEdit ? 'Update' : 'Create'; ?> Post
                    </button>
                    <a href="<?php echo Auth::getBasePrefix(); ?>/admin/blog" class="neo-btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>