<?php
require_once __DIR__ . '/../../../src/config/auth.php';
require_once __DIR__ . '/../../../src/core/CsrfProtection.php';
Auth::requireRole('admin');
CsrfProtection::generate();

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/Property.php';

$database = new Database();
$db = $database->getConnection();
$property = new Property($db);

$message = "";
$messageClass = "";

if($_POST){
    $property->title = $_POST['title'];
    $property->description = $_POST['description'];
    $property->price = $_POST['price'];
    $property->location = $_POST['location'];
    $property->property_type = $_POST['property_type'];
    $property->bedrooms = $_POST['bedrooms'];
    $property->bathrooms = $_POST['bathrooms'];
    $property->area_sqft = $_POST['area_sqft'];
    $property->main_image = $_POST['main_image'] ?? '';
    $property->is_available = $_POST['is_available'] ?? 1;

    if($property->create()){
        header("Location: ../admin/create.php?success=1");
        exit;
    } else {
        $message = "Failed to create property.";
        $messageClass = "text-rust";
    }
}

$success = $_GET['success'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital@0;1&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { ink: '#0a0a0a', paper: '#f4efe6', rust: '#e76f51', sand: '#e9c46a', surface: '#121212' }, fontFamily: { display: ['Bodoni Moda', 'serif'], body: ['Inter', 'sans-serif'] } } }
        }
    </script>
    <style>body { font-family: 'Inter', sans-serif; background: #0a0a0a; color: #f4efe6; font-weight: 300; } h1, h2, h3 { font-family: 'Bodoni Moda', serif; } ::selection { background: #e76f51; color: #0a0a0a; }.noise-overlay { position: fixed; inset: 0; z-index: 9999; pointer-events: none; opacity: 0.015; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); background-repeat: repeat; background-size: 256px 256px; }</style>
</head>
<body class="antialiased min-h-screen">
    <div class="noise-overlay"></div>
    <div class="max-w-3xl mx-auto px-6 py-12">
        <a href="/admin/dashboard" class="text-[10px] font-body uppercase tracking-[0.25em] text-rust/60 hover:text-rust transition mb-8 inline-block">&larr; Back to Dashboard</a>
        
        <h1 class="text-5xl font-display italic text-paper mb-2">Add Property</h1>
        <p class="text-sm text-paper/30 font-body font-light mb-10">Add a new property to the collection.</p>

        <?php if($success): ?>
            <div class="bg-sand/10 border border-sand/20 text-sand/80 p-4 mb-6 text-sm font-body">Property created successfully! <a href="/admin/create" class="underline">Add another</a></div>
        <?php endif; ?>
        <?php if($message): ?>
            <div class="bg-rust/10 border border-rust/20 text-rust p-4 mb-6 text-sm font-body"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" class="space-y-6">
            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Title</label>
                    <input type="text" name="title" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition placeholder-paper/30" placeholder="Property name" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition placeholder-paper/30" placeholder="Describe the property..." required></textarea>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Price ($)</label>
                    <input type="number" name="price" step="0.01" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Location</label>
                    <input type="text" name="location" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition placeholder-paper/30" placeholder="City, State" required>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Type</label>
                    <select name="property_type" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                        <option value="Villa" class="bg-ink">Villa</option>
                        <option value="Penthouse" class="bg-ink">Penthouse</option>
                        <option value="Estate" class="bg-ink">Estate</option>
                        <option value="Loft" class="bg-ink">Loft</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Bedrooms</label>
                    <input type="number" name="bedrooms" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Bathrooms</label>
                    <input type="number" name="bathrooms" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div>
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Area (sqft)</label>
                    <input type="number" name="area_sqft" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[9px] font-body font-bold uppercase tracking-[0.2em] text-paper/30 mb-2">Image URL</label>
                    <input type="text" name="main_image" class="w-full bg-paper/[0.02] border border-paper/10 p-4 text-paper text-sm focus:outline-none focus:border-rust/50 transition placeholder-paper/30" placeholder="https://images.unsplash.com/...">
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <input type="checkbox" name="is_available" value="1" checked class="rounded bg-paper/[0.02] border-paper/10">
                <label class="text-sm text-paper/60 font-body">Available for rent</label>
            </div>
            <button type="submit" class="w-full border-2 border-rust/60 text-rust py-4 text-xs font-body font-bold uppercase tracking-[0.25em] hover:bg-rust hover:text-ink transition-all duration-300">Create Property</button>
        </form>
    </div>
</body>
</html>
