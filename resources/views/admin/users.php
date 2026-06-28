<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::requireRole('admin');

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/User.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

if (isset($_POST['update_role'])) {
    $userId = $_POST['user_id'];
    $newRole = $_POST['role'];
    $userModel->updateRole($userId, $newRole);
    header("Location: users.php?updated=1");
    exit;
}

if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    if ($userId != $_SESSION['user_id']) {
        $userModel->delete($userId);
    }
    header("Location: users.php?deleted=1");
    exit;
}

$stmt = $userModel->read();
$roles = Auth::getAllRoles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users — Aura Estates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{bg:'#f8f8f6','bg-alt':'#ffffff',surface:'#ffffff',ink:'#111111','ink-secondary':'#6b6b6b',graphite:'#6b6b6b',muted:'#999998',clay:'#e5e5e3',border:'#e5e5e3','border-light':'#f0f0ee',accent:'#111111','accent-hover':'#333333',success:'#2d7d46',warning:'#b8860b',danger:'#b22222'},fontFamily:{sans:['DM Sans','-apple-system','BlinkMacSystemFont','sans-serif'],mono:['JetBrains Mono','monospace'],serif:['Cormorant Garamond','Georgia','serif']}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,700&family=DM+Sans:opsz,wght@9..40,200;9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../resources/css/neo.css">
    <style>::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: var(--neo-clay); border-radius: 2px; }</style>
