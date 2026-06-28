<?php
require_once __DIR__ . '/../../src/config/auth.php';
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/models/SiteContent.php';

Auth::startSession();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$db = (new Database())->getConnection();
$content = new SiteContent($db);

// Handle save
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'save') {
    $content->set($_POST['page'], $_POST['section'], $_POST['key_name'], $_POST['value'], $_POST['type'] ?? 'text', (int)($_POST['sort_order'] ?? 0));
    $msg = "Saved: {$_POST['page']} / {$_POST['section']} / {$_POST['key_name']}";
}

$allContent = $content->getAll();
$pages = $content->getPages();
$currentPage = $_GET['page'] ?? ($pages[0] ?? 'home');
$currentSections = $content->getSections($currentPage);
$currentSection = $_GET['section'] ?? ($currentSections[0] ?? 'hero');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Manager — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #e8e5db; }
        .card { background: #faf8f4; border: 1px solid #e1ddd4; }
        .inp { width:100%; border:1px solid #d6d2c8; padding:0.5rem 0.75rem; font-size:0.8125rem; outline:none; background:transparent; transition:border-color 0.2s; }
        .inp:focus { border-color:#3a322c; }
        .inp-lg { width:100%; border:1px solid #d6d2c8; padding:0.75rem; font-size:0.875rem; outline:none; background:transparent; min-height:120px; resize:vertical; }
        .inp-lg:focus { border-color:#3a322c; }
        .btn { padding:0.5rem 1.25rem; font-size:0.6875rem; letter-spacing:0.08em; text-transform:uppercase; cursor:pointer; transition:all 0.2s; border:none; }
        .btn-primary { background:#3a322c; color:#faf8f4; }
        .btn-primary:hover { background:#2a2420; }
        .nav-tab { padding:0.5rem 1rem; font-size:0.6875rem; letter-spacing:0.08em; text-transform:uppercase; cursor:pointer; border:1px solid transparent; transition:all 0.2s; color:#5c5349; }
        .nav-tab:hover { color:#1c1b18; }
        .nav-tab.active { background:#faf8f4; border-color:#d6d2c8; color:#1c1b18; }
        .toast { position:fixed; bottom:2rem; right:2rem; background:#3a322c; color:#faf8f4; padding:0.75rem 1.5rem; font-size:0.75rem; z-index:50; opacity:0; transition:opacity 0.3s; }
        .toast.show { opacity:1; }
        ::-webkit-scrollbar { width:6px; }
        ::-webkit-scrollbar-track { background:#e1ddd4; }
        ::-webkit-scrollbar-thumb { background:#9a9086; }
    </style>
</head>
<body class="min-h-screen">
    <?php if (isset($msg)): ?><div class="toast show" id="toast"><?php echo $msg; ?></div><?php endif; ?>

    <div class="flex h-screen">
        <div class="w-56 bg-[#f2efe9] border-r border-[#e1ddd4] p-6 flex flex-col shrink-0">
            <a href="../../index.html" class="inline-flex items-center gap-2 mb-8 no-underline">
                <span class="w-6 h-6 bg-[#3a322c] text-[#e8e5db] text-[0.5625rem] font-semibold flex items-center justify-center">A</span>
                <span class="text-[0.625rem] tracking-[0.15em] uppercase text-[#1c1b18]">CMS</span>
            </a>
            <p class="text-[0.5625rem] uppercase tracking-[0.15em] text-[#5c5349] mb-3">Pages</p>
            <nav class="flex flex-col gap-1">
                <?php foreach ($pages as $p): ?>
                    <a href="?page=<?php echo $p; ?>" class="nav-tab text-left <?php echo $p === $currentPage ? 'active' : ''; ?>"><?php echo ucfirst($p); ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="mt-auto pt-6 border-t border-[#e1ddd4]">
                <a href="dashboard.php" class="text-[0.6875rem] text-[#5c5349] hover:text-[#1c1b18] transition-colors no-underline">&larr; Dashboard</a>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="flex items-center gap-3 mb-8">
                <h1 class="text-lg font-medium text-[#1c1b18] capitalize"><?php echo $currentPage; ?></h1>
                <span class="text-[0.5625rem] text-[#9a9086] uppercase tracking-[0.1em]">Content Editor</span>
            </div>

            <div class="flex gap-2 mb-6 flex-wrap">
                <?php foreach ($currentSections as $sec): ?>
                    <a href="?page=<?php echo $currentPage; ?>&section=<?php echo $sec; ?>" class="nav-tab <?php echo $sec === $currentSection ? 'active' : ''; ?>"><?php echo ucfirst(str_replace('_', ' ', $sec)); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="card p-6">
                <form method="post" class="space-y-4">
                    <input type="hidden" name="action" value="save">
                    <?php
                    $items = $content->get($currentPage, $currentSection);
                    $idx = 0;
                    foreach ($items as $key => $val):
                        $meta = [];
                        $stmt = $db->prepare("SELECT * FROM site_content WHERE page = ? AND section = ? AND key_name = ?");
                        $stmt->execute([$currentPage, $currentSection, $key]);
                        $row = $stmt->fetch();
                    ?>
                        <div class="border-b border-[#e1ddd4] pb-4 <?php echo $idx > 0 ? 'pt-4' : ''; ?>">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-[0.625rem] uppercase tracking-[0.08em] text-[#5c5349] font-mono"><?php echo $key; ?></label>
                                <span class="text-[0.5rem] text-[#9a9086]"><?php echo $row['type'] ?? 'text'; ?></span>
                            </div>
                            <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
                            <input type="hidden" name="section" value="<?php echo $currentSection; ?>">
                            <input type="hidden" name="key_name[]" value="<?php echo $key; ?>">
                            <input type="hidden" name="type[]" value="<?php echo $row['type'] ?? 'text'; ?>">
                            <input type="hidden" name="sort_order[]" value="<?php echo $row['sort_order'] ?? $idx; ?>">
                            <?php if (($row['type'] ?? 'text') === 'textarea'): ?>
                                <textarea name="value[]" class="inp-lg"><?php echo htmlspecialchars($val); ?></textarea>
                            <?php else: ?>
                                <input type="text" name="value[]" value="<?php echo htmlspecialchars($val); ?>" class="inp">
                            <?php endif; ?>
                        </div>
                    <?php $idx++; endforeach; ?>
                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const t = document.getElementById('toast');
        if (t) setTimeout(() => t.classList.remove('show'), 3000);
    </script>
</body>
</html>
