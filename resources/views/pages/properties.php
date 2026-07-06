<?php
require_once __DIR__ . '/../../../src/config/database.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$minPrice = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 0;
$bedrooms = isset($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : 0;

$properties = [];
try {
    $database = new Database();
    $db = $database->getConnection();

    $sql = "SELECT * FROM properties WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $s = "%$search%";
        $params[] = $s;
        $params[] = $s;
        $params[] = $s;
    }
    if (!empty($type)) {
        $sql .= " AND property_type = ?";
        $params[] = $type;
    }
    if ($minPrice > 0) {
        $sql .= " AND price >= ?";
        $params[] = $minPrice;
    }
    if ($maxPrice > 0) {
        $sql .= " AND price <= ?";
        $params[] = $maxPrice;
    }
    if ($bedrooms > 0) {
        $sql .= " AND bedrooms >= ?";
        $params[] = $bedrooms;
    }

    $sql .= " ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $properties = [];
}

$pageTitle = 'Properties';
$currentPage = 'properties';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero bg-bg-alt" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%);">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-4">Portfolio</p>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink">Our Properties</h1>
    </div>
</section>

<section class="py-16 lg:py-24" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <form method="GET" class="mb-10 p-6 bg-surface border border-border-light" data-reveal>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label class="input-label">Search</label>
                    <input type="text" name="search" class="input-field" placeholder="Search by title, location, or keyword..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div>
                    <label class="input-label">Type</label>
                    <select name="type" class="input-field appearance-none bg-surface cursor-pointer">
                        <option value="">All Types</option>
                        <option value="Villa" <?php echo $type === 'Villa' ? 'selected' : ''; ?>>Villas</option>
                        <option value="Penthouse" <?php echo $type === 'Penthouse' ? 'selected' : ''; ?>>Penthouses</option>
                        <option value="Loft" <?php echo $type === 'Loft' ? 'selected' : ''; ?>>Lofts</option>
                        <option value="Apartment" <?php echo $type === 'Apartment' ? 'selected' : ''; ?>>Apartments</option>
                        <option value="Estate" <?php echo $type === 'Estate' ? 'selected' : ''; ?>>Estates</option>
                    </select>
                </div>
                <div>
                    <label class="input-label">Min. Bedrooms</label>
                    <select name="bedrooms" class="input-field appearance-none bg-surface cursor-pointer">
                        <option value="">Any</option>
                        <option value="1" <?php echo $bedrooms === 1 ? 'selected' : ''; ?>>1+</option>
                        <option value="2" <?php echo $bedrooms === 2 ? 'selected' : ''; ?>>2+</option>
                        <option value="3" <?php echo $bedrooms === 3 ? 'selected' : ''; ?>>3+</option>
                        <option value="4" <?php echo $bedrooms === 4 ? 'selected' : ''; ?>>4+</option>
                        <option value="5" <?php echo $bedrooms === 5 ? 'selected' : ''; ?>>5+</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full justify-center">Filter</button>
                </div>
            </div>
        </form>

        <?php
        $mapProperties = array_filter($properties, function($p) {
            return !empty($p['latitude']) && !empty($p['longitude']);
        });
        $mapProperties = array_values($mapProperties);
        ?>
        <?php if (count($mapProperties) > 0): ?>
        <div class="mb-10" data-reveal>
            <div id="properties-map" class="w-full h-[350px] lg:h-[450px] bg-bg-alt border border-border-light rounded-2xl overflow-hidden"></div>
        </div>
        <script>
        (function() {
            function initMap() {
                if (typeof AuraMaps !== 'undefined' && window.__googleMapsReady) {
                    var props = <?php echo json_encode(array_map(function($p) {
                        return [
                            'id' => $p['id'],
                            'title' => $p['title'],
                            'location' => $p['location'],
                            'property_type' => $p['property_type'],
                            'bedrooms' => $p['bedrooms'],
                            'bathrooms' => $p['bathrooms'],
                            'area_sqft' => $p['area_sqft'],
                            'price' => $p['price'],
                            'main_image' => $p['main_image'],
                            'latitude' => $p['latitude'],
                            'longitude' => $p['longitude']
                        ];
                    }, $mapProperties), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                    AuraMaps.initMultiPropertyMap('properties-map', props);
                }
            }
            if (window.__googleMapsReady) { initMap(); }
            else { document.addEventListener('google-maps-ready', initMap); }
        })();
        </script>
        <?php endif; ?>

        <?php if (count($properties) === 0): ?>
        <div class="text-center py-16 lg:py-24">
            <i class="fas fa-search text-3xl text-muted mb-4"></i>
            <p class="font-sans text-[1.125rem] text-ink-secondary">No properties match your criteria.</p>
            <a href="/properties" class="btn-primary mt-6 inline-flex">Clear Filters</a>
        </div>
        <?php else: ?>
        <p class="font-mono text-[0.625rem] tracking-[0.02em] uppercase text-muted mb-6"><?php echo count($properties); ?> property<?php echo count($properties) !== 1 ? 'ies' : 'y'; ?> found</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8" data-stagger>
            <?php foreach ($properties as $p): ?>
            <a href="/property-detail?id=<?php echo $p['id']; ?>" class="property-card no-underline" data-stagger-item>
                <?php if (!empty($p['main_image'])): ?>
                <img src="<?php echo htmlspecialchars($p['main_image']); ?>" loading="lazy" onerror="this.onerror=null;this.src='resources/placeholders/property.svg';">
                <?php else: ?>
                <div class="w-full aspect-[16/10] bg-surface flex items-center justify-center"><i class="fas fa-building text-3xl text-muted"></i></div>
                <?php endif; ?>
                <div class="p-4 lg:p-5">
                    <div class="font-mono text-[0.625rem] tracking-[-0.02em] uppercase text-muted flex gap-4 mb-2">
                        <span><?php echo htmlspecialchars($p['property_type']); ?></span>
                        <span><?php echo htmlspecialchars($p['location']); ?></span>
                    </div>
                    <h3 class="font-sans font-medium text-[1.125rem] leading-[1.2] text-ink mb-1"><?php echo htmlspecialchars($p['title']); ?></h3>
                    <p class="font-sans text-[0.875rem] leading-[1.5] text-ink-secondary opacity-80"><?php echo htmlspecialchars(substr($p['description'] ?? '', 0, 120)); ?>...</p>
                    <div class="flex gap-4 mt-3 font-mono text-[0.625rem] text-muted">
                        <span><?php echo $p['bedrooms']; ?> bed</span>
                        <span><?php echo $p['bathrooms']; ?> bath</span>
                        <span><?php echo number_format($p['area_sqft']); ?> ft&sup2;</span>
                        <span class="ml-auto text-ink font-medium">$<?php echo number_format($p['price']); ?><span class="font-normal text-muted">/mo</span></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../partials/footer.php'; ?>