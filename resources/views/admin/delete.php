<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::requireRole('admin');

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/Property.php';

$database = new Database();
$db = $database->getConnection();
$property = new Property($db);

$id = $_GET['id'] ?? 0;
$property->id = $id;

if($property->readOne()){
    if($_POST && isset($_POST['confirm'])){
        if($property->delete()){
            header("Location: dashboard.php?deleted=1");
            exit;
        }
    }
} else {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Property — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital@0;1&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { ink: '#0a0a0a', paper: '#f4efe6', rust: '#e76f51', surface: '#121212' }, fontFamily: { display: ['Bodoni Moda', 'serif'], body: ['Inter', 'sans-serif'] } } }
        }
    </script>
    <style>body { font-family: 'Inter', sans-serif; background: #0a0a0a; color: #f4efe6; font-weight: 300; } h1 { font-family: 'Bodoni Moda', serif; } ::selection { background: #e76f51; }.noise-overlay { position: fixed; inset: 0; z-index: 9999; pointer-events: none; opacity: 0.015; background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); background-repeat: repeat; background-size: 256px 256px; }</style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center">
    <div class="noise-overlay"></div>
    <div class="max-w-lg mx-auto px-6 text-center">
        <div class="text-6xl font-display italic text-paper/10 mb-8">AURA</div>
        <h1 class="text-4xl font-display italic text-paper mb-4">Delete Property</h1>
        <p class="text-paper/40 text-sm font-body font-light mb-8">Are you sure you want to delete <strong class="text-paper/80"><?php echo htmlspecialchars($property->title); ?></strong>? This action cannot be undone.</p>
        <form method="post" class="flex justify-center space-x-4">
            <input type="hidden" name="confirm" value="1">
            <button type="submit" class="border-2 border-rust/60 text-rust px-8 py-3 text-xs font-body font-bold uppercase tracking-[0.2em] hover:bg-rust hover:text-ink transition-all duration-300">Delete</button>
            <a href="/admin/dashboard" class="border-2 border-paper/20 text-paper/40 px-8 py-3 text-xs font-body font-bold uppercase tracking-[0.2em] hover:border-paper/40 hover:text-paper/60 transition-all duration-300">Cancel</a>
        </form>
    </div>
</body>
</html>
