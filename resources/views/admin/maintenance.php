<?php
require_once __DIR__ . '/../../../src/config/auth.php';
require_once __DIR__ . '/../../../src/core/CsrfProtection.php';
Auth::requireRole('admin');
CsrfProtection::generate();

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/MaintenanceRequest.php';

$database = new Database();
$db = $database->getConnection();
$maintenance = new MaintenanceRequest($db);

if(isset($_POST['update_status'])){
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        die('Invalid CSRF token.');
    }
    $maintenance->id = (int)$_POST['request_id'];
    $maintenance->status = $_POST['status'];
    $maintenance->resolved_at = ($_POST['status'] == 'completed') ? date('Y-m-d H:i:s') : null;
    $maintenance->update();
    header("Location: maintenance.php");
    exit;
}

if(isset($_POST['assign_staff'])){
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        die('Invalid CSRF token.');
    }
    $maintenance->id = (int)$_POST['request_id'];
    $maintenance->assigned_to = $_POST['assigned_to'] ?: null;
    $maintenance->assign();
    header("Location: maintenance.php");
    exit;
}

if(isset($_POST['delete'])){
    if (!CsrfProtection::validate($_POST['_csrf_token'] ?? null)) {
        die('Invalid CSRF token.');
    }
    $maintenance->id = (int)$_POST['delete'];
    $maintenance->delete();
    header("Location: maintenance.php");
    exit;
}

