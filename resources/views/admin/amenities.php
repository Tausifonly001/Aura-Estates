<?php
require_once __DIR__ . '/../../../src/config/auth.php';
require_once __DIR__ . '/../../../src/core/CsrfProtection.php';
Auth::requireStaff();
CsrfProtection::generate();

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/Amenity.php';

$database = new Database();
$db = $database->getConnection();
$amenity = new Amenity($db);

if(isset($_POST['create'])){
    $amenity->property_id = $_POST['property_id'];
    $amenity->name = $_POST['name'];
    $amenity->description = $_POST['description'] ?? '';
    $amenity->capacity = $_POST['capacity'] ?? 1;
    $amenity->location = $_POST['location'] ?? '';
    $amenity->image = $_POST['image'] ?? '';
    $amenity->create();
    header("Location: amenities.php");
    exit;
}

if(isset($_POST['update'])){
    $amenity->id = $_POST['id'];
    $amenity->property_id = $_POST['property_id'];
    $amenity->name = $_POST['name'];
    $amenity->description = $_POST['description'] ?? '';
    $amenity->capacity = $_POST['capacity'] ?? 1;
    $amenity->location = $_POST['location'] ?? '';
    $amenity->image = $_POST['image'] ?? '';
    $amenity->is_active = $_POST['is_active'] ?? 0;
    $amenity->update();
    header("Location: amenities.php");
    exit;
}

if(isset($_GET['delete'])){
    $amenity->id = $_GET['delete'];
    $amenity->delete();
    header("Location: amenities.php");
    exit;
}

$stmt = $amenity->read();
$stats = $amenity->getStats();

$propQuery = "SELECT id, title FROM properties ORDER BY title";
$propStmt = $db->prepare($propQuery);
$propStmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amenities — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{bg:'#f8f8f6','bg-alt':'#ffffff',surface:'#ffffff',ink:'#111111','ink-secondary':'#6b6b6b',graphite:'#6b6b6b',muted:'#999998',clay:'#e5e5e3',border:'#e5e5e3','border-light':'#f0f0ee',accent:'#111111','accent-hover':'#333333',success:'#2d7d46',warning:'#b8860b',danger:'#b22222'},fontFamily:{sans:['DM Sans','-apple-system','BlinkMacSystemFont','sans-serif'],mono:['JetBrains Mono','monospace'],serif:['Cormorant Garamond','Georgia','serif']}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,700&family=DM+Sans:opsz,wght@9..40,200;9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../resources/css/neo.css">
    <style>::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: var(--neo-clay); border-radius: 2px; }</style>