</head>
<body class="bg-[var(--neo-bg)] text-ink antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="neo-sidebar w-64 flex flex-col flex-shrink-0">
            <div class="p-6 border-b border-clay/20"><div class="text-2xl font-serif font-light italic text-ink/40">AURA</div><div class="text-[8px] uppercase tracking-[0.3em] text-graphite/40 font-sans font-medium mt-2">Admin Console</div></div>
            <nav class="flex-1 py-6 px-4 space-y-1 overflow-y-auto">
                <a href="/admin/dashboard" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-chart-pie w-4 text-center text-sm"></i><span class="text-sm font-sans">Dashboard</span></a>
                <a href="/admin/maintenance" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-wrench w-4 text-center text-sm"></i><span class="text-sm font-sans">Maintenance</span></a>
                <a href="/admin/amenities" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-swimming-pool w-4 text-center text-sm"></i><span class="text-sm font-sans">Amenities</span></a>
                <a href="/admin/amenity-bookings" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-calendar-check w-4 text-center text-sm"></i><span class="text-sm font-sans">Bookings</span></a>
                <a href="/admin/inquiries" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-envelope w-4 text-center text-sm"></i><span class="text-sm font-sans">Inquiries</span></a>
                <a href="/admin/rentals" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-key w-4 text-center text-sm"></i><span class="text-sm font-sans">Rentals</span></a>
                <a href="/admin/users" class="sidebar-link active flex items-center space-x-4 py-3 px-4 text-ink transition-all"><i class="fas fa-users w-4 text-center text-sm"></i><span class="text-sm font-medium font-sans">Users</span></a>
                <a href="/admin/blog" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-pen-fancy w-4 text-center text-sm"></i><span class="text-sm font-sans">Blog</span></a>
                <hr class="border-clay/20 my-6">
                <a href="../index.html" target="_blank" class="flex items-center space-x-4 py-2 px-4 text-graphite/40 hover:text-ink transition-all text-sm font-sans"><i class="fas fa-globe w-4 text-center text-sm"></i><span>View Site</span><i class="fas fa-external-link-alt text-[9px] ml-auto"></i></a>
            </nav>
            <div class="p-4 border-t border-clay/20 bg-[var(--neo-card)]">
                <div class="flex items-center space-x-3 mb-3 px-2">
                    <div class="w-8 h-8 flex items-center justify-center border border-clay/50"><span class="text-ink text-xs font-bold font-sans"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span></div>
                    <div class="flex-1 min-w-0"><div class="text-sm text-ink/80 font-medium font-sans truncate"><?php echo $_SESSION['user_name']; ?></div><div class="text-[8px] text-graphite/40 uppercase tracking-[0.25em] font-sans font-medium">Administrator</div></div>
                </div>
                <a href="/admin/logout" class="flex items-center justify-center space-x-2 py-3 px-4 border border-clay/30 text-graphite/60 hover:text-ink hover:bg-ink/[0.03] transition-all text-[10px] font-sans font-bold uppercase tracking-[0.15em]"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </div>
        </aside>
        <main class="flex-1 overflow-auto p-8">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-4xl font-serif font-light italic text-ink mb-2">Users</h1>
                <p class="text-xs text-graphite/50 mb-8 font-sans">Manage user roles and permissions</p>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="mb-6 p-4 bg-ink/10"><span class="text-ink/70 text-sm font-sans">Role updated successfully.</span></div>
                <?php endif; ?>
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="mb-6 p-4 bg-graphite/15"><span class="text-graphite text-sm font-sans">User deleted.</span></div>
                <?php endif; ?>

                <div class="neo-table overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr class="border-b border-clay/10">
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">ID</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Name</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Email</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Role</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Joined</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Actions</th>
                            </tr></thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="border-b border-clay/10 hover:bg-ink/[0.02] transition">
                                    <td class="p-5 text-sm text-graphite/70 tabular-nums">#<?php echo $row['id']; ?></td>
                                    <td class="p-5 text-sm">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 flex items-center justify-center border border-clay/30 bg-[var(--neo-card)]">
                                                <span class="text-graphite/70 text-xs font-bold font-sans"><?php echo strtoupper(substr($row['name'], 0, 1)); ?></span>
                                            </div>
                                            <span class="text-ink/90 font-medium font-sans"><?php echo htmlspecialchars($row['name']); ?></span>
                                            <?php if ($row['id'] == $_SESSION['user_id']): ?>
                                                <span class="text-[8px] border border-clay/50 text-graphite/70 px-2 py-0.5 uppercase tracking-[0.1em] font-sans">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="p-5 text-sm text-graphite/60 font-sans"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="p-5 text-sm">
                                        <span class="px-3 py-1 text-[9px] font-bold uppercase tracking-[0.1em] font-sans 
                                            <?php echo $row['role'] == 'admin' ? 'bg-graphite/15 text-graphite' : ($row['role'] == 'property_manager' ? 'bg-graphite/15 text-graphite' : ($row['role'] == 'maintenance_staff' ? 'bg-ink/10 text-ink/70' : 'bg-ink/5 text-graphite/60')); ?>">
                                            <?php echo $row['role_display'] ?: ucfirst(str_replace('_', ' ', $row['role'])); ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-sm text-graphite/60 font-sans tabular-nums"><?php echo date('M d, Y', strtotime($row['created_at'] ?? 'now')); ?></td>
                                    <td class="p-5 text-sm">
                                        <form method="POST" class="flex items-center space-x-2" onsubmit="return confirm('Change role for <?php echo htmlspecialchars($row['name']); ?>?')">
                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                            <select name="role" class="neo-select text-xs px-2 py-1.5 font-sans">
                                                <?php foreach ($roles as $r): ?>
                                                    <option value="<?php echo $r['name']; ?>" <?php echo $r['name'] == $row['role'] ? 'selected' : ''; ?>>
                                                        <?php echo $r['display_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_role" class="neo-btn neo-btn-sm text-graphite">Update</button>
                                        </form>
                                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" class="mt-2" onsubmit="return confirm('Delete <?php echo htmlspecialchars($row['name']); ?>? This cannot be undone.')">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="delete_user" class="neo-btn neo-btn-sm neo-btn-ghost text-graphite/50 hover:text-ink">Delete</button>
                                            </form>
                                        <?php endif; ?>
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
</body>
</html>
