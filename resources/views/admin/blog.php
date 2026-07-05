<?php
require_once __DIR__ . '/../../../src/config/auth.php';
require_once __DIR__ . '/../../../src/core/CsrfProtection.php';
Auth::requireRole('admin');
CsrfProtection::generate();

require_once __DIR__ . '/../../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle delete
if (isset($_POST['delete'])) {
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        die('Invalid CSRF token.');
    }
    $id = (int)$_POST['delete'];
    $db->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
    header('Location: blog.php?deleted=1');
    exit;
}

// Handle status toggle
if (isset($_POST['toggle'])) {
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        die('Invalid CSRF token.');
    }
    $id = (int)$_POST['toggle'];
    $status = $_POST['new_status'] === 'published' ? 'published' : 'draft';
    $db->prepare("UPDATE blog_posts SET status = ? WHERE id = ?")->execute([$status, $id]);
    header('Location: blog.php?updated=1');
    exit;
}

$stmt = $db->query("SELECT id, title, category, author, status, published_at, created_at FROM blog_posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog — Aura Estates Admin</title>
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
    <link rel="stylesheet" href="../resources/css/neo.css">
</head>
<body class="min-h-screen bg-bg font-sans">

<div class="flex h-screen overflow-hidden">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-1 overflow-y-auto p-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-4xl font-serif font-light italic text-ink mb-2">Blog Posts</h1>
                <p class="text-sm text-graphite font-sans">Manage your journal articles and publications</p>
            </div>
            <a href="/admin/blog/edit" class="neo-btn">
                <i class="fas fa-plus mr-2"></i> New Post
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
        <div class="mb-6 p-4 bg-graphite/15"><span class="text-graphite text-sm font-sans">Post deleted.</span></div>
        <?php endif; ?>
        <?php if (isset($_GET['updated'])): ?>
        <div class="mb-6 p-4 bg-success/15"><span class="text-success text-sm font-sans">Post updated.</span></div>
        <?php endif; ?>

        <div class="neo-table overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-clay/30">
                        <th class="text-left py-4 px-6 font-mono text-[0.625rem] uppercase tracking-wider text-graphite font-medium">Title</th>
                        <th class="text-left py-4 px-6 font-mono text-[0.625rem] uppercase tracking-wider text-graphite font-medium">Category</th>
                        <th class="text-left py-4 px-6 font-mono text-[0.625rem] uppercase tracking-wider text-graphite font-medium">Author</th>
                        <th class="text-left py-4 px-6 font-mono text-[0.625rem] uppercase tracking-wider text-graphite font-medium">Status</th>
                        <th class="text-left py-4 px-6 font-mono text-[0.625rem] uppercase tracking-wider text-graphite font-medium">Date</th>
                        <th class="text-right py-4 px-6 font-mono text-[0.625rem] uppercase tracking-wider text-graphite font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($posts) === 0): ?>
                    <tr><td colspan="6" class="py-12 text-center text-graphite font-sans">No posts yet. <a href="/admin/blog/edit" class="text-ink underline">Create one.</a></td></tr>
                    <?php else: ?>
                    <?php foreach ($posts as $p): ?>
                    <tr class="border-b border-clay/20 hover:bg-ink/[0.02] transition-colors">
                        <td class="py-4 px-6">
                            <span class="font-sans text-sm text-ink font-medium"><?php echo htmlspecialchars($p['title']); ?></span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite"><?php echo htmlspecialchars($p['category']); ?></span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-sans text-sm text-graphite"><?php echo htmlspecialchars($p['author']); ?></span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-mono text-[0.625rem] uppercase tracking-wider <?php echo $p['status'] === 'published' ? 'text-success' : 'text-warning'; ?>">
                                <?php echo $p['status']; ?>
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-sans text-sm text-graphite"><?php echo date('M j, Y', strtotime($p['published_at'] ?: $p['created_at'])); ?></span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="/admin/blog/edit?id=<?php echo $p['id']; ?>" class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite hover:text-ink transition-colors no-underline">Edit</a>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="toggle" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $p['status'] === 'published' ? 'draft' : 'published'; ?>">
                                    <button type="submit" class="font-mono text-[0.625rem] uppercase tracking-wider text-graphite hover:text-ink transition-colors no-underline bg-transparent border-0 cursor-pointer" style="background:none;border:none;cursor:pointer">
                                        <?php echo $p['status'] === 'published' ? 'Draft' : 'Publish'; ?>
                                    </button>
                                </form>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this post?')">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="delete" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="font-mono text-[0.625rem] uppercase tracking-wider text-danger/70 hover:text-danger transition-colors no-underline bg-transparent border-0 cursor-pointer" style="background:none;border:none;cursor:pointer">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
