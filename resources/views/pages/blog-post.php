<?php
require_once __DIR__ . '/../../../src/config/database.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$database = new Database();
$db = $database->getConnection();

$post = null;
try {
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

if (!$post) {
    // Static fallback for demo
    $samplePosts = [
        'embodied-carbon-heritage' => [
            'title' => 'Embodied carbon in heritage buildings: A counterintuitive case',
            'category' => 'Project Stories',
            'author' => 'Lukas Walker',
            'published_at' => '2026-05-15',
            'cover_image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&q=80&w=1200',
            'content' => "<p>When we think about sustainable construction, heritage buildings rarely come to mind. They are old, often draughty, and typically associated with inefficient energy performance. But the calculus around embodied carbon tells a different story.</p><p>A recent study comparing the full lifecycle emissions of a heritage retrofit versus a new-build replacement found that the retrofit option saved over 40% in total carbon emissions — even when the new building achieved Passivhaus standards. The reason is simple: demolishing and rebuilding releases massive amounts of stored carbon, both from the materials themselves and from the construction process.</p><p>At Aura Estates, we specialise in managing heritage and listed properties. Our approach has always been to preserve and adapt rather than replace. The environmental data now confirms what our instincts told us from the start: the greenest building is often the one that already exists.</p><p>This has practical implications for property owners. Instead of viewing an older building as a liability, it should be seen as a carbon asset. With thoughtful upgrades — improved insulation, modern glazing, efficient HVAC systems — heritage properties can achieve excellent performance while retaining their character and significantly lower embodied carbon footprint.</p>"
        ],
        'draw-by-hand' => [
            'title' => 'Why we still draw by hand before we draw on screen',
            'category' => 'Design Insights',
            'author' => 'Hanna Bennett',
            'published_at' => '2026-04-28',
            'cover_image' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&q=80&w=1200',
            'content' => "<p>In an age of sophisticated BIM software and parametric design tools, the question inevitably arises: why do we still draw by hand? The answer is simpler than you might think.</p><p>Hand drawing is not about nostalgia. It is about thinking at the right speed. When you draw by hand, your hand moves at roughly the same pace as your thoughts. This creates a feedback loop that is immediate, tactile, and deeply connected to the design process. You can explore variations, test ideas, and discard them without the friction of software interfaces.</p><p>We begin every new project with hand sketches. These are not polished presentation drawings — they are thinking tools. They allow us to explore proportions, relationships, and spatial qualities before committing to a digital model. The computer is an extraordinary tool for precision, coordination, and documentation. But it is a poor tool for the early, exploratory phase of design.</p><p>Our practice follows a simple workflow: hand sketch, physical model, digital model. Each phase builds on the previous one, and each brings a different quality of thinking to the process. The result is work that feels considered, human, and grounded — because it is.</p>"
        ]
    ];
    $post = $samplePosts[$slug] ?? null;
    if (!$post) {
        header('Location: blog.php');
        exit;
    }
    $post['slug'] = $slug;
}

$pageTitle = $post['title'];
$currentPage = 'blog';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<article class="py-24 lg:py-32" data-reveal>
    <div class="max-w-[48rem] mx-auto px-6 lg:px-12">
        <div class="mb-8" data-reveal>
            <div class="flex flex-wrap gap-3 font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-4">
                <span class="border border-border px-3 py-1"><?php echo htmlspecialchars($post['category']); ?></span>
                <span class="border border-border px-3 py-1"><?php echo date('M j, Y', strtotime($post['published_at'])); ?></span>
                <span class="border border-border px-3 py-1">By <?php echo htmlspecialchars($post['author']); ?></span>
            </div>
            <h1 class="font-sans font-medium text-[1.75rem] lg:text-[2.5rem] leading-[1.1] text-ink" data-split><?php echo htmlspecialchars($post['title']); ?></h1>
        </div>

        <div class="aspect-[16/9] bg-surface border border-border-light overflow-hidden mb-10" data-reveal>
            <img src="<?php echo htmlspecialchars($post['cover_image']); ?>" class="w-full h-full object-cover" alt="<?php echo htmlspecialchars($post['title']); ?>" data-image-reveal>
        </div>

        <div class="font-sans text-[0.9375rem] lg:text-[1rem] leading-[1.8] text-ink-secondary space-y-6">
            <?php echo $post['content']; ?>
        </div>

        <div class="mt-12 pt-8 border-t border-border flex items-center justify-between">
            <a href="/blog" class="flex items-center gap-2 font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary hover:text-ink transition-colors no-underline">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M11 7H3M3 7l4-4M3 7l4 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to Journal
            </a>
            <div class="flex gap-4 font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted">
                <span>Share:</span>
                <a href="#" class="hover:text-ink transition-colors no-underline">Twitter</a>
                <a href="#" class="hover:text-ink transition-colors no-underline">LinkedIn</a>
                <a href="#" class="hover:text-ink transition-colors no-underline">Email</a>
            </div>
        </div>
    </div>
</article>

<?php include __DIR__ . '/../partials/footer.php'; ?>