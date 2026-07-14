<?php
$current_page = basename($_SERVER['PHP_SELF']);
function getSidebarClass($pageName, $currentPage, $extra = '') {
    $isActive = ($currentPage === $pageName);
    $base = 'sidebar-link flex items-center space-x-4 py-3 px-4 transition-all no-underline';
    if ($isActive) {
        return $base . ' active text-ink font-medium ' . $extra;
    } else {
        return $base . ' text-graphite/50 hover:text-ink hover:bg-ink/[0.03] ' . $extra;
    }
}
?>
<aside class="neo-sidebar w-64 flex flex-col flex-shrink-0">
    <div class="p-6 border-b border-clay/20"><div class="text-2xl font-serif font-light italic text-ink/40">AURA</div><div class="text-[8px] uppercase tracking-[0.3em] text-graphite/40 font-sans font-medium mt-2">Admin Console</div></div>
    <nav class="flex-1 py-6 px-4 space-y-1 overflow-y-auto">
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/dashboard" class="<?php echo getSidebarClass('dashboard.php', $current_page); ?>"><i class="fas fa-chart-pie w-4 text-center text-sm"></i><span class="text-sm font-sans">Dashboard</span></a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/maintenance" class="<?php echo getSidebarClass('maintenance.php', $current_page); ?>"><i class="fas fa-wrench w-4 text-center text-sm"></i><span class="text-sm font-sans">Maintenance</span></a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/amenities" class="<?php echo getSidebarClass('amenities.php', $current_page); ?>"><i class="fas fa-swimming-pool w-4 text-center text-sm"></i><span class="text-sm font-sans">Amenities</span></a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/amenity-bookings" class="<?php echo getSidebarClass('amenity_bookings.php', $current_page); ?>"><i class="fas fa-calendar-check w-4 text-center text-sm"></i><span class="text-sm font-sans">Bookings</span></a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/inquiries" class="<?php echo getSidebarClass('inquiries.php', $current_page); ?>"><i class="fas fa-envelope w-4 text-center text-sm"></i><span class="text-sm font-sans">Inquiries</span></a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/rentals" class="<?php echo getSidebarClass('rentals.php', $current_page); ?>"><i class="fas fa-key w-4 text-center text-sm"></i><span class="text-sm font-sans">Rentals</span></a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/users" class="<?php echo getSidebarClass('users.php', $current_page); ?>"><i class="fas fa-users w-4 text-center text-sm"></i><span class="text-sm font-sans">Users</span></a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/blog" class="<?php echo getSidebarClass('blog.php', $current_page); ?>"><i class="fas fa-pen-fancy w-4 text-center text-sm"></i><span class="text-sm font-sans">Blog</span></a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/content" class="<?php echo getSidebarClass('content.php', $current_page); ?>"><i class="fas fa-edit w-4 text-center text-sm"></i><span class="text-sm font-sans">Content</span></a>
        <hr class="border-clay/20 my-6">
        <a href="<?php echo Auth::getBasePrefix(); ?>/index.html" target="_blank" class="flex items-center space-x-4 py-2 px-4 text-graphite/40 hover:text-ink transition-all text-sm font-sans no-underline"><i class="fas fa-globe w-4 text-center text-sm"></i><span>View Site</span><i class="fas fa-external-link-alt text-[9px] ml-auto"></i></a>
    </nav>
    <div class="p-4 border-t border-clay/20 bg-[var(--neo-card)]" style="background:var(--neo-card)">
        <div class="flex items-center space-x-3 mb-3 px-2">
            <div class="w-8 h-8 flex items-center justify-center border border-clay/50"><span class="text-ink text-xs font-bold font-sans"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? '', 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span></div>
            <div class="flex-1 min-w-0"><div class="text-sm text-ink/80 font-medium font-sans truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div><div class="text-[8px] text-graphite/40 uppercase tracking-[0.25em] font-sans font-medium">Administrator</div></div>
        </div>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/logout" class="flex items-center justify-center space-x-2 py-3 px-4 border border-clay/30 text-graphite/60 hover:text-ink hover:bg-ink/[0.03] transition-all text-[10px] font-sans font-bold uppercase tracking-[0.15em] no-underline"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
</aside>