$stmt = $maintenance->read();
$stats = $maintenance->getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — Aura Estates</title>
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
                <a href="/admin/maintenance" class="sidebar-link active flex items-center space-x-4 py-3 px-4 text-ink transition-all"><i class="fas fa-wrench w-4 text-center text-sm"></i><span class="text-sm font-medium font-sans">Maintenance</span></a>
                <a href="/admin/amenities" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-swimming-pool w-4 text-center text-sm"></i><span class="text-sm font-sans">Amenities</span></a>
                <a href="/admin/amenity-bookings" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-calendar-check w-4 text-center text-sm"></i><span class="text-sm font-sans">Bookings</span></a>
                <a href="/admin/inquiries" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-envelope w-4 text-center text-sm"></i><span class="text-sm font-sans">Inquiries</span></a>
                <a href="/admin/rentals" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-key w-4 text-center text-sm"></i><span class="text-sm font-sans">Rentals</span></a>
                <a href="/admin/users" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-users w-4 text-center text-sm"></i><span class="text-sm font-sans">Users</span></a>
                <a href="/admin/blog" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-pen-fancy w-4 text-center text-sm"></i><span class="text-sm font-sans">Blog</span></a>
                <hr class="border-clay/20 my-6">
                <a href="../index.html" target="_blank" class="flex items-center space-x-4 py-2 px-4 text-graphite/40 hover:text-ink transition-all text-sm font-sans"><i class="fas fa-globe w-4 text-center text-sm"></i><span>View Site</span><i class="fas fa-external-link-alt text-[9px] ml-auto"></i></a>
            </nav>
            <div class="p-4 border-t border-clay/20 bg-[var(--neo-card)]">
                <div class="flex items-center space-x-3 mb-3 px-2">
                    <div class="w-8 h-8 flex items-center justify-center border border-clay/50"><span class="text-ink text-xs font-bold font-sans"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? '', 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span></div>
                    <div class="flex-1 min-w-0"><div class="text-sm text-ink/80 font-medium font-sans truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div><div class="text-[8px] text-graphite/40 uppercase tracking-[0.25em] font-sans font-medium">Administrator</div></div>
                </div>
                <a href="/admin/logout" class="flex items-center justify-center space-x-2 py-3 px-4 border border-clay/30 text-graphite/60 hover:text-ink hover:bg-ink/[0.03] transition-all text-[10px] font-sans font-bold uppercase tracking-[0.15em]"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </div>
        </aside>
        <main class="flex-1 overflow-auto p-8">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-4xl font-serif font-light italic text-ink mb-8">Maintenance</h1>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3 mb-8">
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Pending</div><div class="text-2xl font-bold text-ink mt-1 tabular-nums"><?php echo $stats['pending_count']; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">In Progress</div><div class="text-2xl font-bold text-graphite mt-1 tabular-nums"><?php echo $stats['in_progress_count']; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Completed</div><div class="text-2xl font-bold text-ink/70 mt-1 tabular-nums"><?php echo $stats['completed_count']; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Avg Resolution</div><div class="text-2xl font-bold text-ink/60 mt-1 tabular-nums"><?php echo $stats['avg_resolution_hours'] ? $stats['avg_resolution_hours'] . 'h' : 'N/A'; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Completion Rate</div><div class="text-2xl font-bold <?php echo ($stats['completion_rate'] ?? 0) >= 90 ? 'text-success' : 'text-warning'; ?> mt-1 tabular-nums"><?php echo ($stats['completion_rate'] ?? 'N/A') . '%'; ?></div></div>
                    <div class="neo-flat p-5 <?php echo ($stats['overdue_count'] ?? 0) > 0 ? 'border border-danger/20' : ''; ?>"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Overdue (>48h)</div><div class="text-2xl font-bold <?php echo ($stats['overdue_count'] ?? 0) > 0 ? 'text-danger' : 'text-ink/60'; ?> mt-1 tabular-nums"><?php echo $stats['overdue_count'] ?? 0; ?></div></div>
                </div>
                <div class="neo-table overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>                            <tr class="border-b border-clay/10">
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">ID</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Property</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Tenant</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Issue</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Priority</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Status</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Assigned To</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Date</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Actions</th>
                            </tr></thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="border-b border-clay/10 hover:bg-ink/[0.02] transition">
                                    <td class="p-5 text-sm text-graphite/70 tabular-nums">#<?php echo $row['id']; ?></td>
                                    <td class="p-5 text-sm"><span class="text-ink/90 font-medium font-sans"><?php echo htmlspecialchars($row['property_title']); ?></span><br><span class="text-graphite/50 text-xs font-sans"><?php echo htmlspecialchars($row['property_location']); ?></span></td>
                                    <td class="p-5 text-sm"><span class="text-ink/90 font-sans"><?php echo htmlspecialchars($row['tenant_name']); ?></span><br><span class="text-graphite/50 text-xs font-sans"><?php echo htmlspecialchars($row['tenant_email']); ?></span></td>
                                    <td class="p-5 text-sm max-w-xs"><span class="text-graphite/60 truncate block font-sans font-light"><?php echo htmlspecialchars($row['issue_description']); ?></span></td>
                                    <td class="p-5 text-sm">
                                        <span class="px-3 py-1 text-[9px] font-bold uppercase tracking-[0.1em] font-sans 
                                            <?php echo $row['priority'] == 'urgent' ? 'text-ink border border-clay/50 bg-ink/[0.04]' : ($row['priority'] == 'high' ? 'text-ink border border-clay/50' : ($row['priority'] == 'medium' ? 'text-ink/70 border border-clay/30' : 'text-graphite/50 border border-clay/30')); ?>">
                                            <?php echo ucfirst($row['priority']); ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-sm">
                                        <span class="px-3 py-1 text-[9px] font-bold uppercase tracking-[0.1em] font-sans 
                                            <?php echo $row['status'] == 'pending' ? 'bg-graphite/15 text-graphite' : ($row['status'] == 'in_progress' ? 'bg-graphite/15 text-graphite' : 'bg-ink/10 text-ink/70'); ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($row['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-sm"><span class="text-graphite/60 font-sans"><?php echo $row['assigned_name'] ?: '<span class="text-graphite/40 italic text-xs">Unassigned</span>'; ?></span></td>
                                    <td class="p-5 text-sm"><span class="text-graphite/60 font-sans tabular-nums"><?php echo date('M d', strtotime($row['created_at'])); ?></span><br><span class="text-graphite/50 text-xs font-sans tabular-nums"><?php echo date('h:i A', strtotime($row['created_at'])); ?></span></td>
                                    <td class="p-5 text-sm">
                                        <div class="flex flex-col space-y-1">
                                            <form method="POST" class="flex space-x-1">
                                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                                <?php if($row['status'] == 'pending'): ?>
                                                    <button type="submit" name="update_status" value="in_progress" class="neo-btn neo-btn-sm text-graphite">Start</button>
                                                <?php endif; ?>
                                                <?php if($row['status'] == 'in_progress'): ?>
                                                    <button type="submit" name="update_status" value="completed" class="neo-btn neo-btn-sm text-ink/70">Complete</button>
                                                <?php endif; ?>
                                                <?php if($row['status'] == 'pending'): ?>
                                                    <button type="submit" name="update_status" value="completed" class="neo-btn neo-btn-sm text-graphite/60">Skip</button>
                                                <?php endif; ?>
                                            </form>
                                            <form method="POST" class="flex items-center space-x-1">
                                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="assign_staff" value="1">
                                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                                <select name="assigned_to" class="text-[9px] border border-clay/30 px-2 py-1 bg-transparent font-sans text-graphite/70 outline-none w-24" onchange="this.form.submit()">
                                                    <option value="">Assign...</option>
                                                    <?php
                                                    $staffStmt = $maintenance->getStaffList();
                                                    while ($staff = $staffStmt->fetch(PDO::FETCH_ASSOC)) {
                                                        $selected = ($staff['id'] == $row['assigned_to']) ? 'selected' : '';
                                                        echo '<option value="' . $staff['id'] . '" ' . $selected . '>' . htmlspecialchars($staff['name']) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('Delete this request?')">
                                                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="delete" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="neo-btn neo-btn-sm neo-btn-ghost text-graphite/50 hover:text-ink" style="background:none;border:none;cursor:pointer">Delete</button>
                                            </form>
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
</body>
</html>