</head>
<body class="bg-[var(--neo-bg)] text-ink antialiased">
    <div class="flex h-screen overflow-hidden">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="flex-1 overflow-auto p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-4xl font-serif font-light italic text-ink">Amenities</h1>
                    <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="neo-btn px-6 py-3 text-xs font-sans font-medium uppercase tracking-[0.2em] text-ink">Add Amenity</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-8">
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Active</div><div class="text-2xl font-bold text-ink/70 mt-1 tabular-nums"><?php echo $stats['active_count']; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Total</div><div class="text-2xl font-bold text-ink/60 mt-1 tabular-nums"><?php echo $stats['total_count']; ?></div></div>
                </div>
                <div class="neo-table overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr class="border-b border-clay/10">
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Name</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Property</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Location</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Capacity</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Status</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Actions</th>
                            </tr></thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="border-b border-clay/10 hover:bg-ink/[0.02] transition">
                                    <td class="p-5 text-sm"><span class="text-ink/90 font-medium font-sans"><?php echo htmlspecialchars($row['name']); ?></span><?php if($row['description']): ?><br><span class="text-graphite/50 text-xs font-sans"><?php echo htmlspecialchars(substr($row['description'], 0, 60)); ?></span><?php endif; ?></td>
                                    <td class="p-5 text-sm text-graphite/60 font-sans font-light"><?php echo htmlspecialchars($row['property_title']); ?></td>
                                    <td class="p-5 text-sm text-graphite/60 font-sans font-light"><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td class="p-5 text-sm text-graphite/60 font-sans tabular-nums"><?php echo $row['capacity']; ?></td>
                                    <td class="p-5 text-sm"><span class="px-3 py-1 text-[9px] font-bold uppercase tracking-[0.1em] font-sans <?php echo $row['is_active'] ? 'bg-ink/10 text-ink/70' : 'bg-graphite/15 text-graphite'; ?>"><?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                    <td class="p-5 text-sm">
                                        <div class="flex space-x-4">
                                            <a href="#" onclick='openEdit(<?php echo json_encode($row); ?>); return false;' class="neo-btn neo-btn-sm text-graphite">Edit</a>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="neo-btn neo-btn-sm neo-btn-ghost text-graphite/50 hover:text-ink" onclick="return confirm('Delete this amenity?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="createModal" class="hidden fixed inset-0 bg-[var(--neo-card)]/95 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="neo-card p-8 w-full max-w-lg mx-4 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-serif font-light italic text-ink">Add Amenity</h2>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-graphite/50 hover:text-ink transition text-2xl font-sans font-light">&times;</button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Property</label>
                    <select name="property_id" class="w-full neo-input p-3 text-ink text-sm font-sans" required>
                        <?php $propStmt->execute(); while($p = $propStmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['title']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Name</label><input type="text" name="name" class="w-full neo-input p-3 text-ink text-sm font-sans" required></div>
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Description</label><textarea name="description" rows="2" class="w-full neo-input p-3 text-ink text-sm font-sans"></textarea></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Capacity</label><input type="number" name="capacity" value="1" min="1" class="w-full neo-input p-3 text-ink text-sm font-sans"></div>
                    <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Location</label><input type="text" name="location" class="w-full neo-input p-3 text-ink text-sm font-sans"></div>
                </div>
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Image URL</label><input type="text" name="image" class="w-full neo-input p-3 text-ink text-sm font-sans"></div>
                <button type="submit" name="create" class="w-full neo-btn py-3 text-xs font-sans font-medium uppercase tracking-[0.2em] text-ink">Create</button>
            </form>
        </div>
    </div>

    <div id="editModal" class="hidden fixed inset-0 bg-[var(--neo-card)]/95 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="neo-card p-8 w-full max-w-lg mx-4 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-serif font-light italic text-ink">Edit Amenity</h2>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-graphite/50 hover:text-ink transition text-2xl font-sans font-light">&times;</button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id" id="edit_id">
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Property</label>
                    <select name="property_id" id="edit_property_id" class="w-full neo-input p-3 text-ink text-sm font-sans" required>
                        <?php $propStmt->execute(); while($p = $propStmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['title']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Name</label><input type="text" name="name" id="edit_name" class="w-full neo-input p-3 text-ink text-sm font-sans" required></div>
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Description</label><textarea name="description" id="edit_description" rows="2" class="w-full neo-input p-3 text-ink text-sm font-sans"></textarea></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Capacity</label><input type="number" name="capacity" id="edit_capacity" min="1" class="w-full neo-input p-3 text-ink text-sm font-sans"></div>
                    <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Location</label><input type="text" name="location" id="edit_location" class="w-full neo-input p-3 text-ink text-sm font-sans"></div>
                </div>
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Image URL</label><input type="text" name="image" id="edit_image" class="w-full neo-input p-3 text-ink text-sm font-sans"></div>
                <div class="flex items-center space-x-3"><input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded neo-input"><label for="edit_is_active" class="text-sm text-graphite/60 font-sans">Active</label></div>
                <button type="submit" name="update" class="w-full neo-btn py-3 text-xs font-sans font-medium uppercase tracking-[0.2em] text-ink">Update</button>
            </form>
        </div>
    </div>

    <script>
        function openEdit(amenity) {
            document.getElementById('edit_id').value = amenity.id;
            document.getElementById('edit_name').value = amenity.name;
            document.getElementById('edit_description').value = amenity.description || '';
            document.getElementById('edit_capacity').value = amenity.capacity;
            document.getElementById('edit_location').value = amenity.location || '';
            document.getElementById('edit_image').value = amenity.image || '';
            document.getElementById('edit_is_active').checked = amenity.is_active == 1;
            document.getElementById('edit_property_id').value = amenity.property_id;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</body>
</html>
