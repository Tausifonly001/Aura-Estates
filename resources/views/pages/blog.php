<?php
require_once __DIR__ . '/../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Check if blog table exists
$posts = [];
try {
    $stmt = $db->query("SELECT id, title, slug, excerpt, author, category, published_at, cover_image FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table doesn't exist yet
}

$pageTitle = 'Journal';
$currentPage = 'blog';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero bg-bg-alt" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%);">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4">Journal</p>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink">Insights from the studio.</h1>
    </div>
</section>

        <section class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <?php if (count($posts) === 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12" data-stagger>
            <a href="#" class="journal-card group" data-stagger-item>
                <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80&w=800" loading="lazy" data-image-reveal>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-2">Project Stories</p>
                <h3 class="font-sans font-medium text-[1rem] lg:text-[1.125rem] leading-[1.3] text-ink mb-4">Embodied carbon in heritage buildings: A counterintuitive case</h3>
                <div class="flex items-center justify-between text-ink group-hover:text-accent transition-colors py-3">
                    <span class="font-sans font-medium text-[1rem] tracking-[0.05em] uppercase">Read</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 15L15 1M15 1H5M15 1V11" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </a>
            <a href="#" class="journal-card group" data-stagger-item>
                <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&q=80&w=800" loading="lazy" data-image-reveal>
                <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-2">Design Insights</p>
                <h3 class="font-sans font-medium text-[1rem] lg:text-[1.125rem] leading-[1.3] text-ink mb-4">Why we still draw by hand before we draw on screen</h3>
                <div class="flex items-center justify-between text-ink group-hover:text-accent transition-colors py-3">
                    <span class="font-sans font-medium text-[1rem] tracking-[0.05em] uppercase">Read</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 15L15 1M15 1H5M15 1V11" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </a>
        </div>
        <div class="text-center mt-12 p-8 bg-surface border border-border-light" data-reveal>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12" data-stagger>
            <?php foreach ($posts as $post): ?>
            <a href="blog-post.php?slug=<?php echo urlencode($post['slug']); ?>" class="journal-card group" data-stagger-item>
                <img src="<?php echo htmlspecialchars($post['cover_image'] ?: 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80&w=800'); ?>" loading="lazy" data-image-reveal>
                <div class="flex gap-3 font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-2">
                    <span><?php echo htmlspecialchars($post['category']); ?></span>
                    <span>&middot;</span>
                    <span><?php echo date('M j, Y', strtotime($post['published_at'])); ?></span>
                </div>
                <h3 class="font-sans font-medium text-[1rem] lg:text-[1.125rem] leading-[1.3] text-ink mb-2"><?php echo htmlspecialchars($post['title']); ?></h3>
                <p class="font-sans text-[0.875rem] leading-[1.6] text-ink-secondary mb-4"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                <div class="flex items-center justify-between text-ink group-hover:text-accent transition-colors py-3">
                    <span class="font-sans font-medium text-[1rem] tracking-[0.05em] uppercase">Read</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1 15L15 1M15 1H5M15 1V11" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>