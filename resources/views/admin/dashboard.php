<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::requireRole('admin');
Auth::startSession();
?>
<!DOCTYPE html>
<html lang="en" ng-app="adminApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Aura Estates</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg: '#e8e5db',
                        'bg-alt': '#f2efe9',
                        surface: '#faf8f4',
                        ink: '#1c1b18',
                        'ink-secondary': '#5c5349',
                        muted: '#9a9086',
                        border: '#d6d2c8',
                        'border-light': '#e1ddd4',
                        accent: '#3a322c',
                        'accent-hover': '#2a2420',
                        success: '#5d7a4f',
                        warning: '#a6875a',
                        danger: '#9e4f4f',
                    },
                    fontFamily: {
                        sans: ['DM Sans', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <style>
        [ng-cloak] { display: none !important; }
        body { font-family: 'DM Sans', sans-serif; background-color: #e8e5db; color: #1c1b18; -webkit-font-smoothing: antialiased; }
        .stagger-item { opacity: 0; }
        .stagger-inner { }
        .sidebar-link { border-radius: 8px; transition: all 0.25s ease; }
        .sidebar-link.active { background: #faf8f4; border: 1px solid #e1ddd4; }
        .sidebar-link:not(.active):hover { background: rgba(58,50,44,0.05); }
        .stat-card { position: relative; overflow: hidden; }
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 3px;
            background: #3a322c;
            transform: scaleX(0);
            transform-origin: left;
        }
        .stat-card.animated::after { animation: statBarIn 0.8s ease forwards; }
        @keyframes statBarIn { to { transform: scaleX(1); } }
        .pulse-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #3a322c;
            position: relative;
        }
        .pulse-dot::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: rgba(58,50,44,0.2);
            animation: pulseRing 2s ease infinite;
        }
        @keyframes pulseRing {
            0% { transform: scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .count-up { display: inline-block; }
    </style>
</head>
<body ng-controller="AdminController" ng-cloak>

    <div ng-if="pageLoading" class="fixed inset-0 z-[60] flex items-center justify-center bg-bg">
        <div class="text-center">
            <div class="font-sans font-medium text-[0.875rem] tracking-[0.15em] uppercase text-ink/40">AURA</div>
            <div class="mt-4 flex gap-2 justify-center">
                <div class="w-2 h-2 rounded-full bg-accent animate-bounce" style="animation-delay:0s"></div>
                <div class="w-2 h-2 rounded-full bg-accent/60 animate-bounce" style="animation-delay:0.15s"></div>
                <div class="w-2 h-2 rounded-full bg-accent/30 animate-bounce" style="animation-delay:0.3s"></div>
            </div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden" ng-show="!pageLoading">
        <!-- Sidebar -->
        <aside class="w-64 bg-surface border-r border-border-light flex flex-col flex-shrink-0">
            <div class="p-6 border-b border-border-light">
                <a href="../index.html" class="flex items-center gap-3 no-underline">
                    <span class="inline-flex items-center justify-center w-8 h-8 bg-accent text-bg text-[0.75rem] font-semibold">A</span>
                    <div>
                        <div class="font-sans font-medium text-[0.875rem] tracking-[0.15em] uppercase text-ink">AURA</div>
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Admin Console</div>
                    </div>
                </a>
            </div>
            <nav class="flex-1 py-6 px-4 flex flex-col gap-1 overflow-y-auto">
                <a href="dashboard.php" class="sidebar-link active flex items-center gap-4 py-3 px-4 text-ink no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="font-sans font-medium text-[0.875rem]">Dashboard</span>
                </a>
                <a href="maintenance.php" class="sidebar-link flex items-center gap-4 py-3 px-4 text-ink-secondary hover:text-ink transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="font-sans text-[0.875rem]">Maintenance</span>
                    <span ng-if="stats.maintenance.pending_count > 0" class="ml-auto bg-warning/10 text-warning font-mono text-[0.5rem] font-medium px-2 py-0.5">{{stats.maintenance.pending_count}}</span>
                </a>
                <a href="amenities.php" class="sidebar-link flex items-center gap-4 py-3 px-4 text-ink-secondary hover:text-ink transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="font-sans text-[0.875rem]">Amenities</span>
                </a>
                <a href="amenity_bookings.php" class="sidebar-link flex items-center gap-4 py-3 px-4 text-ink-secondary hover:text-ink transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="font-sans text-[0.875rem]">Bookings</span>
                    <span ng-if="stats.amenities.today_count > 0" class="ml-auto bg-warning/10 text-warning font-mono text-[0.5rem] font-medium px-2 py-0.5">{{stats.amenities.today_count}}</span>
                </a>
                <a href="inquiries.php" class="sidebar-link flex items-center gap-4 py-3 px-4 text-ink-secondary hover:text-ink transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="font-sans text-[0.875rem]">Inquiries</span>
                    <span ng-if="stats.inquiries.pending_inquiries > 0" class="ml-auto bg-warning/10 text-warning font-mono text-[0.5rem] font-medium px-2 py-0.5">{{stats.inquiries.pending_inquiries}}</span>
                </a>
                <a href="rentals.php" class="sidebar-link flex items-center gap-4 py-3 px-4 text-ink-secondary hover:text-ink transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <span class="font-sans text-[0.875rem]">Rentals</span>
                </a>
                <a href="users.php" class="sidebar-link flex items-center gap-4 py-3 px-4 text-ink-secondary hover:text-ink transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="font-sans text-[0.875rem]">Users</span>
                </a>
                <a href="blog.php" class="sidebar-link flex items-center gap-4 py-3 px-4 text-ink-secondary hover:text-ink transition-colors no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 4h4a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h4m4-2v6m0 0l-3-3m3 3l3-3"/></svg>
                    <span class="font-sans text-[0.875rem]">Blog</span>
                </a>
                <hr class="border-border-light my-4">
                <a href="../index.html" target="_blank" class="flex items-center gap-4 py-2 px-4 text-muted hover:text-ink-secondary transition-colors font-sans text-[0.875rem] no-underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    <span>View Site</span>
                </a>
            </nav>
            <div class="p-4 border-t border-border-light">
                <div class="flex items-center gap-3 mb-3 px-2">
                    <div class="w-8 h-8 flex items-center justify-center border border-border">
                        <span class="text-ink font-sans font-medium text-[0.75rem]"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-sans font-medium text-[0.875rem] text-ink truncate"><?php echo $_SESSION['user_name']; ?></div>
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Administrator</div>
                    </div>
                </div>
                <a href="logout.php" class="flex items-center justify-center gap-2 py-3 px-4 border border-border text-ink-secondary hover:text-ink hover:bg-bg transition-colors font-mono text-[0.625rem] tracking-[0.02em] uppercase no-underline">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 overflow-auto bg-bg">
            <div class="p-8 max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-end mb-6 stagger-item">
                    <div>
                        <h1 class="font-sans font-medium text-[2rem] text-ink" id="dashboardTitle">Dashboard</h1>
                        <p class="font-sans text-[0.875rem] text-ink-secondary mt-1">Real-time management overview</p>
                    </div>
                    <div class="flex items-center gap-3 bg-surface border border-border-light px-4 py-2">
                        <span ng-if="isPolling" class="flex items-center gap-2 font-mono text-[0.625rem] text-ink-secondary">
                            <span class="pulse-dot"></span>
                            Live
                        </span>
                        <span class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-muted">5s</span>
                    </div>
                </div>
                <div class="h-px bg-border-light mb-8 stagger-item"></div>

                <!-- Primary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8" id="statGrid">
                    <div class="bg-surface border border-border-light p-6 stagger-item stat-card" data-stat>
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Total</span>
                            <svg class="w-5 h-5 text-ink-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="font-sans font-medium text-[2rem] text-ink mb-1 count-up" id="statProperties">{{stats.properties.total || 0}}</div>
                        <div class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Properties</div>
                    </div>
                    <div class="bg-surface border border-border-light p-6 stagger-item stat-card">
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Pending</span>
                            <svg class="w-5 h-5 text-ink-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="font-sans font-medium text-[2rem] text-ink mb-1 count-up">{{stats.maintenance.pending_count || 0}}</div>
                        <div class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Maintenance</div>
                    </div>
                    <div class="bg-surface border border-border-light p-6 stagger-item stat-card">
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Today</span>
                            <svg class="w-5 h-5 text-ink-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="font-sans font-medium text-[2rem] text-ink mb-1 count-up">{{stats.amenities.today_count || 0}}</div>
                        <div class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Bookings</div>
                    </div>
                    <div class="bg-surface border border-border-light p-6 stagger-item stat-card">
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">New</span>
                            <svg class="w-5 h-5 text-ink-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="font-sans font-medium text-[2rem] text-ink mb-1 count-up">{{stats.inquiries.pending_inquiries || 0}}</div>
                        <div class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Inquiries</div>
                    </div>
                </div>

                <!-- Secondary Stats -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-10">
                    <div class="bg-bg-alt border border-border-light p-4 stagger-item">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">Completed</div>
                        <div class="font-sans font-medium text-[1.125rem] text-ink">{{stats.maintenance.completed_count || 0}}</div>
                    </div>
                    <div class="bg-bg-alt border border-border-light p-4 stagger-item">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">In Progress</div>
                        <div class="font-sans font-medium text-[1.125rem] text-ink">{{stats.maintenance.in_progress_count || 0}}</div>
                    </div>
                    <div class="bg-bg-alt border border-border-light p-4 stagger-item">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">Active Amenities</div>
                        <div class="font-sans font-medium text-[1.125rem] text-ink">{{stats.amenities.active_count || 0}}</div>
                    </div>
                    <div class="bg-bg-alt border border-border-light p-4 stagger-item">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">Upcoming</div>
                        <div class="font-sans font-medium text-[1.125rem] text-ink">{{stats.amenities.upcoming_count || 0}}</div>
                    </div>
                    <div class="bg-bg-alt border border-border-light p-4 stagger-item">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">Active Rentals</div>
                        <div class="font-sans font-medium text-[1.125rem] text-ink">{{stats.rentals.active_rentals || 0}}</div>
                    </div>
                </div>

                <!-- Notifications -->
                <div ng-if="notifications.length > 0" class="mb-6 stagger-item">
                    <div ng-repeat="n in notifications" class="flex items-center gap-3 px-4 py-3 mb-2 border" ng-class="{'bg-danger/5 border-danger/20 text-danger': n.type == 'overdue', 'bg-success/5 border-success/20 text-success': n.type == 'resolved', 'bg-warning/5 border-warning/20 text-warning': n.type == 'pending'}">
                        <span class="font-sans text-[0.875rem]">{{n.message}}</span>
                        <span class="font-mono text-[0.5625rem] opacity-60 ml-auto">{{n.time}}</span>
                    </div>
                </div>

                <!-- KPI -->
                <div id="kpiSection" class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8 stagger-item">
                    <div class="bg-surface border border-border-light p-4">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">Avg Resolution</div>
                        <div class="font-sans font-medium text-[1.125rem] text-ink">{{stats.kpi.avg_resolution_hours || 'N/A'}}<span ng-if="stats.kpi.avg_resolution_hours" class="text-[0.75rem] text-ink-secondary ml-1">hrs</span></div>
                        <div class="font-mono text-[0.5rem] text-ink-secondary/50 mt-1" ng-class="{'text-danger': stats.kpi.avg_resolution_hours > 48, 'text-success': stats.kpi.avg_resolution_hours <= 48 && stats.kpi.avg_resolution_hours > 0}">
                            {{stats.kpi.avg_resolution_hours > 48 ? '⚠ Exceeds 48h target' : stats.kpi.avg_resolution_hours > 0 ? '✓ Within 48h target' : ''}}
                        </div>
                    </div>
                    <div class="bg-surface border border-border-light p-4">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">Completion Rate</div>
                        <div class="font-sans font-medium text-[1.125rem]" ng-class="{'text-success': stats.kpi.completion_rate >= 90, 'text-warning': stats.kpi.completion_rate < 90 && stats.kpi.completion_rate >= 0}">{{stats.kpi.completion_rate || 0}}%</div>
                        <div class="font-mono text-[0.5rem] text-ink-secondary/50 mt-1" ng-class="{'text-success': stats.kpi.completion_rate >= 90, 'text-warning': stats.kpi.completion_rate < 90 && stats.kpi.completion_rate >= 0}">
                            {{stats.kpi.completion_rate >= 90 ? '✓ Goal met (≥90%)' : stats.kpi.completion_rate >= 0 ? '⚠ Below 90% target' : ''}}
                        </div>
                    </div>
                    <div class="bg-surface border border-border-light p-4">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">Overdue Requests</div>
                        <div class="font-sans font-medium text-[1.125rem]" ng-class="{'text-danger': stats.kpi.overdue_count > 0, 'text-success': stats.kpi.overdue_count == 0}">{{stats.kpi.overdue_count || 0}}</div>
                        <div class="font-mono text-[0.5rem] text-ink-secondary/50 mt-1">{{stats.kpi.overdue_count > 0 ? 'Beyond 48h resolution time' : 'No overdue items'}}</div>
                    </div>
                    <div class="bg-surface border border-border-light p-4">
                        <div class="font-mono text-[0.5rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-1">Today's Conflicts</div>
                        <div class="font-sans font-medium text-[1.125rem]" ng-class="{'text-success': stats.kpi.today_conflicts == 0, 'text-danger': stats.kpi.today_conflicts > 0}">{{stats.kpi.today_conflicts || 0}}</div>
                        <div class="font-mono text-[0.5rem] text-ink-secondary/50 mt-1">{{stats.kpi.today_conflicts > 0 ? '⚠ Booking conflicts detected' : '✓ No conflicts'}}</div>
                    </div>
                </div>

                <!-- Panels -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Recent Maintenance -->
                    <div class="bg-surface border border-border-light flex flex-col h-[500px] stagger-item">
                        <div class="px-6 py-5 border-b border-border-light flex justify-between items-center">
                            <h2 class="font-sans font-medium text-[1rem] text-ink">Maintenance</h2>
                            <a href="maintenance.php" class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary hover:text-ink transition-colors no-underline">View →</a>
                        </div>
                        <div class="p-6 flex-1 overflow-y-auto">
                            <p ng-if="!recentMaintenance || recentMaintenance.length == 0" class="text-ink-secondary text-[0.875rem] font-sans text-center mt-10">No recent maintenance requests.</p>
                            <div class="flex flex-col gap-3">
                                <div ng-repeat="r in recentMaintenance" class="flex items-start justify-between p-4 border border-border-light hover:border-border transition stagger-inner" ng-class="{'border-border bg-bg-alt': r._updated}">
                                    <div class="flex-1 pr-4">
                                        <p class="font-sans font-medium text-ink text-[0.875rem] mb-1">{{r.property_title}}</p>
                                        <p class="font-sans text-[0.75rem] text-ink-secondary line-clamp-2">{{r.issue_description}}</p>
                                        <p class="font-mono text-[0.5625rem] text-muted mt-2">{{r.created_at}}</p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                        <span class="px-3 py-1 font-mono text-[0.5625rem] font-medium uppercase tracking-[0.02em]" ng-class="getStatusClass(r.status)">{{r.status.replace('_', ' ')}}</span>
                                        <span class="px-2 py-0.5 font-mono text-[0.5rem] border font-medium uppercase tracking-[0.02em]" ng-class="getPriorityClass(r.priority)">{{r.priority}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Bookings -->
                    <div class="bg-surface border border-border-light flex flex-col h-[500px] stagger-item">
                        <div class="px-6 py-5 border-b border-border-light flex justify-between items-center">
                            <h2 class="font-sans font-medium text-[1rem] text-ink">Today's Bookings</h2>
                            <a href="amenity_bookings.php" class="font-mono text-[0.5625rem] tracking-[0.02em] uppercase text-ink-secondary hover:text-ink transition-colors no-underline">View →</a>
                        </div>
                        <div class="p-6 flex-1 overflow-y-auto">
                            <p ng-if="!todayBookings || todayBookings.length == 0" class="text-ink-secondary text-[0.875rem] font-sans text-center mt-10">No bookings for today.</p>
                            <div class="flex flex-col gap-3">
                                <div ng-repeat="b in todayBookings" class="flex items-center justify-between p-4 border border-border-light hover:border-border transition stagger-inner" ng-class="{'border-border': b._updated}">
                                    <div>
                                        <p class="font-sans font-medium text-ink text-[0.875rem] mb-1">{{b.amenity_name}}</p>
                                        <p class="font-sans text-[0.75rem] text-ink-secondary">{{b.guest_name}}</p>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <p class="font-sans font-medium text-[0.875rem] text-ink mb-2 bg-bg-alt px-3 py-1">{{formatTime(b.check_in_time)}} — {{formatTime(b.check_out_time)}}</p>
                                        <span class="font-mono text-[0.5625rem] px-3 py-1 font-medium uppercase tracking-[0.02em]" ng-class="getBookingStatusClass(b.status)">{{b.status.replace('_', ' ')}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        var app = angular.module('adminApp', []);

        app.controller('AdminController', function($scope, $http, $timeout, $window) {
            $scope.pageLoading = true;
            $scope.stats = {};
            $scope.recentMaintenance = [];
            $scope.todayBookings = [];
            $scope.isPolling = false;
            $scope.notifications = [];
            $scope._seenOverdueIds = {};

            $scope.formatTime = function(tStr) {
                if(!tStr) return '';
                let parts = tStr.split(':');
                if(parts.length < 2) return tStr;
                let h = parseInt(parts[0]), m = parts[1], ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12; h = h ? h : 12;
                return h + ':' + m + ' ' + ampm;
            };

            $scope._checkNotifications = function(m) {
                if (!m) return;
                if (m.overdue_count > 0 && !$scope._seenOverdueIds['o_' + m.overdue_count]) {
                    $scope._seenOverdueIds['o_' + m.overdue_count] = true;
                    if ($scope.notifications.length < 5) {
                        $scope.notifications.unshift({
                            type: 'overdue',
                            message: m.overdue_count + ' maintenance request(s) overdue (>48h)',
                            time: new Date().toLocaleTimeString()
                        });
                    }
                }
            };

            $scope.loadDashboardData = function() {
                return $http.get('../api/dashboard.php').then(function(res) {
                    $scope.stats = {
                        properties: res.data.properties,
                        maintenance: res.data.maintenance,
                        amenities: res.data.amenities,
                        inquiries: res.data.inquiries,
                        rentals: res.data.rentals,
                        kpi: res.data.kpi || {}
                    };
                    $scope.recentMaintenance = res.data.kpirecent_maintenance_requests || [];
                    $scope.todayBookings = res.data.today_bookings || [];
                    $scope._checkNotifications(res.data.kpi);
                });
            };

            $scope.loadDashboardData().then(function() {
                $scope.pageLoading = false;
                $scope.animateEntry();
                $scope.startSSE();
            });

            var sseSource = null;
            $scope.startSSE = function() {
                $scope.isPolling = true;
                sseSource = new EventSource('../api/sse.php');
                sseSource.addEventListener('new_maintenance', function(e) {
                    $scope.$apply(function() { $scope._refreshDashboard(); });
                });
                sseSource.addEventListener('new_booking', function(e) {
                    $scope.$apply(function() { $scope._refreshDashboard(); });
                });
                sseSource.onerror = function() {
                    setTimeout(function() { $scope.$apply($scope.startSSE); }, 3000);
                };
            };

            $scope._refreshDashboard = function() {
                $http.get('../api/dashboard.php').then(function(res) {
                    var oldPending = $scope.stats.maintenance ? $scope.stats.maintenance.pending_count : 0;
                    var oldInProgress = $scope.stats.maintenance ? $scope.stats.maintenance.in_progress_count : 0;
                    var oldOverdue = $scope.stats.kpi ? $scope.stats.kpi.overdue_count : 0;
                    $scope.stats = {
                        properties: res.data.properties,
                        maintenance: res.data.maintenance,
                        amenities: res.data.amenities,
                        inquiries: res.data.inquiries,
                        rentals: res.data.rentals,
                        kpi: res.data.kpi || {}
                    };
                    var newPending = $scope.stats.maintenance.pending_count || 0;
                    var newInProgress = $scope.stats.maintenance.in_progress_count || 0;
                    var newOverdue = $scope.stats.kpi.overdue_count || 0;
                    $scope._checkNotifications($scope.stats.kpi);
                    if (oldPending !== newPending || oldInProgress !== newInProgress || oldOverdue !== newOverdue) {
                        document.querySelectorAll('.count-up').forEach(function(el) {
                            gsap.from(el, { scale: 1.2, duration: 0.3, ease: 'power2.out', clearProps: 'scale' });
                        });
                        gsap.fromTo('#kpiSection', { backgroundColor: 'rgba(90,120,80,0.08)' }, { backgroundColor: 'transparent', duration: 1.5 });
                    }
                    let newMaint = res.data.kpirecent_maintenance_requests || [];
                    if($scope.recentMaintenance.length !== newMaint.length || (newMaint[0] && $scope.recentMaintenance[0] && (newMaint[0].id !== $scope.recentMaintenance[0].id || newMaint[0].status !== $scope.recentMaintenance[0].status))) {
                        $scope.recentMaintenance = newMaint;
                    }
                    let newBookings = res.data.today_bookings || [];
                    if($scope.todayBookings.length !== newBookings.length || (newBookings[0] && $scope.todayBookings[0] && (newBookings[0].id !== $scope.todayBookings[0].id || newBookings[0].status !== $scope.todayBookings[0].status))) {
                        $scope.todayBookings = newBookings;
                    }
                });
            };

            $scope.$on('$destroy', function() { if (sseSource) sseSource.close(); });

            $scope.animateEntry = function() {
                $timeout(function() {
                    var tl = gsap.timeline();

                    tl.fromTo(".stagger-item",
                        { opacity: 0, y: 30 },
                        { opacity: 1, y: 0, duration: 0.7, stagger: 0.06, ease: "power4.out", clearProps: "transform" }
                    );

                    var innerItems = document.querySelectorAll(".stagger-inner");
                    if (innerItems.length) {
                        tl.fromTo(innerItems,
                            { opacity: 0, x: -12 },
                            { opacity: 1, x: 0, duration: 0.5, stagger: 0.04, ease: "power3.out", clearProps: "transform" }
                        , "-=0.2");
                    }

                    tl.from("#dashboardTitle", {
                        scale: 0.8,
                        opacity: 0,
                        duration: 0.6,
                        ease: "back.out(1.7)",
                        clearProps: "all"
                    }, 0);

                    tl.call(function() {
                        document.querySelectorAll('.stat-card').forEach(function(el) {
                            el.classList.add('animated');
                        });
                    }, null, "-=0.5");
                }, 50);
            };

            $scope.getStatusClass = function(s) {
                const map = { pending: 'bg-warning/10 text-warning', in_progress: 'text-ink bg-ink/10', completed: 'bg-success/10 text-success' };
                return map[s] || 'text-muted bg-bg';
            };
            $scope.getPriorityClass = function(p) {
                const map = { low: 'text-muted border-border', medium: 'text-ink border-border', high: 'text-warning border-warning/30', urgent: 'text-danger border-danger/30 bg-danger/10' };
                return map[p] || 'text-muted border-border';
            };
            $scope.getBookingStatusClass = function(s) {
                const map = { confirmed: 'bg-warning/10 text-warning', checked_in: 'bg-success/10 text-success', cancelled: 'bg-danger/10 text-danger', completed: 'text-muted bg-bg' };
                return map[s] || 'text-muted bg-bg';
            };
        });
    </script>
</body>
</html>
