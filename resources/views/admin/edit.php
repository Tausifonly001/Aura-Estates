<?php
require_once __DIR__ . '/../../../src/config/auth.php';
require_once __DIR__ . '/../../../src/core/CsrfProtection.php';
Auth::requireStaff();
CsrfProtection::generate();

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/Property.php';

$database = new Database();
$db = $database->getConnection();
$property = new Property($db);

$id = $_GET['id'] ?? 0;
$property->id = $id;

if(!$property->readOne()){
    header("Location: dashboard.php");
    exit;
}

$message = "";

if($_POST){
    $property->title = $_POST['title'];
    $property->description = $_POST['description'];
    $property->price = $_POST['price'];
    $property->location = $_POST['location'];
    $property->latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $property->longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    $property->property_type = $_POST['property_type'];
    $property->bedrooms = $_POST['bedrooms'];
    $property->bathrooms = $_POST['bathrooms'];
    $property->area_sqft = $_POST['area_sqft'];
    $property->main_image = $_POST['main_image'] ?? '';
    $property->is_available = $_POST['is_available'] ?? 0;

    if($property->update()){
        header("Location: edit.php?id=" . $id . "&updated=1");
        exit;
    } else {
        $message = "Failed to update property.";
    }
}

$updated = $_GET['updated'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital@0;1&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { ink: '#0a0a0a', paper: '#f4efe6', rust: '#e76f51', sand: '#e9c46a', surface: '#121212' }, fontFamily: { display: ['Bodoni Moda', 'serif'], body: ['Inter', 'sans-serif'] } } }
        }
    </script>
    <style>body { font-family: 'Inter', sans-serif; background: #0a0a0a; color: #f4efe6; font-weight: 300; } h1, h2, h3 { font-family: 'Bodoni Moda', serif; } ::selection { background: #e76f51; }.noise-overlay { position: fixed; inset: 0; z-index: 9999; pointer-events: none; opacity: 0.015; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); background-repeat: repeat; background-size: 256px 256px; }</style>
</head>
<body class="antialiased min-h-screen">
    <div class="noise-overlay"></div>
    <div class="max-w-3xl mx-auto px-6 py-12">
        <a href="/admin/dashboard" class="text-[10px] font-body uppercase tracking-[0.25em] text-rust/60 hover:text-rust transition mb-8 inline-block">&larr; Back to Dashboard</a>
        
        <h1 class="text-5xl font-display italic text-paper mb-2">Edit Property</h1>
        <p class="text-sm text-paper/30 font-body font-light mb-10">Update property details.</p>

        <?php if($updated): ?>
            <div class="bg-sand/10 border border-sand/20 text-sand/80 p-4 mb-6 text-sm font-body">Property updated successfully!</div>
        <?php endif; ?>
        <?php if($message): ?>
            <div class="bg-rust/10 border border-rust/20 text-rust p-4 mb-6 text-sm font-body"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $id);?>" method="post" class="space-y-6">
            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($property->title); ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required><?php echo htmlspecialchars($property->description); ?></textarea>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Price ($)</label>
                    <input type="number" name="price" step="0.01" value="<?php echo $property->price; ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($property->location); ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Type</label>
                    <select name="property_type" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                        <?php foreach(['Villa','Penthouse','Estate','Loft'] as $t): ?>
                            <option value="<?php echo $t; ?>" class="bg-ink" <?php echo $property->property_type == $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Bedrooms</label>
                    <input type="number" name="bedrooms" value="<?php echo $property->bedrooms; ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Bathrooms</label>
                    <input type="number" name="bathrooms" value="<?php echo $property->bathrooms; ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Area (sqft)</label>
                    <input type="number" name="area_sqft" value="<?php echo $property->area_sqft; ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Image URL</label>
                    <input type="text" name="main_image" value="<?php echo htmlspecialchars($property->main_image); ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition">
                    <?php if($property->main_image): ?>
                        <img src="<?php echo htmlspecialchars($property->main_image); ?>" class="w-32 h-24 object-cover mt-3 border border-paper/10">
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2 border-t border-paper/10 pt-6 mt-2">
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Coordinates</label>
                    <p class="text-[10px] text-paper/20 font-body mb-3">Enter coordinates manually or use the geocode button to auto-fill from location.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Latitude</label>
                            <input type="text" name="latitude" id="field-latitude" value="<?php echo htmlspecialchars($property->latitude ?? ''); ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" placeholder="34.0736">
                        </div>
                        <div>
                            <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Longitude</label>
                            <input type="text" name="longitude" id="field-longitude" value="<?php echo htmlspecialchars($property->longitude ?? ''); ?>" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" placeholder="-118.4004">
                        </div>
                    </div>
                    <button type="button" onclick="geocodeLocation()" class="mt-3 border border-rust/40 text-rust/70 py-2 px-4 text-[9px] font-body font-bold uppercase tracking-[0.2em] hover:bg-rust/10 transition">Geocode from Location</button>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <input type="checkbox" name="is_available" value="1" <?php echo $property->is_available ? 'checked' : ''; ?> class="rounded bg-paper/[0.02] border-paper/10">
                <label class="text-sm text-paper/60 font-body">Available for rent</label>
            </div>
            <button type="submit" class="w-full border-2 border-rust/60 text-rust py-4 text-xs font-body font-bold uppercase tracking-[0.25em] hover:bg-rust hover:text-ink transition-all duration-300">Update Property</button>
        </form>
    </div>
    <script>
    function geocodeLocation() {
        var location = document.querySelector('input[name="location"]').value;
        if (!location) { alert('Please enter a location first.'); return; }
        var apiKey = '<?php
            $key = $_ENV['GOOGLE_MAPS_API_KEY'] ?? getenv('GOOGLE_MAPS_API_KEY') ?: '';
            if (empty($key) || $key === 'YOUR_API_KEY_HERE') {
                $dotenvPath = str_replace('\\', '/', dirname(__DIR__, 3)) . '/.env';
                if (file_exists($dotenvPath)) {
                    foreach (file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                        $line = trim($line);
                        if (empty($line) || $line[0] === '#') continue;
                        if (strpos($line, '=') !== false) {
                            list($k, $v) = explode('=', $line, 2);
                            if (trim($k) === 'GOOGLE_MAPS_API_KEY') { $v = trim(trim($v), '"\''); if (!empty($v) && $v !== 'YOUR_API_KEY_HERE') $key = $v; break; }
                        }
                    }
                }
            }
            echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
        ?>';
        if (!apiKey || apiKey === 'YOUR_API_KEY_HERE') { alert('Google Maps API key not configured.'); return; }
        fetch('https://maps.googleapis.com/maps/api/geocode/json?address=' + encodeURIComponent(location) + '&key=' + apiKey)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'OK' && data.results.length > 0) {
                    var loc = data.results[0].geometry.location;
                    document.getElementById('field-latitude').value = loc.lat.toFixed(7);
                    document.getElementById('field-longitude').value = loc.lng.toFixed(7);
                } else {
                    alert('Could not geocode this location. Please enter coordinates manually.');
                }
            })
            .catch(function() { alert('Geocoding request failed.'); });
    }
    </script>
</body>
</html>
