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

if (empty($properties)) {
    $properties = [
        ['id'=>1,'title'=>'The Sapphire Penthouse','description'=>'A stunning penthouse with panoramic ocean views and private elevator access.','price'=>5000000,'location'=>'Beverly Hills, CA','property_type'=>'Penthouse','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>4500,'main_image'=>'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000','latitude'=>34.0736,'longitude'=>-118.4004],
        ['id'=>2,'title'=>'Onyx Villa','description'=>'Modern architectural masterpiece nestled in the hills with infinity pool.','price'=>3500000,'location'=>'Malibu, CA','property_type'=>'Villa','bedrooms'=>5,'bathrooms'=>6,'area_sqft'=>6000,'main_image'=>'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000','latitude'=>34.0259,'longitude'=>-118.7798],
        ['id'=>3,'title'=>'Emerald Estate','description'=>'Classic luxury estate with sprawling gardens and tennis court.','price'=>8200000,'location'=>'Hamptons, NY','property_type'=>'Estate','bedrooms'=>7,'bathrooms'=>8,'area_sqft'=>12000,'main_image'=>'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000','latitude'=>40.9006,'longitude'=>-72.3018],
        ['id'=>4,'title'=>'Golden Loft','description'=>'Industrial chic loft in the heart of the city with floor-to-ceiling windows.','price'=>1200000,'location'=>'Tribeca, NY','property_type'=>'Loft','bedrooms'=>2,'bathrooms'=>2,'area_sqft'=>2500,'main_image'=>'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000','latitude'=>40.7178,'longitude'=>-74.0060],
        ['id'=>5,'title'=>'Crystal Waters Estate','description'=>'A breathtaking waterfront estate with private dock, infinity pool, and panoramic ocean views.','price'=>7200000,'location'=>'Miami Beach, FL','property_type'=>'Estate','bedrooms'=>6,'bathrooms'=>7,'area_sqft'=>8500,'main_image'=>'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000','latitude'=>25.7907,'longitude'=>-80.1300],
        ['id'=>6,'title'=>'The Ivory Tower','description'=>'Minimalist penthouse occupying the entire top floor with 360-degree city views.','price'=>4500000,'location'=>'Manhattan, NY','property_type'=>'Penthouse','bedrooms'=>3,'bathrooms'=>4,'area_sqft'=>3800,'main_image'=>'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&q=80&w=1000','latitude'=>40.7614,'longitude'=>-73.9716],
        ['id'=>7,'title'=>'Villa del Sol','description'=>'Mediterranean-inspired villa surrounded by olive groves with a private vineyard.','price'=>2800000,'location'=>'Santa Barbara, CA','property_type'=>'Villa','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>5500,'main_image'=>'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000','latitude'=>34.4208,'longitude'=>-119.6982],
        ['id'=>8,'title'=>'The Industrial Loft','description'=>'Converted warehouse with exposed brick walls, 20-foot ceilings, and curated interiors.','price'=>950000,'location'=>'Brooklyn, NY','property_type'=>'Loft','bedrooms'=>2,'bathrooms'=>2,'area_sqft'=>2200,'main_image'=>'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&q=80&w=1000','latitude'=>40.7128,'longitude'=>-73.9654],
        ['id'=>9,'title'=>'Azure Cliffs Residence','description'=>'Sculptural concrete and glass masterpiece cantilevered over the Pacific Ocean.','price'=>9800000,'location'=>'Big Sur, CA','property_type'=>'Estate','bedrooms'=>5,'bathrooms'=>6,'area_sqft'=>7200,'main_image'=>'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000','latitude'=>36.2704,'longitude'=>-121.8081],
        ['id'=>10,'title'=>'The Metropolitan','description'=>'Sleek modern penthouse in the financial district with smart home automation.','price'=>3200000,'location'=>'San Francisco, CA','property_type'=>'Penthouse','bedrooms'=>3,'bathrooms'=>3,'area_sqft'=>3100,'main_image'=>'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&q=80&w=1000','latitude'=>37.7749,'longitude'=>-122.4194],
        ['id'=>11,'title'=>'Amalfi Cliff Residence','description'=>'Perched above the Pacific, this glass-and-stone villa features cantilevered terraces over the ocean.','price'=>9750000,'location'=>'Pacific Palisades, CA','property_type'=>'Villa','bedrooms'=>6,'bathrooms'=>7,'area_sqft'=>7800,'main_image'=>'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000','latitude'=>34.0459,'longitude'=>-118.5260],
        ['id'=>12,'title'=>'The Monolith Tower Penthouse','description'=>'A triple-height penthouse crowning a 60-storey tower with 360-degree glazing.','price'=>14500000,'location'=>'Manhattan, NY','property_type'=>'Penthouse','bedrooms'=>5,'bathrooms'=>6,'area_sqft'=>8200,'main_image'=>'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000','latitude'=>40.7614,'longitude'=>-73.9716],
        ['id'=>13,'title'=>'Maison du Vignoble','description'=>'A 19th-century French estate reimagined with steel-and-glass extensions.','price'=>6400000,'location'=>'Napa Valley, CA','property_type'=>'Estate','bedrooms'=>8,'bathrooms'=>9,'area_sqft'=>14500,'main_image'=>'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000','latitude'=>38.2975,'longitude'=>-122.2869],
        ['id'=>14,'title'=>'Glacier Point Lodge','description'=>'A timber-and-glass mountain retreat inspired by Scandinavian stave churches.','price'=>4200000,'location'=>'Aspen, CO','property_type'=>'Lodge','bedrooms'=>5,'bathrooms'=>5,'area_sqft'=>5600,'main_image'=>'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1000','latitude'=>39.1869,'longitude'=>-106.8178],
        ['id'=>15,'title'=>'Dune House','description'=>'An earth-sheltered residence built into coastal dunes with a living green roof.','price'=>5800000,'location'=>'Montauk, NY','property_type'=>'House','bedrooms'=>4,'bathrooms'=>4,'area_sqft'=>4200,'main_image'=>'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1000','latitude'=>41.0704,'longitude'=>-71.9235],
        ['id'=>16,'title'=>'The Glass Pavilion','description'=>'A Miesian glass box reinterpreted for the desert with polished concrete floors.','price'=>7200000,'location'=>'Scottsdale, AZ','property_type'=>'Villa','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>6100,'main_image'=>'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&q=80&w=1000','latitude'=>33.4942,'longitude'=>-111.9261],
        ['id'=>17,'title'=>'Harbour View Tower','description'=>'A 42nd-floor residence in a sculptural waterfront tower with wraparound terrace.','price'=>8900000,'location'=>'Sydney, NSW','property_type'=>'Penthouse','bedrooms'=>3,'bathrooms'=>4,'area_sqft'=>3800,'main_image'=>'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000','latitude'=>-33.8568,'longitude'=>151.2153],
        ['id'=>18,'title'=>'Palazzo Nero','description'=>'A Venetian palazzo restored with museum-grade precision and private canal mooring.','price'=>11500000,'location'=>'Venice, IT','property_type'=>'Estate','bedrooms'=>7,'bathrooms'=>8,'area_sqft'=>11000,'main_image'=>'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000','latitude'=>45.4408,'longitude'=>12.3155],
        ['id'=>19,'title'=>'Cedar Bridge Farmhouse','description'=>'A timber-frame farmhouse on 12 acres with a geothermal-heated indoor pool.','price'=>3650000,'location'=>'Hudson Valley, NY','property_type'=>'Farmhouse','bedrooms'=>5,'bathrooms'=>4,'area_sqft'=>5200,'main_image'=>'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000','latitude'=>41.9845,'longitude'=>-73.9080],
        ['id'=>20,'title'=>'The Vertex','description'=>'A 28-storey sculptural tower with rotating floor plates and sky gardens.','price'=>6800000,'location'=>'Miami Beach, FL','property_type'=>'Penthouse','bedrooms'=>3,'bathrooms'=>4,'area_sqft'=>3600,'main_image'=>'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&q=80&w=1000','latitude'=>25.7907,'longitude'=>-80.1300],
        ['id'=>21,'title'=>'Amanoi Retreat','description'=>'A resort-inspired residence nestled in hillside jungle with private plunge pools.','price'=>4500000,'location'=>'Tulum, MX','property_type'=>'Villa','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>4800,'main_image'=>'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000','latitude'=>20.2145,'longitude'=>-87.4291],
        ['id'=>22,'title'=>'The Foundry','description'=>'A converted ironworks with triple-height spaces and raw steel trusses.','price'=>5100000,'location'=>'Brooklyn, NY','property_type'=>'Loft','bedrooms'=>3,'bathrooms'=>3,'area_sqft'=>4500,'main_image'=>'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&q=80&w=1000','latitude'=>40.7128,'longitude'=>-73.9654],
        ['id'=>23,'title'=>'Villa Aether','description'=>'A cantilevered concrete and timber villa hovering above a private cove.','price'=>12800000,'location'=>'Santorini, GR','property_type'=>'Villa','bedrooms'=>6,'bathrooms'=>6,'area_sqft'=>7200,'main_image'=>'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?auto=format&fit=crop&q=80&w=1000','latitude'=>36.3932,'longitude'=>25.4615],
        ['id'=>24,'title'=>'Maison Terre','description'=>'A rammed-earth compound in the hills above Malibu with reflecting pool.','price'=>7500000,'location'=>'Malibu, CA','property_type'=>'Compound','bedrooms'=>6,'bathrooms'=>7,'area_sqft'=>8500,'main_image'=>'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?auto=format&fit=crop&q=80&w=1000','latitude'=>34.0259,'longitude'=>-118.7798],
        ['id'=>25,'title'=>'The Observatory','description'=>'A cylindrical glass residence with a rotating living room platform.','price'=>5400000,'location'=>'Joshua Tree, CA','property_type'=>'House','bedrooms'=>3,'bathrooms'=>3,'area_sqft'=>3200,'main_image'=>'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&q=80&w=1000','latitude'=>34.1226,'longitude'=>-116.3131],
        ['id'=>26,'title'=>'Schwarzwald Chalet','description'=>'A Black Forest-inspired timber chalet with heated infinity pool.','price'=>3900000,'location'=>'Whistler, BC','property_type'=>'Chalet','bedrooms'=>6,'bathrooms'=>5,'area_sqft'=>6400,'main_image'=>'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1000','latitude'=>50.1163,'longitude'=>-122.9574],
        ['id'=>27,'title'=>'Skybridge Residences','description'=>'Two towers connected by a sky bridge with shared infinity pool on 40th floor.','price'=>8200000,'location'=>'Dubai, UAE','property_type'=>'Penthouse','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>5100,'main_image'=>'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000','latitude'=>25.1972,'longitude'=>55.2744],
        ['id'=>28,'title'=>'The Copper House','description'=>'A weathered copper-clad residence that evolves with the seasons.','price'=>4800000,'location'=>'Portland, OR','property_type'=>'House','bedrooms'=>4,'bathrooms'=>4,'area_sqft'=>4100,'main_image'=>'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000','latitude'=>45.5155,'longitude'=>-122.6789],
        ['id'=>29,'title'=>'Marina Bay Grand','description'=>'A waterfront duplex penthouse with private marina berth.','price'=>10200000,'location'=>'Singapore','property_type'=>'Penthouse','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>5800,'main_image'=>'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000','latitude'=>1.2647,'longitude'=>103.8222],
        ['id'=>30,'title'=>'The Lighthouse','description'=>'A converted Victorian lighthouse with glass-walled upper floor.','price'=>2800000,'location'=>'Big Sur, CA','property_type'=>'House','bedrooms'=>3,'bathrooms'=>3,'area_sqft'=>2800,'main_image'=>'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1000','latitude'=>36.2704,'longitude'=>-121.8081],
        ['id'=>31,'title'=>'Orchid Court','description'=>'A heritage-listed Georgian townhouse with subterranean spa.','price'=>9100000,'location'=>'London, UK','property_type'=>'Townhouse','bedrooms'=>6,'bathrooms'=>5,'area_sqft'=>6800,'main_image'=>'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000','latitude'=>51.5074,'longitude'=>-0.1278],
    ];
}

$pageTitle = 'Properties';
$currentPage = 'properties';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%); padding: 5rem 0 3rem;">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <p class="font-mono text-[0.75rem] lg:text-[0.875rem] tracking-[-0.02em] uppercase text-ink-secondary opacity-60 mb-3">Portfolio</p>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink mb-4">Our Properties</h1>
        <p class="font-sans text-[1rem] lg:text-[1.125rem] text-ink-secondary max-w-[42rem] leading-relaxed">Explore our curated collection of exceptional residences across the world's most coveted addresses.</p>
    </div>
</section>

<section class="py-12 lg:py-20" data-reveal>
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">

        <form method="GET" class="mb-12 p-6 lg:p-8 bg-surface border border-border-light rounded-xl" data-reveal>
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
        <div class="mb-12" data-reveal>
            <div id="properties-map" class="w-full h-[300px] lg:h-[400px] bg-bg-alt border border-border-light rounded-xl overflow-hidden"></div>
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
        <div class="flex items-baseline justify-between mb-8">
            <p class="font-mono text-[0.625rem] tracking-[0.02em] uppercase text-muted"><?php echo count($properties); ?> propert<?php echo count($properties) !== 1 ? 'ies' : 'y'; ?> found</p>
            <div class="hidden lg:flex gap-2">
                <button class="px-3 py-1.5 text-[0.6875rem] font-mono uppercase tracking-wider border border-border-light rounded-md text-muted hover:text-ink hover:border-ink transition-colors" onclick="document.querySelectorAll('.property-card').forEach(c=>c.style.display='');">All</button>
                <button class="px-3 py-1.5 text-[0.6875rem] font-mono uppercase tracking-wider border border-border-light rounded-md text-muted hover:text-ink hover:border-ink transition-colors" onclick="filterCards('Villa')">Villa</button>
                <button class="px-3 py-1.5 text-[0.6875rem] font-mono uppercase tracking-wider border border-border-light rounded-md text-muted hover:text-ink hover:border-ink transition-colors" onclick="filterCards('Penthouse')">Penthouse</button>
                <button class="px-3 py-1.5 text-[0.6875rem] font-mono uppercase tracking-wider border border-border-light rounded-md text-muted hover:text-ink hover:border-ink transition-colors" onclick="filterCards('Estate')">Estate</button>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 lg:gap-6">
            <?php foreach ($properties as $p): ?>
            <a href="/property-detail/<?php echo $p['id']; ?>" class="property-card no-underline" data-type="<?php echo htmlspecialchars($p['property_type']); ?>">
                <div class="overflow-hidden">
                    <?php if (!empty($p['main_image'])): ?>
                    <img src="<?php echo htmlspecialchars($p['main_image']); ?>" loading="lazy" alt="<?php echo htmlspecialchars($p['title']); ?>" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-full aspect-[16/10] bg-bg-alt flex items-center justify-center\'><i class=\'fas fa-image text-2xl text-muted\'></i></div>';">
                    <?php else: ?>
                    <div class="w-full aspect-[16/10] bg-bg-alt flex items-center justify-center"><i class="fas fa-image text-2xl text-muted"></i></div>
                    <?php endif; ?>
                </div>
                <div class="p-4 lg:p-5 flex flex-col flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-block px-2 py-0.5 text-[0.5625rem] font-mono uppercase tracking-wider bg-bg-alt text-muted rounded"><?php echo htmlspecialchars($p['property_type']); ?></span>
                    </div>
                    <h3 class="font-sans font-medium text-[1rem] leading-[1.25] text-ink mb-1"><?php echo htmlspecialchars($p['title']); ?></h3>
                    <p class="font-mono text-[0.625rem] tracking-[-0.02em] uppercase text-muted mb-3"><?php echo htmlspecialchars($p['location']); ?></p>
                    <div class="flex items-center gap-3 mt-auto pt-3 border-t border-border-light font-mono text-[0.625rem] text-muted">
                        <span class="flex items-center gap-1"><i class="fas fa-bed text-[0.5rem]"></i> <?php echo $p['bedrooms']; ?></span>
                        <span class="flex items-center gap-1"><i class="fas fa-bath text-[0.5rem]"></i> <?php echo $p['bathrooms']; ?></span>
                        <span class="flex items-center gap-1"><i class="fas fa-ruler-combined text-[0.5rem]"></i> <?php echo number_format($p['area_sqft']); ?></span>
                        <span class="ml-auto text-ink font-medium">$<?php echo number_format($p['price']); ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<script>
function filterCards(type) {
    document.querySelectorAll('.property-card').forEach(function(card) {
        card.style.display = (card.dataset.type === type) ? '' : 'none';
    });
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>