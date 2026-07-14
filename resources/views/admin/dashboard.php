<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::requireStaff();
Auth::startSession();
?>
<!DOCTYPE html>
<html lang="en" ng-app="adminApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Aura Estates</title>
    <link rel="icon" type="image/svg+xml" href="../../../favicon.svg">
    <script src="../resources/js/tailwindcss.js"></script>
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
                        fontFamily: {
                        sans: ['DM Sans', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                        serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,700&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../resources/js/angular.min.js"></script>
    <script src="../resources/js/gsap.min.js"></script>
    <script>window.AURA_API_BASE = '../api/';</script>
    <script src="../resources/js/api-http.js"></script>
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
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #5d7a4f;
            position: relative;
        }
        .pulse-dot::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: rgba(93, 122, 79, 0.25);
            animation: pulseRing 2s ease infinite;
        }
        @keyframes pulseRing {
            0% { transform: scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .count-up { display: inline-block; }
        /* Custom scrollbar for panels */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d6d2c8;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9a9086;
        }
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
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main -->
        <main class="flex-1 overflow-auto bg-bg">
            <div class="p-8 max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-end mb-6 stagger-item">
                    <div>
                        <h1 class="font-serif font-light italic text-[2.5rem] text-ink leading-tight" id="dashboardTitle">Dashboard</h1>
                        <p class="font-sans text-[0.8125rem] text-ink-secondary/70 mt-1.5 font-light">Real-time management overview</p>
                    </div>
                    <div class="flex items-center gap-3 bg-surface border border-border-light px-4 py-2 rounded-full shadow-sm hover:shadow-md transition-all duration-300">
                        <span ng-if="isPolling" class="flex items-center gap-2 font-mono text-[0.625rem] text-ink-secondary font-medium uppercase tracking-wider">
                            <span class="pulse-dot"></span>
                            Live Connection
                        </span>
                        <span class="font-mono text-[0.5625rem] tracking-[0.05em] uppercase text-muted/80 bg-bg-alt px-2 py-0.5 rounded-full">5s Refresh</span>
                    </div>
                </div>
                <div class="h-px bg-border-light mb-8 stagger-item"></div>

                <!-- Primary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8" id="statGrid">
                    <!-- Stat 1 -->
                    <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 stagger-item stat-card group" data-stat>
                        <div class="flex items-center justify-between mb-5">
                            <span class="font-mono text-[0.5625rem] tracking-[0.1em] uppercase text-ink-secondary/65">Total Listings</span>
                            <div class="w-8 h-8 rounded-full bg-bg-alt/80 flex items-center justify-center text-accent/80 group-hover:bg-accent group-hover:text-bg transition-colors duration-300">
                                <i class="fas fa-building text-[10px]"></i>
                            </div>
                        </div>
                        <div class="font-serif italic font-light text-[2.5rem] text-ink leading-none mb-2 count-up" id="statProperties">{{stats.properties.total || 0}}</div>
                        <div class="font-sans text-[0.6875rem] font-medium tracking-[0.05em] uppercase text-ink-secondary/50">Properties Listed</div>
                    </div>
                    <!-- Stat 2 -->
                    <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 stagger-item stat-card group">
                        <div class="flex items-center justify-between mb-5">
                            <span class="font-mono text-[0.5625rem] tracking-[0.1em] uppercase text-ink-secondary/65">Active Tickets</span>
                            <div class="w-8 h-8 rounded-full bg-bg-alt/80 flex items-center justify-center text-accent/80 group-hover:bg-accent group-hover:text-bg transition-colors duration-300">
                                <i class="fas fa-wrench text-[10px]"></i>
                            </div>
                        </div>
                        <div class="font-serif italic font-light text-[2.5rem] text-ink leading-none mb-2 count-up">{{stats.maintenance.pending_count || 0}}</div>
                        <div class="font-sans text-[0.6875rem] font-medium tracking-[0.05em] uppercase text-ink-secondary/50">Pending Maintenance</div>
                    </div>
                    <!-- Stat 3 -->
                    <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 stagger-item stat-card group">
                        <div class="flex items-center justify-between mb-5">
                            <span class="font-mono text-[0.5625rem] tracking-[0.1em] uppercase text-ink-secondary/65">Today's Usage</span>
                            <div class="w-8 h-8 rounded-full bg-bg-alt/80 flex items-center justify-center text-accent/80 group-hover:bg-accent group-hover:text-bg transition-colors duration-300">
                                <i class="fas fa-calendar-check text-[10px]"></i>
                            </div>
                        </div>
                        <div class="font-serif italic font-light text-[2.5rem] text-ink leading-none mb-2 count-up">{{stats.amenities.today_count || 0}}</div>
                        <div class="font-sans text-[0.6875rem] font-medium tracking-[0.05em] uppercase text-ink-secondary/50">Amenity Bookings</div>
                    </div>
                    <!-- Stat 4 -->
                    <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 stagger-item stat-card group">
                        <div class="flex items-center justify-between mb-5">
                            <span class="font-mono text-[0.5625rem] tracking-[0.1em] uppercase text-ink-secondary/65">New Contacts</span>
                            <div class="w-8 h-8 rounded-full bg-bg-alt/80 flex items-center justify-center text-accent/80 group-hover:bg-accent group-hover:text-bg transition-colors duration-300">
                                <i class="fas fa-envelope text-[10px]"></i>
                            </div>
                        </div>
                        <div class="font-serif italic font-light text-[2.5rem] text-ink leading-none mb-2 count-up">{{stats.inquiries.pending_inquiries || 0}}</div>
                        <div class="font-sans text-[0.6875rem] font-medium tracking-[0.05em] uppercase text-ink-secondary/50">Pending Inquiries</div>
                    </div>
                </div>

                <!-- Secondary Stats -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                    <div class="bg-surface/50 border border-border-light p-4 rounded-xl shadow-sm stagger-item hover:border-accent/15 transition-colors">
                        <div class="font-mono text-[0.5rem] tracking-[0.08em] uppercase text-ink-secondary/65 mb-1.5">Completed Maint.</div>
                        <div class="font-sans font-semibold text-[1.25rem] text-ink">{{stats.maintenance.completed_count || 0}}</div>
                    </div>
                    <div class="bg-surface/50 border border-border-light p-4 rounded-xl shadow-sm stagger-item hover:border-accent/15 transition-colors">
                        <div class="font-mono text-[0.5rem] tracking-[0.08em] uppercase text-ink-secondary/65 mb-1.5">In Progress Maint.</div>
                        <div class="font-sans font-semibold text-[1.25rem] text-ink">{{stats.maintenance.in_progress_count || 0}}</div>
                    </div>
                    <div class="bg-surface/50 border border-border-light p-4 rounded-xl shadow-sm stagger-item hover:border-accent/15 transition-colors">
                        <div class="font-mono text-[0.5rem] tracking-[0.08em] uppercase text-ink-secondary/65 mb-1.5">Active Amenities</div>
                        <div class="font-sans font-semibold text-[1.25rem] text-ink">{{stats.amenities.active_count || 0}}</div>
                    </div>
                    <div class="bg-surface/50 border border-border-light p-4 rounded-xl shadow-sm stagger-item hover:border-accent/15 transition-colors">
                        <div class="font-mono text-[0.5rem] tracking-[0.08em] uppercase text-ink-secondary/65 mb-1.5">Upcoming Bookings</div>
                        <div class="font-sans font-semibold text-[1.25rem] text-ink">{{stats.amenities.upcoming_count || 0}}</div>
                    </div>
                    <div class="bg-surface/50 border border-border-light p-4 rounded-xl shadow-sm stagger-item hover:border-accent/15 transition-colors">
                        <div class="font-mono text-[0.5rem] tracking-[0.08em] uppercase text-ink-secondary/65 mb-1.5">Active Leases</div>
                        <div class="font-sans font-semibold text-[1.25rem] text-ink">{{stats.rentals.active_rentals || 0}}</div>
                    </div>
                </div>

                <!-- Notifications -->
                <div ng-if="notifications.length > 0" class="mb-6 stagger-item">
                    <div ng-repeat="n in notifications" class="flex items-center gap-3 px-4 py-3 mb-2 border rounded-xl" ng-class="{'bg-danger/5 border-danger/20 text-danger': n.type == 'overdue', 'bg-success/5 border-success/20 text-success': n.type == 'resolved', 'bg-warning/5 border-warning/20 text-warning': n.type == 'pending'}">
                        <span class="w-2 h-2 rounded-full" ng-class="{'bg-danger animate-pulse': n.type == 'overdue', 'bg-success': n.type == 'resolved', 'bg-warning': n.type == 'pending'}"></span>
                        <span class="font-sans text-[0.875rem] font-light">{{n.message}}</span>
                        <span class="font-mono text-[0.5625rem] opacity-60 ml-auto">{{n.time}}</span>
                    </div>
                </div>

                <!-- KPI Section -->
                <div id="kpiSection" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 stagger-item">
                    <!-- KPI 1 -->
                    <div class="bg-surface border border-border-light p-5 rounded-2xl shadow-sm hover:border-accent/20 transition-all duration-300">
                        <div class="font-mono text-[0.5rem] tracking-[0.1em] uppercase text-ink-secondary/65 mb-2">Avg Resolution</div>
                        <div class="font-serif italic font-light text-[1.5rem] text-ink">
                            {{stats.kpi.avg_resolution_hours || 'N/A'}}<span ng-if="stats.kpi.avg_resolution_hours" class="text-[0.875rem] font-sans text-ink-secondary/60 ml-1 font-normal">hrs</span>
                        </div>
                        <div class="font-sans text-[0.625rem] mt-2.5 flex items-center gap-1.5 font-medium" ng-class="{'text-danger': stats.kpi.avg_resolution_hours > 48, 'text-success': stats.kpi.avg_resolution_hours <= 48 && stats.kpi.avg_resolution_hours > 0}">
                            <span class="w-1.5 h-1.5 rounded-full" ng-class="{'bg-danger animate-pulse': stats.kpi.avg_resolution_hours > 48, 'bg-success': stats.kpi.avg_resolution_hours <= 48 && stats.kpi.avg_resolution_hours > 0}"></span>
                            {{stats.kpi.avg_resolution_hours > 48 ? 'Exceeds 48h limit' : stats.kpi.avg_resolution_hours > 0 ? 'Within 48h target' : 'No data yet'}}
                        </div>
                    </div>
                    <!-- KPI 2 -->
                    <div class="bg-surface border border-border-light p-5 rounded-2xl shadow-sm hover:border-accent/20 transition-all duration-300">
                        <div class="font-mono text-[0.5rem] tracking-[0.1em] uppercase text-ink-secondary/65 mb-2">Completion Rate</div>
                        <div class="font-serif italic font-light text-[1.5rem]" ng-class="{'text-success': stats.kpi.completion_rate >= 90, 'text-warning': stats.kpi.completion_rate < 90 && stats.kpi.completion_rate >= 0}">
                            {{stats.kpi.completion_rate || 0}}%
                        </div>
                        <div class="font-sans text-[0.625rem] mt-2.5 flex items-center gap-1.5 font-medium" ng-class="{'text-success': stats.kpi.completion_rate >= 90, 'text-warning': stats.kpi.completion_rate < 90 && stats.kpi.completion_rate >= 0}">
                            <span class="w-1.5 h-1.5 rounded-full" ng-class="{'bg-success': stats.kpi.completion_rate >= 90, 'bg-warning animate-pulse': stats.kpi.completion_rate < 90 && stats.kpi.completion_rate >= 0}"></span>
                            {{stats.kpi.completion_rate >= 90 ? 'Goal met (≥90%)' : stats.kpi.completion_rate >= 0 ? 'Below 90% target' : 'No data'}}
                        </div>
                    </div>
                    <!-- KPI 3 -->
                    <div class="bg-surface border border-border-light p-5 rounded-2xl shadow-sm hover:border-accent/20 transition-all duration-300">
                        <div class="font-mono text-[0.5rem] tracking-[0.1em] uppercase text-ink-secondary/65 mb-2">Overdue Requests</div>
                        <div class="font-serif italic font-light text-[1.5rem]" ng-class="{'text-danger': stats.kpi.overdue_count > 0, 'text-success': stats.kpi.overdue_count == 0}">
                            {{stats.kpi.overdue_count || 0}}
                        </div>
                        <div class="font-sans text-[0.625rem] mt-2.5 flex items-center gap-1.5 font-medium" ng-class="{'text-danger': stats.kpi.overdue_count > 0, 'text-success': stats.kpi.overdue_count == 0}">
                            <span class="w-1.5 h-1.5 rounded-full" ng-class="{'bg-danger animate-pulse': stats.kpi.overdue_count > 0, 'bg-success': stats.kpi.overdue_count == 0}"></span>
                            {{stats.kpi.overdue_count > 0 ? 'Beyond 48h SLA' : 'All items in SLA'}}
                        </div>
                    </div>
                    <!-- KPI 4 -->
                    <div class="bg-surface border border-border-light p-5 rounded-2xl shadow-sm hover:border-accent/20 transition-all duration-300">
                        <div class="font-mono text-[0.5rem] tracking-[0.1em] uppercase text-ink-secondary/65 mb-2">Today's Conflicts</div>
                        <div class="font-serif italic font-light text-[1.5rem]" ng-class="{'text-success': stats.kpi.today_conflicts == 0, 'text-danger': stats.kpi.today_conflicts > 0}">
                            {{stats.kpi.today_conflicts || 0}}
                        </div>
                        <div class="font-sans text-[0.625rem] mt-2.5 flex items-center gap-1.5 font-medium" ng-class="{'text-success': stats.kpi.today_conflicts == 0, 'text-danger': stats.kpi.today_conflicts > 0}">
                            <span class="w-1.5 h-1.5 rounded-full" ng-class="{'bg-success': stats.kpi.today_conflicts == 0, 'bg-danger animate-pulse': stats.kpi.today_conflicts > 0}"></span>
                            {{stats.kpi.today_conflicts > 0 ? 'Conflicts detected' : 'No conflicts'}}
                        </div>
                    </div>
                </div>

                <!-- Panels -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Recent Maintenance -->
                    <div class="bg-surface border border-border-light rounded-2xl flex flex-col h-[520px] shadow-sm stagger-item overflow-hidden">
                        <div class="px-6 py-5 border-b border-border-light bg-surface/50 backdrop-blur-sm flex justify-between items-center">
                            <h2 class="font-serif font-light italic text-[1.25rem] text-ink">Recent Maintenance</h2>
                            <a href="<?php echo Auth::getBasePrefix(); ?>/admin/maintenance" class="font-mono text-[0.625rem] tracking-[0.1em] uppercase text-accent hover:text-accent-hover transition-colors no-underline font-semibold">View All →</a>
                        </div>
                        <div class="p-6 flex-1 overflow-y-auto custom-scrollbar">
                            <p ng-if="!recentMaintenance || recentMaintenance.length == 0" class="text-ink-secondary/60 text-[0.875rem] font-sans text-center mt-12 font-light">No recent maintenance requests.</p>
                            <div class="flex flex-col gap-4">
                                <div ng-repeat="r in recentMaintenance" class="flex items-start justify-between p-4 border border-border-light hover:border-accent/20 rounded-xl hover:bg-bg-alt/25 transition-all duration-300 stagger-inner" ng-class="{'border-accent/40 bg-bg-alt/20': r._updated}">
                                    <div class="flex-1 pr-4">
                                        <p class="font-sans font-semibold text-ink text-[0.875rem] mb-1.5">{{r.property_title}}</p>
                                        <p class="font-sans text-[0.8125rem] text-ink-secondary/80 font-light leading-relaxed line-clamp-2">{{r.issue_description}}</p>
                                        <p class="font-mono text-[0.5625rem] text-muted/80 mt-3 flex items-center gap-1.5">
                                            <i class="far fa-clock"></i> {{r.created_at}}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                        <span class="px-2.5 py-1 font-mono text-[0.5625rem] font-medium uppercase tracking-wider rounded" ng-class="getStatusClass(r.status)">{{r.status.replace('_', ' ')}}</span>
                                        <span class="px-2 py-0.5 font-mono text-[0.5rem] border rounded font-medium uppercase tracking-wider" ng-class="getPriorityClass(r.priority)">{{r.priority}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Bookings -->
                    <div class="bg-surface border border-border-light rounded-2xl flex flex-col h-[520px] shadow-sm stagger-item overflow-hidden">
                        <div class="px-6 py-5 border-b border-border-light bg-surface/50 backdrop-blur-sm flex justify-between items-center">
                            <h2 class="font-serif font-light italic text-[1.25rem] text-ink">Today's Bookings</h2>
                            <a href="<?php echo Auth::getBasePrefix(); ?>/admin/amenity-bookings" class="font-mono text-[0.625rem] tracking-[0.1em] uppercase text-accent hover:text-accent-hover transition-colors no-underline font-semibold">View All →</a>
                        </div>
                        <div class="p-6 flex-1 overflow-y-auto custom-scrollbar">
                            <p ng-if="!todayBookings || todayBookings.length == 0" class="text-ink-secondary/60 text-[0.875rem] font-sans text-center mt-12 font-light">No bookings scheduled for today.</p>
                            <div class="flex flex-col gap-4">
                                <div ng-repeat="b in todayBookings" class="flex items-center justify-between p-4 border border-border-light hover:border-accent/20 rounded-xl hover:bg-bg-alt/25 transition-all duration-300 stagger-inner" ng-class="{'border-accent/40 bg-bg-alt/20': b._updated}">
                                    <div>
                                        <p class="font-sans font-semibold text-ink text-[0.875rem] mb-1">{{b.amenity_name}}</p>
                                        <p class="font-sans text-[0.8125rem] text-ink-secondary/70 font-light">{{b.guest_name}}</p>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <p class="font-mono text-[0.75rem] font-semibold text-accent mb-2 bg-bg-alt/60 px-3 py-1 rounded-full">{{formatTime(b.check_in_time)}} — {{formatTime(b.check_out_time)}}</p>
                                        <span class="font-mono text-[0.5625rem] px-2.5 py-1 font-medium uppercase tracking-wider rounded" ng-class="getBookingStatusClass(b.status)">{{b.status.replace('_', ' ')}}</span>
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
        var app = angular.module('adminApp', ['apiHttp']);

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
                return $http.get('../api/dashboard').then(function(res) {
                    var d = res.data.data || {};
                    $scope.stats = {
                        properties: d.properties,
                        maintenance: d.maintenance,
                        amenities: d.amenities,
                        inquiries: d.inquiries,
                        rentals: d.rentals,
                        kpi: d.kpi || {}
                    };
                    $scope.recentMaintenance = d.kpirecent_maintenance_requests || [];
                    $scope.todayBookings = d.today_bookings || [];
                    $scope._checkNotifications(d.kpi);
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
                sseSource = new EventSource('../api/sse');
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
                $http.get('../api/dashboard').then(function(res) {
                    var d = res.data.data || {};
                    var oldPending = $scope.stats.maintenance ? $scope.stats.maintenance.pending_count : 0;
                    var oldInProgress = $scope.stats.maintenance ? $scope.stats.maintenance.in_progress_count : 0;
                    var oldOverdue = $scope.stats.kpi ? $scope.stats.kpi.overdue_count : 0;
                    $scope.stats = {
                        properties: d.properties,
                        maintenance: d.maintenance,
                        amenities: d.amenities,
                        inquiries: d.inquiries,
                        rentals: d.rentals,
                        kpi: d.kpi || {}
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
                    let newMaint = d.kpirecent_maintenance_requests || [];
                    if($scope.recentMaintenance.length !== newMaint.length || (newMaint[0] && $scope.recentMaintenance[0] && (newMaint[0].id !== $scope.recentMaintenance[0].id || newMaint[0].status !== $scope.recentMaintenance[0].status))) {
                        $scope.recentMaintenance = newMaint;
                    }
                    let newBookings = d.today_bookings || [];
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
