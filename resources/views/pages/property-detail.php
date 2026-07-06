<?php
require_once __DIR__ . '/../../../src/config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$property = null;
try {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

if (!$property) {
    header('Location: properties');
    exit;
}

$pageTitle = $property['title'];
$currentPage = 'properties';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero bg-bg-alt" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%);">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <div class="flex flex-wrap gap-3 font-mono text-[0.625rem] lg:text-[0.75rem] tracking-[-0.02em] uppercase text-muted mb-4">
            <span class="border border-border px-3 py-1"><?php echo htmlspecialchars($property['property_type']); ?></span>
            <span class="border border-border px-3 py-1"><?php echo htmlspecialchars($property['location']); ?></span>
            <?php if (!empty($property['status'])): ?>
            <span class="border border-border px-3 py-1"><?php echo htmlspecialchars($property['status']); ?></span>
            <?php endif; ?>
        </div>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink max-w-[20ch]" data-split><?php echo htmlspecialchars($property['title']); ?></h1>
    </div>
</section>

<section class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 lg:gap-20">
            <div class="lg:col-span-3">
                <div class="aspect-[16/10] bg-surface border border-border-light overflow-hidden mb-8">
                    <?php if (!empty($property['main_image'])): ?>
                    <img src="<?php echo htmlspecialchars($property['main_image']); ?>" class="w-full h-full object-cover" alt="<?php echo htmlspecialchars($property['title']); ?>" data-image-reveal onerror="this.onerror=null;this.src='resources/placeholders/property.svg';">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center"><i class="fas fa-building text-4xl text-muted"></i></div>
                    <?php endif; ?>
                </div>
                <div class="flex gap-6 mb-8 font-mono text-[0.625rem] lg:text-[0.75rem] text-muted">
                    <span><strong class="text-ink"><?php echo $property['bedrooms']; ?></strong> bed</span>
                    <span><strong class="text-ink"><?php echo $property['bathrooms']; ?></strong> bath</span>
                    <span><strong class="text-ink"><?php echo number_format($property['area_sqft']); ?></strong> ft&sup2;</span>
                </div>
                <div class="font-sans text-[0.9375rem] lg:text-[1rem] leading-[1.7] text-ink-secondary space-y-4">
                    <p><?php echo nl2br(htmlspecialchars($property['description'] ?? '')); ?></p>
                </div>
                <?php if (!empty($property['features'])): ?>
                <div class="mt-8 pt-8 border-t border-border">
                    <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-4">Features &amp; Amenities</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach (explode("\n", $property['features']) as $feature): ?>
                        <?php if (trim($feature)): ?>
                        <div class="flex items-center gap-2 font-sans text-[0.875rem] text-ink-secondary">
                            <svg class="w-3.5 h-3.5 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <?php echo htmlspecialchars(trim($feature)); ?>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="lg:col-span-2" data-reveal>
                    <p class="font-sans font-medium text-[1.5rem] lg:text-[2.125rem] text-ink mb-1">$<?php echo number_format($property['price']); ?> <span class="font-normal text-muted text-[1rem]">/mo</span></p>
                    <p class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-muted mb-6"><?php echo htmlspecialchars($property['property_type']); ?> &middot; <?php echo htmlspecialchars($property['location']); ?></p>
                    <p class="font-sans font-medium text-[1rem] text-ink mb-4">Interested in this property?</p>
                    <form action="/contact" method="GET" class="flex flex-col gap-4">
                        <input type="hidden" name="property" value="<?php echo $property['id']; ?>">
                        <input type="text" name="name" class="input-field" placeholder="Your name" required>
                        <input type="email" name="email" class="input-field" placeholder="Your email" required>
                        <textarea name="message" class="input-field resize-y leading-relaxed" rows="4" placeholder="I'm interested in <?php echo htmlspecialchars($property['title']); ?>..." required></textarea>
                        <button type="submit" class="btn-primary w-full justify-center mt-2">Send Inquiry</button>
                    </form>
                    <div class="mt-6 pt-6 border-t border-border-light">
                        <a href="/properties" class="flex items-center gap-2 font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary hover:text-ink transition-colors no-underline">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M11 7H3M3 7l4-4M3 7l4 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Back to Properties
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
<section class="py-16 lg:py-24 border-t border-border/40" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="flex flex-col gap-4 mb-8">
            <p class="font-mono text-[0.625rem] tracking-[0.1em] uppercase text-muted">Location</p>
            <h2 class="font-sans font-light text-[2rem] lg:text-[2.75rem] leading-[1.1] text-ink">
                <?php echo htmlspecialchars($property['location']); ?>
            </h2>
        </div>
        <div id="property-map" class="w-full h-[400px] lg:h-[500px] bg-bg-alt border border-border-light rounded-2xl overflow-hidden"></div>
        <div class="mt-6 flex flex-wrap gap-6">
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo htmlspecialchars($property['latitude'] . ',' . $property['longitude']); ?>"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary hover:text-accent transition-colors no-underline border border-border px-4 py-2 rounded-full">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Get Directions
            </a>
            <span class="font-mono text-[0.5625rem] tracking-[0.02em] text-muted self-center">
                LAT. <?php echo number_format((float)$property['latitude'], 4); ?>&deg; &nbsp; LONG. <?php echo number_format((float)$property['longitude'], 4); ?>&deg;
            </span>
        </div>
    </div>
</section>
<script>
(function() {
    function initMap() {
        if (typeof AuraMaps !== 'undefined' && window.__googleMapsReady) {
            AuraMaps.initSinglePropertyMap('property-map',
                '<?php echo htmlspecialchars($property['latitude']); ?>',
                '<?php echo htmlspecialchars($property['longitude']); ?>',
                <?php echo json_encode([
                    'title' => $property['title'],
                    'location' => $property['location'],
                    'property_type' => $property['property_type'],
                    'bedrooms' => $property['bedrooms'],
                    'bathrooms' => $property['bathrooms'],
                    'area_sqft' => $property['area_sqft'],
                    'price' => $property['price'],
                    'main_image' => $property['main_image']
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
            );
        }
    }
    if (window.__googleMapsReady) { initMap(); }
    else { document.addEventListener('google-maps-ready', initMap); }
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>