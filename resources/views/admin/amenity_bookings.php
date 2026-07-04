<?php
require_once __DIR__ . '/../../../src/config/auth.php';
require_once __DIR__ . '/../../../src/core/CsrfProtection.php';
Auth::requireRole('admin');
CsrfProtection::generate();

include_once __DIR__ . '/../../../src/config/database.php';
include_once __DIR__ . '/../../../src/models/AmenityBooking.php';

$database = new Database();
$db = $database->getConnection();
$booking = new AmenityBooking($db);

if(isset($_POST['update_status'])){
    $booking->id = $_POST['booking_id'];
    $booking->status = $_POST['status'];
    $booking->updateStatus();
    header("Location: amenity_bookings.php");
    exit;
}

if(isset($_GET['delete'])){
    $booking->id = $_GET['delete'];
    $booking->delete();
    header("Location: amenity_bookings.php");
    exit;
}

$stmt = $booking->read();
$stats = $booking->getStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings — Aura Estates</title>
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
                <a href="/admin/amenity-bookings" class="sidebar-link active flex items-center space-x-4 py-3 px-4 text-ink transition-all"><i class="fas fa-calendar-check w-4 text-center text-sm"></i><span class="text-sm font-medium font-sans">Bookings</span></a>
                <a href="/admin/inquiries" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-envelope w-4 text-center text-sm"></i><span class="text-sm font-sans">Inquiries</span></a>
                <a href="/admin/rentals" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-key w-4 text-center text-sm"></i><span class="text-sm font-sans">Rentals</span></a>
                <a href="/admin/users" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-users w-4 text-center text-sm"></i><span class="text-sm font-sans">Users</span></a>
                <a href="/admin/blog" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-pen-fancy w-4 text-center text-sm"></i><span class="text-sm font-sans">Blog</span></a>
                <hr class="border-clay/20 my-6">
                <a href="../index.html" target="_blank" class="flex items-center space-x-4 py-2 px-4 text-graphite/40 hover:text-ink transition-all text-sm font-sans"><i class="fas fa-globe w-4 text-center text-sm"></i><span>View Site</span><i class="fas fa-external-link-alt text-[9px] ml-auto"></i></a>
            </nav>
            <div class="p-4 border-t border-clay/20 bg-[var(--neo-card)]">
                <div class="flex items-center space-x-3 mb-3 px-2"><div class="w-8 h-8 flex items-center justify-center border border-clay/50"><span class="text-ink text-xs font-bold font-sans"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? '', 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span></div><div class="flex-1 min-w-0"><div class="text-sm text-ink/80 font-medium font-sans truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div><div class="text-[8px] text-graphite/40 uppercase tracking-[0.25em] font-sans font-medium">Administrator</div></div></div>
                <a href="/admin/logout" class="flex items-center justify-center space-x-2 py-3 px-4 border border-clay/30 text-graphite/60 hover:text-ink hover:bg-ink/[0.03] transition-all text-[10px] font-sans font-bold uppercase tracking-[0.15em]"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </div>
        </aside>
        <main class="flex-1 overflow-auto p-8">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-4xl font-serif font-light italic text-ink mb-8">Bookings</h1>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-8">
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Upcoming</div><div class="text-2xl font-bold text-graphite mt-1 tabular-nums"><?php echo $stats['upcoming_count']; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Active Now</div><div class="text-2xl font-bold text-ink/70 mt-1 tabular-nums"><?php echo $stats['active_count']; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Completed</div><div class="text-2xl font-bold text-graphite/60 mt-1 tabular-nums"><?php echo $stats['completed_count']; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Today</div><div class="text-2xl font-bold text-ink mt-1 tabular-nums"><?php echo $stats['today_count']; ?></div></div>
                    <div class="neo-flat p-5"><div class="text-[8px] font-sans font-bold uppercase tracking-[0.2em] text-graphite/50">Total</div><div class="text-2xl font-bold text-ink/60 mt-1 tabular-nums"><?php echo $stats['total_count']; ?></div></div>
                </div>
                <div class="neo-table overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr class="border-b border-clay/10">
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Guest</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Amenity</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Property</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Date</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Time</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Status</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Actions</th>
                            </tr></thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="border-b border-clay/10 hover:bg-ink/[0.02] transition">
                                    <td class="p-5 text-sm text-ink/90 font-sans"><?php echo htmlspecialchars($row['guest_name']); ?></td>
                                    <td class="p-5 text-sm"><span class="text-ink/90 font-medium font-sans"><?php echo htmlspecialchars($row['amenity_name']); ?></span><br><span class="text-graphite/50 text-xs font-sans"><?php echo htmlspecialchars($row['amenity_location']); ?></span></td>
                                    <td class="p-5 text-sm text-graphite/60 font-sans font-light"><?php echo htmlspecialchars($row['property_title']); ?></td>
                                    <td class="p-5 text-sm text-graphite/60 font-sans tabular-nums"><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                                    <td class="p-5 text-sm"><span class="text-graphite/60 font-sans tabular-nums"><?php echo date('h:i A', strtotime($row['check_in_time'])); ?></span> &mdash; <span class="text-graphite/60 font-sans tabular-nums"><?php echo date('h:i A', strtotime($row['check_out_time'])); ?></span></td>
                                    <td class="p-5 text-sm">
                                        <span class="px-3 py-1 text-[9px] font-bold uppercase tracking-[0.1em] font-sans 
                                            <?php echo $row['status'] == 'confirmed' ? 'bg-graphite/15 text-graphite' : ($row['status'] == 'checked_in' ? 'bg-ink/10 text-ink/70' : ($row['status'] == 'checked_out' ? 'bg-ink/5 text-graphite/60' : 'bg-graphite/15 text-graphite')); ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($row['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="p-5 text-sm">
                                        <form method="POST" class="flex flex-col space-y-1">
                                            <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars($_SESSION['_csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                            <?php if($row['status'] == 'confirmed'): ?>
                                                <button type="submit" name="update_status" value="checked_in" class="neo-btn neo-btn-sm text-graphite">Check In</button>
                                                <button type="submit" name="update_status" value="cancelled" class="neo-btn neo-btn-sm text-graphite">Cancel</button>
                                            <?php elseif($row['status'] == 'checked_in'): ?>
                                                <button type="submit" name="update_status" value="checked_out" class="neo-btn neo-btn-sm text-graphite">Check Out</button>
                                            <?php endif; ?>
                                        </form>
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
