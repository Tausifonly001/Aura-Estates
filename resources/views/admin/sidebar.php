<?php
$current_page = basename($_SERVER['PHP_SELF']);
function getSidebarClass($pageName, $currentPage, $extra = '') {
    $isActive = ($currentPage === $pageName);
    $base = 'sidebar-link flex items-center space-x-3.5 py-2.5 px-4 transition-all no-underline border-l-2';
    if ($isActive) {
        return $base . ' border-accent text-ink font-medium bg-bg-alt/60 ' . $extra;
    } else {
        return $base . ' border-transparent text-ink-secondary hover:text-ink hover:bg-bg-alt/30 ' . $extra;
    }
}
?>
<aside class="neo-sidebar w-64 flex flex-col flex-shrink-0 bg-surface border-r border-border-light">
    <!-- Brand Logo Header -->
    <div class="p-6 border-b border-border-light bg-surface/50 backdrop-blur-sm">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-7 h-7 bg-accent text-bg text-[0.75rem] font-semibold tracking-wider font-sans leading-none">A</span>
            <span class="font-serif italic font-light text-[1.4rem] tracking-wide text-ink leading-none">Aura</span>
            <span class="font-sans text-[0.55rem] font-medium uppercase tracking-[0.2em] text-ink-secondary/60 mt-1">Estates</span>
        </div>
        <div class="text-[0.5rem] uppercase tracking-[0.2em] text-ink-secondary/50 font-sans font-bold mt-2">Admin Workspace</div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto">
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/dashboard" class="<?php echo getSidebarClass('dashboard.php', $current_page); ?>">
            <i class="fas fa-chart-pie w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Dashboard</span>
        </a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/maintenance" class="<?php echo getSidebarClass('maintenance.php', $current_page); ?>">
            <i class="fas fa-wrench w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Maintenance</span>
        </a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/amenities" class="<?php echo getSidebarClass('amenities.php', $current_page); ?>">
            <i class="fas fa-swimming-pool w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Amenities</span>
        </a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/amenity-bookings" class="<?php echo getSidebarClass('amenity_bookings.php', $current_page); ?>">
            <i class="fas fa-calendar-check w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Bookings</span>
        </a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/inquiries" class="<?php echo getSidebarClass('inquiries.php', $current_page); ?>">
            <i class="fas fa-envelope w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Inquiries</span>
        </a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/rentals" class="<?php echo getSidebarClass('rentals.php', $current_page); ?>">
            <i class="fas fa-key w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Rentals</span>
        </a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/users" class="<?php echo getSidebarClass('users.php', $current_page); ?>">
            <i class="fas fa-users w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Users</span>
        </a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/blog" class="<?php echo getSidebarClass('blog.php', $current_page); ?>">
            <i class="fas fa-pen-fancy w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Blog</span>
        </a>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/content" class="<?php echo getSidebarClass('content.php', $current_page); ?>">
            <i class="fas fa-edit w-4 text-center text-xs opacity-75"></i>
            <span class="text-[0.8125rem] font-sans">Content</span>
        </a>
        <hr class="border-border-light my-6 mx-2">
        <a href="<?php echo Auth::getBasePrefix(); ?>/index.html" target="_blank" class="flex items-center space-x-3.5 py-2.5 px-4 text-ink-secondary/60 hover:text-ink transition-all text-[0.8125rem] font-sans no-underline border-l-2 border-transparent">
            <i class="fas fa-globe w-4 text-center text-xs opacity-70"></i>
            <span>View Site</span>
            <i class="fas fa-external-link-alt text-[8px] ml-auto opacity-50"></i>
        </a>
    </nav>

    <!-- User Profile Footer -->
    <div class="p-4 border-t border-border-light bg-surface/50">
        <div class="flex items-center space-x-3 mb-3 px-2">
            <div class="w-8 h-8 rounded-full bg-accent text-bg flex items-center justify-center font-medium font-sans text-xs">
                <?php echo htmlspecialchars(strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[0.8125rem] text-ink font-medium font-sans truncate">
                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="text-[0.5625rem] text-ink-secondary/60 uppercase tracking-[0.1em] font-mono mt-0.5">
                    Administrator
                </div>
            </div>
        </div>
        <a href="<?php echo Auth::getBasePrefix(); ?>/admin/logout" class="flex items-center justify-center space-x-2 py-2 border border-border hover:border-ink rounded-full text-ink-secondary hover:text-ink transition-colors text-[0.625rem] font-mono uppercase tracking-[0.1em] no-underline">
            <i class="fas fa-sign-out-alt text-[10px]"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>