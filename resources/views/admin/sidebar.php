<aside class="neo-sidebar w-64 flex flex-col flex-shrink-0">
    <div class="p-6 border-b border-clay/20"><div class="text-2xl font-serif font-light italic text-ink/40">AURA</div><div class="text-[8px] uppercase tracking-[0.3em] text-graphite/40 font-sans font-medium mt-2">Admin Console</div></div>
    <nav class="flex-1 py-6 px-4 space-y-1 overflow-y-auto">
        <a href="dashboard.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-chart-pie w-4 text-center text-sm"></i><span class="text-sm font-sans">Dashboard</span></a>
        <a href="maintenance.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-wrench w-4 text-center text-sm"></i><span class="text-sm font-sans">Maintenance</span></a>
        <a href="amenities.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-swimmer w-4 text-center text-sm"></i><span class="text-sm font-sans">Amenities</span></a>
        <a href="amenity_bookings.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-calendar-check w-4 text-center text-sm"></i><span class="text-sm font-sans">Bookings</span></a>
        <a href="inquiries.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-envelope w-4 text-center text-sm"></i><span class="text-sm font-sans">Inquiries</span></a>
        <a href="rentals.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-key w-4 text-center text-sm"></i><span class="text-sm font-sans">Rentals</span></a>
        <a href="users.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-users w-4 text-center text-sm"></i><span class="text-sm font-sans">Users</span></a>
        <a href="blog.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-pen-fancy w-4 text-center text-sm"></i><span class="text-sm font-sans">Blog</span></a>
        <a href="content.php" class="sidebar-link flex items-center space-x-4 py-3 px-4 text-graphite/50 hover:text-ink hover:bg-ink/[0.03] transition-all"><i class="fas fa-edit w-4 text-center text-sm"></i><span class="text-sm font-sans">Content</span></a>
        <hr class="border-clay/20 my-6">
        <a href="../index.html" target="_blank" class="flex items-center space-x-4 py-2 px-4 text-graphite/40 hover:text-ink transition-all text-sm font-sans"><i class="fas fa-globe w-4 text-center text-sm"></i><span>View Site</span><i class="fas fa-external-link-alt text-[9px] ml-auto"></i></a>
    </nav>
    <div class="p-4 border-t border-clay/20" style="background:var(--neo-card)">
        <div class="flex items-center space-x-3 mb-3 px-2">
            <div class="w-8 h-8 flex items-center justify-center border border-clay/50"><span class="text-ink text-xs font-bold font-sans"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span></div>
            <div class="flex-1 min-w-0"><div class="text-sm text-ink/80 font-medium font-sans truncate"><?php echo $_SESSION['user_name']; ?></div><div class="text-[8px] text-graphite/40 uppercase tracking-[0.25em] font-sans font-medium">Administrator</div></div>
        </div>
        <a href="logout.php" class="flex items-center justify-center space-x-2 py-3 px-4 border border-clay/30 text-graphite/60 hover:text-ink hover:bg-ink/[0.03] transition-all text-[10px] font-sans font-bold uppercase tracking-[0.15em]"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
</aside>