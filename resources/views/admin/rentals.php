<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::requireRole('admin');

include_once __DIR__ . '/../../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

$userStmt = $db->prepare("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name");
$userStmt->execute();
$users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

$propStmt = $db->prepare("SELECT id, title, price FROM properties WHERE is_available = 1 ORDER BY title");
$propStmt->execute();
$properties = $propStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rentals — Aura Estates</title>
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
                <a href="dashboard.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-chart-pie w-4 text-center text-sm"></i><span class="text-sm font-sans">Dashboard</span></a>
                <a href="maintenance.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-wrench w-4 text-center text-sm"></i><span class="text-sm font-sans">Maintenance</span></a>
                <a href="amenities.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-swimming-pool w-4 text-center text-sm"></i><span class="text-sm font-sans">Amenities</span></a>
                <a href="amenity_bookings.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-calendar-check w-4 text-center text-sm"></i><span class="text-sm font-sans">Bookings</span></a>
                <a href="inquiries.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-envelope w-4 text-center text-sm"></i><span class="text-sm font-sans">Inquiries</span></a>
                <a href="rentals.php" class="sidebar-link active flex items-center space-x-4 py-3 px-4 text-ink transition-all"><i class="fas fa-key w-4 text-center text-sm"></i><span class="text-sm font-medium font-sans">Rentals</span></a>
                <a href="users.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-users w-4 text-center text-sm"></i><span class="text-sm font-sans">Users</span></a>
                <a href="blog.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-pen-fancy w-4 text-center text-sm"></i><span class="text-sm font-sans">Blog</span></a>
                <hr class="border-clay/20 my-6">
                <a href="../index.html" target="_blank" class="flex items-center space-x-4 py-2 px-4 text-graphite/40 hover:text-ink transition-all text-sm font-sans"><i class="fas fa-globe w-4 text-center text-sm"></i><span>View Site</span><i class="fas fa-external-link-alt text-[9px] ml-auto"></i></a>
            </nav>
            <div class="p-4 border-t border-clay/20 bg-[var(--neo-card)]">
                <div class="flex items-center space-x-3 mb-3 px-2"><div class="w-8 h-8 flex items-center justify-center border border-clay/50"><span class="text-ink text-xs font-bold font-sans"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span></div><div class="flex-1 min-w-0"><div class="text-sm text-ink/80 font-medium font-sans truncate"><?php echo $_SESSION['user_name']; ?></div><div class="text-[8px] text-graphite/40 uppercase tracking-[0.25em] font-sans font-medium">Administrator</div></div></div>
                <a href="logout.php" class="flex items-center justify-center space-x-2 py-3 px-4 border border-clay/30 text-graphite/60 hover:text-ink hover:bg-ink/[0.03] transition-all text-[10px] font-sans font-bold uppercase tracking-[0.15em]"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </div>
        </aside>
        <main class="flex-1 overflow-auto p-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-4xl font-serif font-light italic text-ink">Rentals</h1>
                    <button onclick="document.getElementById('assignModal').classList.remove('hidden')" class="neo-btn px-6 py-3 text-xs font-sans font-medium uppercase tracking-[0.2em] text-ink">Assign Tenant</button>
                </div>
                <div id="message" class="mb-4 text-sm text-ink/70 font-sans"></div>
                <div class="neo-table overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead><tr class="border-b border-clay/10">
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Property</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Tenant</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Email</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Start</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">End</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Rent</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Status</th>
                                <th class="text-left p-5 text-[9px] font-bold uppercase tracking-[0.2em] text-graphite/50 font-sans">Actions</th>
                            </tr></thead>
                            <tbody id="rentalsBody" class="divide-y divide-clay/10"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="assignModal" class="hidden fixed inset-0 bg-[var(--neo-card)]/95 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="neo-card p-8 w-full max-w-lg mx-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-serif font-light italic text-ink">Assign Tenant</h2>
                <button onclick="document.getElementById('assignModal').classList.add('hidden')" class="text-graphite/50 hover:text-ink transition text-2xl font-sans font-light">&times;</button>
            </div>
            <form id="assignForm" onsubmit="submitAssign(event)" class="space-y-4">
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Tenant</label>
                    <select name="user_id" id="form_user_id" class="w-full neo-input p-3 text-ink text-sm font-sans" required>
                        <option value="">Select a tenant</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name'] . ' (' . $u['email'] . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Property</label>
                    <select name="property_id" id="form_property_id" class="w-full neo-input p-3 text-ink text-sm font-sans" required onchange="updateRent(this)">
                        <option value="" data-price="">Select a property</option>
                        <?php foreach($properties as $p): ?>
                            <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>"><?php echo htmlspecialchars($p['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Start Date</label><input type="date" name="start_date" id="form_start_date" class="w-full neo-input p-3 text-ink text-sm font-sans" required></div>
                    <div><label class="block text-[9px] font-sans font-medium uppercase tracking-[0.2em] text-graphite/50 mb-2">Monthly Rent</label><input type="number" name="monthly_rent" id="form_monthly_rent" class="w-full neo-input p-3 text-ink text-sm font-sans" required></div>
                </div>
                <button type="submit" id="submitBtn" class="w-full neo-btn py-3 text-xs font-sans font-medium uppercase tracking-[0.2em] text-ink">Assign Rental</button>
            </form>
        </div>
    </div>

    <script>
        function loadRentals() {
            fetch('../api/rentals.php').then(r => r.json()).then(data => {
                const tbody = document.getElementById('rentalsBody');
                const records = data.records || [];
                if(records.length === 0) { tbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-graphite/50 font-sans font-light">No rentals found.</td></tr>'; return; }
                const badges = { active: 'bg-ink/10 text-ink/70', expired: 'bg-ink/5 text-graphite/60', terminated: 'bg-graphite/15 text-graphite' };
                tbody.innerHTML = records.map(r => `<tr class="border-b border-clay/10 hover:bg-ink/[0.02] transition">
                    <td class="p-5 text-sm text-ink/90 font-medium font-sans">${r.property_title || 'N/A'}</td>
                    <td class="p-5 text-sm text-graphite/60 font-sans font-light">${r.user_name || 'N/A'}</td>
                    <td class="p-5 text-sm text-graphite/70 font-sans">${r.user_email || 'N/A'}</td>
                    <td class="p-5 text-sm text-graphite/60 font-sans tabular-nums">${r.start_date}</td>
                    <td class="p-5 text-sm text-graphite/60 font-sans tabular-nums">${r.end_date || '-'}</td>
                    <td class="p-5 text-sm text-ink font-sans tabular-nums">$${parseInt(r.monthly_rent).toLocaleString()}</td>
                    <td class="p-5 text-sm"><span class="px-3 py-1 text-[9px] font-bold uppercase tracking-[0.1em] font-sans ${badges[r.status] || ''}">${r.status}</span></td>
                    <td class="p-5 text-sm">${r.status === 'active' ? `<button onclick="terminateRental(${r.id})" class="neo-btn neo-btn-sm text-graphite"><i class="fas fa-ban mr-1"></i>Terminate</button>` : '-'}</td>
                </tr>`).join('');
            });
        }
        function terminateRental(id) {
            if(!confirm('Terminate this rental?')) return;
            fetch('../api/rentals.php?terminate=1&id=' + id, { method: 'PUT' }).then(r => r.json()).then(data => {
                document.getElementById('message').innerHTML = data.message;
                loadRentals();
            });
        }
        function updateRent(select) {
            var option = select.options[select.selectedIndex];
            document.getElementById('form_monthly_rent').value = option.getAttribute('data-price') || '';
        }
        function submitAssign(e) {
            e.preventDefault();
            var btn = document.getElementById('submitBtn'); btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Assigning...';
            var data = {
                user_id: document.getElementById('form_user_id').value,
                property_id: document.getElementById('form_property_id').value,
                start_date: document.getElementById('form_start_date').value,
                monthly_rent: document.getElementById('form_monthly_rent').value
            };
            fetch('../api/rentals.php', { method: 'POST', body: JSON.stringify(data), headers: { 'Content-Type': 'application/json' } })
                .then(r => r.json()).then(res => {
                    document.getElementById('assignModal').classList.add('hidden');
                    document.getElementById('message').innerHTML = '<span class="text-ink/70">' + res.message + '</span>';
                    loadRentals();
                    btn.disabled = false; btn.innerHTML = 'Assign Rental';
                    document.getElementById('assignForm').reset();
                }).catch(() => { alert('Failed to assign.'); btn.disabled = false; btn.innerHTML = 'Assign Rental'; });
        }
        loadRentals();
    </script>
</body>
</html>
