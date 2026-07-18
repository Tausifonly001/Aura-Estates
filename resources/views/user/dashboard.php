<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::startSession();
if(!Auth::isAuthenticated()) {
    header("Location: " . Auth::getBasePrefix() . "/login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" ng-app="tenantApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal — Aura Estates</title>
    <link rel="icon" type="image/svg+xml" href="../../../favicon.svg">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script>window.AURA_API_BASE = '../api/';</script>
    <script src="../resources/js/api-http.js"></script>
    <style>
        [ng-cloak] { display: none !important; }
        body { font-family: 'DM Sans', sans-serif; background-color: #e8e5db; color: #1c1b18; -webkit-font-smoothing: antialiased; }
        .list-item { opacity: 0; }
        .inner-item { }
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
        .stat-card.animated::after { animation: statBarIn 0.6s ease forwards; }
        @keyframes statBarIn { to { transform: scaleX(1); } }
        .tab-content { will-change: transform, opacity; }
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
        /* Custom scrollbar */
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
        .neo-sidebar {
            width: 16rem !important;
            background: #ffffff !important;
            border-right: 1px solid var(--color-border) !important;
            display: flex !important;
            flex-direction: column !important;
            flex-shrink: 0 !important;
        }
    </style>
</head>
<body ng-controller="TenantController" ng-cloak>

    <!-- Loading -->
    <div ng-if="pageLoading" class="fixed inset-0 z-[60] flex justify-center items-center bg-bg/95 backdrop-blur-sm">
        <div class="text-center">
            <div class="font-sans font-medium text-[0.875rem] tracking-[0.15em] uppercase text-ink/20">AURA</div>
            <div class="mt-4 flex gap-2 justify-center">
                <div class="w-2 h-2 rounded-full bg-accent animate-bounce" style="animation-delay:0s"></div>
                <div class="w-2 h-2 rounded-full bg-accent/60 animate-bounce" style="animation-delay:0.15s"></div>
                <div class="w-2 h-2 rounded-full bg-accent/30 animate-bounce" style="animation-delay:0.3s"></div>
            </div>
        </div>
    </div>

    <div class="flex h-screen overflow-hidden" ng-show="!pageLoading">
        <!-- User Left Sidebar -->
        <aside class="neo-sidebar w-64 flex flex-col flex-shrink-0 bg-surface border-r border-border-light">
            <!-- Brand Logo Header -->
            <div class="p-6 border-b border-border-light bg-surface/50 backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 bg-accent text-bg text-[0.75rem] font-semibold tracking-wider font-sans leading-none">A</span>
                    <span class="font-serif italic font-light text-[1.4rem] tracking-wide text-ink leading-none">Aura</span>
                    <span class="font-sans text-[0.55rem] font-medium uppercase tracking-[0.2em] text-ink-secondary/60 mt-1">Estates</span>
                </div>
                <div class="text-[0.5rem] uppercase tracking-[0.2em] text-ink-secondary/50 font-sans font-bold mt-2">Tenant Portal</div>
            </div>

            <!-- Navigation Tabs -->
            <nav class="flex-1 py-6 px-3 space-y-1 overflow-y-auto">
                <button ng-click="switchTab('overview')" ng-class="currentTab == 'overview' ? 'border-accent text-ink font-medium bg-bg-alt/60' : 'border-transparent text-ink-secondary hover:text-ink hover:bg-bg-alt/30'" class="w-full flex items-center space-x-3.5 py-2.5 px-4 transition-all text-left bg-transparent border-none border-l-2 cursor-pointer outline-none border-solid">
                    <i class="fas fa-th-large w-4 text-center text-xs opacity-75"></i>
                    <span class="text-[0.8125rem] font-sans">Overview</span>
                </button>
                <button ng-click="switchTab('browse')" ng-class="currentTab == 'browse' ? 'border-accent text-ink font-medium bg-bg-alt/60' : 'border-transparent text-ink-secondary hover:text-ink hover:bg-bg-alt/30'" class="w-full flex items-center space-x-3.5 py-2.5 px-4 transition-all text-left bg-transparent border-none border-l-2 cursor-pointer outline-none border-solid">
                    <i class="fas fa-search w-4 text-center text-xs opacity-75"></i>
                    <span class="text-[0.8125rem] font-sans">Browse</span>
                </button>
                <button ng-click="switchTab('rentals')" ng-class="currentTab == 'rentals' ? 'border-accent text-ink font-medium bg-bg-alt/60' : 'border-transparent text-ink-secondary hover:text-ink hover:bg-bg-alt/30'" class="w-full flex items-center space-x-3.5 py-2.5 px-4 transition-all text-left bg-transparent border-none border-l-2 cursor-pointer outline-none border-solid">
                    <i class="fas fa-key w-4 text-center text-xs opacity-75"></i>
                    <span class="text-[0.8125rem] font-sans">Rentals</span>
                </button>
                <button ng-click="switchTab('maintenance')" ng-class="currentTab == 'maintenance' ? 'border-accent text-ink font-medium bg-bg-alt/60' : 'border-transparent text-ink-secondary hover:text-ink hover:bg-bg-alt/30'" class="w-full flex items-center space-x-3.5 py-2.5 px-4 transition-all text-left bg-transparent border-none border-l-2 cursor-pointer outline-none border-solid">
                    <i class="fas fa-tools w-4 text-center text-xs opacity-75"></i>
                    <span class="text-[0.8125rem] font-sans">Maintenance</span>
                </button>
                <button ng-click="switchTab('amenities')" ng-class="currentTab == 'amenities' ? 'border-accent text-ink font-medium bg-bg-alt/60' : 'border-transparent text-ink-secondary hover:text-ink hover:bg-bg-alt/30'" class="w-full flex items-center space-x-3.5 py-2.5 px-4 transition-all text-left bg-transparent border-none border-l-2 cursor-pointer outline-none border-solid">
                    <i class="fas fa-concierge-bell w-4 text-center text-xs opacity-75"></i>
                    <span class="text-[0.8125rem] font-sans">Amenities</span>
                </button>
            </nav>

            <!-- User Profile Footer -->
            <div class="p-4 border-t border-border-light bg-surface/50">
                <div class="flex items-center space-x-3 mb-3 px-2">
                    <div class="w-8 h-8 rounded-full bg-accent text-bg flex items-center justify-center font-medium font-sans text-xs">
                        {{user.name ? (user.name | limitTo:1).toUpperCase() : 'U'}}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[0.8125rem] text-ink font-medium font-sans truncate">
                            {{user.name}}
                        </div>
                        <div class="text-[0.5625rem] text-ink-secondary/60 uppercase tracking-[0.1em] font-mono mt-0.5">
                            Resident / Tenant
                        </div>
                    </div>
                </div>
                <a href="<?php echo Auth::getBasePrefix(); ?>/user/logout" class="flex items-center justify-center space-x-2 py-2 border border-border hover:border-ink rounded-full text-ink-secondary hover:text-ink transition-colors text-[0.625rem] font-mono uppercase tracking-[0.1em] no-underline">
                    <i class="fas fa-sign-out-alt text-[10px]"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-auto bg-bg">
            <div class="p-8 max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-end mb-6 stagger-item">
                    <div>
                        <h1 class="font-serif font-light italic text-[2.5rem] text-ink leading-tight">Welcome, <span class="font-sans not-italic font-normal">{{user.name}}</span></h1>
                        <p class="font-sans text-[0.8125rem] text-ink-secondary/70 mt-1.5 font-light">Tenant Console · <?php echo date('F d, Y'); ?></p>
                    </div>
                    <div class="flex items-center gap-3 bg-surface border border-border-light px-4 py-2 rounded-full shadow-sm hover:shadow-md transition-all duration-300">
                        <span ng-if="isPolling" class="flex items-center gap-2 font-mono text-[0.625rem] text-ink-secondary font-medium uppercase tracking-wider">
                            <span class="pulse-dot"></span>
                            Portal Connected
                        </span>
                        <span class="font-mono text-[0.5625rem] tracking-[0.05em] uppercase text-muted/80 bg-bg-alt px-2 py-0.5 rounded-full">Automated Sync</span>
                    </div>
                </div>
                <div class="h-px bg-border-light mb-8 stagger-item"></div>

                <!-- Tab Container -->
                <div class="tab-container relative min-h-[400px]">
                    <!-- Overview -->
                    <div ng-show="currentTab == 'overview'" class="tab-content w-full" id="tab-overview">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                            <!-- Stat 1 -->
                            <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 list-item stat-card group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-mono text-[0.5625rem] tracking-[0.1em] uppercase text-ink-secondary/65">Active Leases</p>
                                        <p class="font-serif italic font-light text-[2.5rem] text-ink mt-2 leading-none">{{counts.rentals}}</p>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-bg-alt/80 flex items-center justify-center text-accent/80 group-hover:bg-accent group-hover:text-bg transition-colors duration-300">
                                        <i class="fas fa-home text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- Stat 2 -->
                            <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 list-item stat-card group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-mono text-[0.5625rem] tracking-[0.1em] uppercase text-ink-secondary/65">Open Requests</p>
                                        <p class="font-serif italic font-light text-[2.5rem] text-ink mt-2 leading-none">{{counts.maintenance}}</p>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-bg-alt/80 flex items-center justify-center text-accent/80 group-hover:bg-accent group-hover:text-bg transition-colors duration-300">
                                        <i class="fas fa-wrench text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <!-- Stat 3 -->
                            <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 list-item stat-card group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-mono text-[0.5625rem] tracking-[0.1em] uppercase text-ink-secondary/65">Upcoming Bookings</p>
                                        <p class="font-serif italic font-light text-[2.5rem] text-ink mt-2 leading-none">{{counts.bookings}}</p>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-bg-alt/80 flex items-center justify-center text-accent/80 group-hover:bg-accent group-hover:text-bg transition-colors duration-300">
                                        <i class="fas fa-calendar-check text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- My Properties list card -->
                        <div class="bg-surface border border-border-light rounded-2xl p-6 shadow-sm list-item">
                            <h3 class="font-serif font-light italic text-[1.25rem] text-ink mb-6">My Leased Properties</h3>
                            <div class="flex flex-col gap-4">
                                <p ng-if="!activeRentals || activeRentals.length == 0" class="text-ink-secondary/60 text-[0.875rem] font-sans font-light">
                                    No active rentals. <a href="#" ng-click="switchTab('browse'); $event.preventDefault();" class="text-accent hover:underline font-semibold">Browse available properties</a>
                                </p>
                                <div ng-repeat="r in activeRentals" class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 border border-border-light hover:border-accent/20 rounded-xl hover:bg-bg-alt/20 transition-all duration-300 inner-item">
                                    <div class="mb-2 md:mb-0">
                                        <p class="font-sans font-semibold text-ink text-[1rem]">{{r.property_title}}</p>
                                        <p class="font-sans text-[0.8125rem] text-ink-secondary/70 font-light mt-1">
                                            {{r.location}} · {{r.property_type}} · 
                                            <span class="text-ink font-semibold bg-bg-alt/70 px-2 py-0.5 rounded-md font-mono text-xs">{{r.monthly_rent | currency:"$":0}}/mo</span>
                                        </p>
                                    </div>
                                    <span class="font-mono text-[0.5625rem] px-3 py-1 bg-success/15 text-success font-semibold uppercase tracking-wider rounded">Active Lease</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Browse -->
                    <div ng-show="currentTab == 'browse'" class="tab-content absolute top-0 w-full" id="tab-browse">
                        <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm">
                            <h2 class="font-serif font-light italic text-[1.25rem] text-ink mb-6">Available Properties</h2>
                            <p ng-if="!availableProperties || availableProperties.length == 0" class="text-ink-secondary/60 text-[0.875rem] font-sans font-light">No properties available at this time.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div ng-repeat="p in availableProperties" class="bg-bg-alt/25 border border-border-light rounded-xl overflow-hidden list-item group hover:border-accent/20 hover:shadow-md transition-all duration-300 flex flex-col">
                                    <div class="h-48 relative overflow-hidden bg-bg">
                                        <img ng-if="p.main_image" ng-src="{{p.main_image}}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                        <div ng-if="!p.main_image" class="bg-bg-alt/60 w-full h-full flex items-center justify-center text-muted/50 text-4xl font-light">+</div>
                                    </div>
                                    <div class="p-5 flex-1 flex flex-col justify-between">
                                        <div>
                                            <h4 class="font-sans font-semibold text-ink text-[1rem] group-hover:text-accent transition-colors">{{p.title}}</h4>
                                            <p class="font-mono text-[0.625rem] text-ink-secondary/70 mt-1.5 uppercase tracking-wider flex items-center gap-1">
                                                <i class="fas fa-map-marker-alt opacity-70"></i> {{p.location}} · {{p.property_type}}
                                            </p>
                                            <div class="flex gap-6 mt-4 pt-4 border-t border-border-light/80 text-center">
                                                <div class="flex-1">
                                                    <span class="block font-sans font-semibold text-ink text-[0.9375rem]">{{p.bedrooms}}</span>
                                                    <span class="font-mono text-[0.5rem] text-ink-secondary/60 uppercase tracking-wider">Beds</span>
                                                </div>
                                                <div class="w-px bg-border-light/80"></div>
                                                <div class="flex-1">
                                                    <span class="block font-sans font-semibold text-ink text-[0.9375rem]">{{p.bedrooms}}</span>
                                                    <span class="font-mono text-[0.5rem] text-ink-secondary/60 uppercase tracking-wider">Baths</span>
                                                </div>
                                                <div class="w-px bg-border-light/80"></div>
                                                <div class="flex-1">
                                                    <span class="block font-sans font-semibold text-ink text-[0.9375rem]">{{p.area_sqft}}</span>
                                                    <span class="font-mono text-[0.5rem] text-ink-secondary/60 uppercase tracking-wider">SqFt</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-5 pt-3 border-t border-border-light/60 flex items-center justify-between">
                                            <span class="font-serif italic font-light text-[1.25rem] text-ink leading-none">{{p.price | currency:"$":0}}<span class="text-xs font-sans not-italic text-ink-secondary/60 font-light">/mo</span></span>
                                            <span class="font-mono text-[0.5625rem] px-2 py-1 bg-accent/10 text-accent font-semibold uppercase tracking-wider rounded">Available</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rentals -->
                    <div ng-show="currentTab == 'rentals'" class="tab-content absolute top-0 w-full" id="tab-rentals">
                        <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm">
                            <h2 class="font-serif font-light italic text-[1.25rem] text-ink mb-6">Rental Lease History</h2>
                            <p ng-if="!rentals || rentals.length == 0" class="text-ink-secondary/60 text-[0.875rem] font-sans font-light">No lease history found.</p>
                            <div class="flex flex-col gap-4">
                                <div ng-repeat="r in rentals" class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 border border-border-light hover:border-accent/20 hover:bg-bg-alt/20 rounded-xl transition-all duration-300 list-item">
                                    <div>
                                        <p class="font-sans font-semibold text-ink text-[1rem]">{{r.property_title}}</p>
                                        <p class="font-sans text-[0.8125rem] text-ink-secondary/70 font-light mt-1">{{r.location}} · <span class="font-semibold text-ink bg-bg-alt/60 px-2 py-0.5 rounded font-mono text-xs">{{r.monthly_rent | currency:"$":0}}/mo</span></p>
                                        <p class="font-mono text-[0.625rem] text-muted/80 mt-2 flex items-center gap-1.5 font-light">
                                            <i class="far fa-calendar-alt"></i> Lease Duration: {{r.start_date}} — {{r.end_date || 'Ongoing'}}
                                        </p>
                                    </div>
                                    <span class="mt-3 md:mt-0 font-mono text-[0.5625rem] px-3 py-1 font-semibold uppercase tracking-wider rounded" ng-class="{'bg-success/15 text-success': r.status=='active', 'bg-border text-ink-secondary/70': r.status=='terminated', 'bg-bg-alt text-muted': r.status=='expired'}">{{r.status}}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance -->
                    <div ng-show="currentTab == 'maintenance'" class="tab-content absolute top-0 w-full" id="tab-maintenance">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Submit Maintenance -->
                            <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm list-item">
                                <h3 class="font-serif font-light italic text-[1.25rem] text-ink mb-6 pb-3 border-b border-border-light/80">Submit Service Request</h3>
                                <form ng-submit="submitMaint()" class="flex flex-col gap-4">
                                    <div>
                                        <label class="block font-mono text-[0.5625rem] tracking-[0.08em] uppercase text-ink-secondary/70 mb-2">Select Property</label>
                                        <select ng-model="maintForm.property_id" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 rounded-lg outline-none focus:border-accent appearance-none">
                                            <option value="">Choose property...</option>
                                            <option ng-repeat="r in activeRentals" value="{{r.property_id}}">{{r.property_title}}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block font-mono text-[0.5625rem] tracking-[0.08em] uppercase text-ink-secondary/70 mb-2">Subject</label>
                                        <input type="text" ng-model="maintForm.subject" placeholder="Brief issue description..." required class="w-full font-sans text-[0.875rem] text-ink bg-transparent border border-border px-4 py-3 rounded-lg outline-none focus:border-accent placeholder:text-muted/60">
                                    </div>
                                    <div>
                                        <label class="block font-mono text-[0.5625rem] tracking-[0.08em] uppercase text-ink-secondary/70 mb-2">Detailed Context</label>
                                        <textarea ng-model="maintForm.description" rows="3" placeholder="Please describe the issue in detail so we can dispatch the right team..." required class="w-full font-sans text-[0.875rem] text-ink bg-transparent border border-border px-4 py-3 rounded-lg outline-none focus:border-accent resize-none placeholder:text-muted/60"></textarea>
                                    </div>
                                    <div>
                                        <label class="block font-mono text-[0.5625rem] tracking-[0.08em] uppercase text-ink-secondary/70 mb-2">Priority Level</label>
                                        <select ng-model="maintForm.priority" class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 rounded-lg outline-none focus:border-accent appearance-none">
                                            <option value="low">Low (Non-urgent request)</option>
                                            <option value="medium">Medium (Standard request)</option>
                                            <option value="high">High (Urgent response needed)</option>
                                            <option value="urgent">Urgent (Emergency issue)</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-3 font-mono text-[0.6875rem] tracking-[0.15em] uppercase text-bg bg-accent px-5 py-3 rounded-full hover:bg-accent-hover transition-colors font-semibold shadow-sm mt-2" ng-disabled="isSubmittingMaint">
                                        <i class="fas fa-paper-plane text-[9px]"></i>
                                        {{ isSubmittingMaint ? 'Submitting Request...' : 'Submit Request' }}
                                    </button>
                                </form>
                                <div ng-if="maintMessage" class="mt-4 p-4 font-mono text-[0.75rem] rounded-lg shadow-sm" ng-class="maintMessageClass">{{maintMessage}}</div>
                            </div>

                            <!-- My Requests List -->
                            <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm list-item flex flex-col">
                                <h3 class="font-serif font-light italic text-[1.25rem] text-ink mb-6 pb-3 border-b border-border-light/80 flex justify-between items-center">
                                    My Requests
                                    <span ng-if="isPolling" class="flex h-2.5 w-2.5 relative">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent/40"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-accent"></span>
                                    </span>
                                </h3>
                                <p ng-if="!myMaintenance || myMaintenance.length == 0" class="text-ink-secondary/60 text-[0.875rem] font-sans font-light text-center my-auto">No requests submitted yet.</p>
                                <div class="flex flex-col gap-4 overflow-y-auto max-h-[500px] pr-2 custom-scrollbar">
                                    <div ng-repeat="m in myMaintenance" class="p-4 border border-border-light hover:border-accent/20 hover:bg-bg-alt/20 rounded-xl transition-all duration-300 inner-item" ng-class="{'border-accent bg-bg-alt/30': m._updated}">
                                        <div class="flex items-start justify-between mb-2 gap-4">
                                            <p class="font-sans font-semibold text-ink text-[0.875rem] w-3/5 truncate">{{m.subject || (m.issue_description | limitTo:30)}}</p>
                                            <div class="flex gap-2 w-2/5 justify-end flex-wrap">
                                                <span class="font-mono text-[0.5rem] px-2 py-0.5 font-semibold uppercase tracking-wider rounded" ng-class="getPriorityClass(m.priority)">{{m.priority}}</span>
                                                <span class="font-mono text-[0.5rem] px-2 py-0.5 font-semibold uppercase tracking-wider rounded" ng-class="getStatusClass(m.status)">{{m.status.replace('_',' ')}}</span>
                                            </div>
                                        </div>
                                        <p class="font-sans text-[0.8125rem] text-ink-secondary/80 font-light leading-relaxed mb-3">{{m.issue_description}}</p>
                                        
                                        <!-- Stepper -->
                                        <div class="mt-4 flex items-center justify-between w-full text-[0.625rem] font-mono text-ink-secondary/50 border-t border-border-light/60 pt-3">
                                            <div class="flex items-center gap-1" ng-class="{'text-ink font-semibold': m.status === 'pending' || m.status === 'in_progress' || m.status === 'completed'}">
                                                <span class="w-1.5 h-1.5 rounded-full" ng-class="m.status === 'pending' || m.status === 'in_progress' || m.status === 'completed' ? 'bg-accent' : 'bg-border'"></span>
                                                <span>Received</span>
                                            </div>
                                            <div class="h-0.5 flex-1 bg-border-light mx-2"></div>
                                            <div class="flex items-center gap-1" ng-class="{'text-ink font-semibold': m.status === 'in_progress' || m.status === 'completed'}">
                                                <span class="w-1.5 h-1.5 rounded-full" ng-class="m.status === 'in_progress' || m.status === 'completed' ? 'bg-accent' : 'bg-border'"></span>
                                                <span>In Progress</span>
                                            </div>
                                            <div class="h-0.5 flex-1 bg-border-light mx-2"></div>
                                            <div class="flex items-center gap-1" ng-class="{'text-success font-semibold': m.status === 'completed'}">
                                                <span class="w-1.5 h-1.5 rounded-full" ng-class="m.status === 'completed' ? 'bg-success' : 'bg-border'"></span>
                                                <span>Completed</span>
                                            </div>
                                        </div>
                                        
                                        <p class="font-mono text-[0.5625rem] text-muted/80 mt-3.5 flex items-center gap-1">
                                            <i class="far fa-clock"></i> Submitted: {{m.created_at}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div ng-show="currentTab == 'amenities'" class="tab-content absolute top-0 w-full" id="tab-amenities">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Book Amenity -->
                            <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm list-item">
                                <h3 class="font-serif font-light italic text-[1.25rem] text-ink mb-6 pb-3 border-b border-border-light/80">Book Amenity</h3>
                                <form ng-submit="submitBooking()" class="flex flex-col gap-4">
                                    <div>
                                        <label class="block font-mono text-[0.5625rem] tracking-[0.08em] uppercase text-ink-secondary/70 mb-2">Amenity Selection</label>
                                        <select ng-model="bookForm.amenity_id" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 rounded-lg outline-none focus:border-accent appearance-none" ng-change="onAmenityChange()">
                                            <option value="">Choose amenity...</option>
                                            <option ng-repeat="a in amenities | filter:{is_active:1}" value="{{a.id}}">{{a.name}} (Capacity: {{a.capacity}} · {{a.location || 'N/A'}})</option>
                                        </select>
                                        <div ng-if="bookForm.amenity_id && bookForm.booking_date && bookForm.start_time && bookForm.end_time" class="mt-2.5 flex items-center gap-2">
                                            <span class="font-mono text-[0.5rem] tracking-wider text-ink-secondary/50 uppercase">AVAILABILITY STATUS</span>
                                            <span ng-if="selectedAmenityCapacity > 0" class="font-mono text-[0.5625rem] px-2 py-0.5 bg-success/10 text-success rounded font-semibold">{{selectedAmenityCapacity}} slot(s) available</span>
                                            <span ng-if="selectedAmenityCapacity == 0" class="font-mono text-[0.5625rem] px-2 py-0.5 bg-danger/10 text-danger rounded font-semibold">Fully booked</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block font-mono text-[0.5625rem] tracking-[0.08em] uppercase text-ink-secondary/70 mb-2">Booking Date</label>
                                        <input type="date" ng-model="bookForm.booking_date" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 rounded-lg outline-none focus:border-accent">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block font-mono text-[0.5625rem] tracking-[0.08em] uppercase text-ink-secondary/70 mb-2">Start Time</label>
                                            <input type="time" ng-model="bookForm.start_time" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 rounded-lg outline-none focus:border-accent">
                                        </div>
                                        <div>
                                            <label class="block font-mono text-[0.5625rem] tracking-[0.08em] uppercase text-ink-secondary/70 mb-2">End Time</label>
                                            <input type="time" ng-model="bookForm.end_time" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 rounded-lg outline-none focus:border-accent">
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-3 font-mono text-[0.6875rem] tracking-[0.15em] uppercase text-bg bg-accent px-5 py-3 rounded-full hover:bg-accent-hover transition-colors font-semibold shadow-sm mt-2" ng-disabled="isSubmittingBooking">
                                        <i class="fas fa-calendar-check text-[10px]"></i>
                                        {{ isSubmittingBooking ? 'Reserving Slots...' : 'Confirm Booking' }}
                                    </button>
                                </form>
                                <div ng-if="bookingMessage" class="mt-4 p-4 font-mono text-[0.75rem] rounded-lg shadow-sm" ng-class="bookingMessageClass">{{bookingMessage}}</div>
                            </div>

                            <!-- My Bookings List -->
                            <div class="bg-surface border border-border-light p-6 rounded-2xl shadow-sm list-item flex flex-col">
                                <h3 class="font-serif font-light italic text-[1.25rem] text-ink mb-6 pb-3 border-b border-border-light/80 flex justify-between items-center">
                                    My Bookings
                                    <span ng-if="isPolling" class="flex h-2.5 w-2.5 relative">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent/40"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-accent"></span>
                                    </span>
                                </h3>
                                <p ng-if="!myBookings || myBookings.length == 0" class="text-ink-secondary/60 text-[0.875rem] font-sans font-light text-center my-auto">No bookings reserved yet.</p>
                                <div class="flex flex-col gap-4 overflow-y-auto max-h-[500px] pr-2 custom-scrollbar">
                                    <div ng-repeat="b in myBookings" class="p-4 border border-border-light hover:border-accent/20 hover:bg-bg-alt/20 rounded-xl transition-all duration-300 inner-item" ng-class="{'border-accent bg-bg-alt/30': b._updated}">
                                        <div class="flex items-center justify-between mb-3.5">
                                            <p class="font-sans font-semibold text-ink text-[0.875rem]">{{b.amenity_name}}</p>
                                            <span class="font-mono text-[0.5625rem] px-2.5 py-1 font-semibold uppercase tracking-wider rounded" ng-class="getBookingStatusClass(b.status)">{{b.status.replace('_',' ')}}</span>
                                        </div>
                                        <div class="flex items-center gap-2 font-sans text-[0.8125rem] text-ink-secondary/80 font-light">
                                            <i class="far fa-calendar-alt text-xs opacity-60"></i> Booking Date: {{b.booking_date}}
                                        </div>
                                        <div class="flex items-center gap-2 font-sans text-[0.8125rem] text-ink-secondary/80 font-light mt-1.5">
                                            <i class="far fa-clock text-xs opacity-60"></i> Reserved Time: {{formatTime(b.check_in_time)}} — {{formatTime(b.check_out_time)}}
                                        </div>
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
        var app = angular.module('tenantApp', ['apiHttp']);

        app.controller('TenantController', function($scope, $http, $timeout, $window, $q) {
            $scope.user = null;
            $scope.pageLoading = true;
            $scope.currentTab = 'overview';

            $scope.rentals = [];
            $scope.activeRentals = [];
            $scope.myMaintenance = [];
            $scope.myBookings = [];
            $scope.availableProperties = [];
            $scope.amenities = [];

            $scope.counts = { rentals: 0, maintenance: 0, bookings: 0 };

            $scope.maintForm = { priority: 'medium' };
            $scope.bookForm = {};
            $scope.selectedAmenityCapacity = null;

            $scope.$watchGroup(['bookForm.amenity_id', 'bookForm.booking_date', 'bookForm.start_time', 'bookForm.end_time'], function() {
                if (!$scope.bookForm.amenity_id || !$scope.bookForm.booking_date || !$scope.bookForm.start_time || !$scope.bookForm.end_time) {
                    $scope.selectedAmenityCapacity = null;
                    return;
                }
                var d = $scope.bookForm.booking_date;
                if (d instanceof Date) { d = d.getFullYear() + '-' + ('0'+(d.getMonth()+1)).slice(-2) + '-' + ('0'+d.getDate()).slice(-2); }
                $http.get(API.amenities + '?action=check_capacity&id=' + $scope.bookForm.amenity_id + '&date=' + encodeURIComponent(d)).then(function(res) {
                    $scope.selectedAmenityCapacity = (res.data.data && res.data.data.available) || 0;
                }, function() { $scope.selectedAmenityCapacity = null; });
            });

            $scope.onAmenityChange = function() {
                var d = $scope.bookForm.booking_date;
                if (d instanceof Date) { d = d.getFullYear() + '-' + ('0'+(d.getMonth()+1)).slice(-2) + '-' + ('0'+d.getDate()).slice(-2); }
                if ($scope.bookForm.amenity_id && d) {
                    $http.get(API.amenities + '?action=check_capacity&id=' + $scope.bookForm.amenity_id + '&date=' + encodeURIComponent(d)).then(function(res) {
                        $scope.selectedAmenityCapacity = (res.data.data && res.data.data.available) || 0;
                    });
                }
            };

            $scope.isPolling = false;

            const API = { auth: '../api/auth', user: '../api/user_data', maintenance: '../api/maintenance', amenities: '../api/amenities', bookings: '../api/amenity_bookings' };

            $scope.formatTime = function(tStr) {
                if(!tStr) return '';
                let parts = tStr.split(':');
                if(parts.length < 2) return tStr;
                let h = parseInt(parts[0]), m = parts[1], ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12; h = h ? h : 12;
                return h + ':' + m + ' ' + ampm;
            };

            $http.get(API.auth + '?action=me').then(function(res) {
                if(!res.data.data || !res.data.data.user) { $window.location.href = '<?php echo Auth::getBasePrefix(); ?>/login'; return; }
                $scope.user = res.data.data.user;
                $scope.loadAllData().then(function() {
                    $scope.pageLoading = false;
                    $scope.animateTabEntry();
                    $scope.startSSE();
                });
                }, function() { $window.location.href = '<?php echo Auth::getBasePrefix(); ?>/login'; });

            $scope.loadAllData = function() {
                return $q.all([
                    $http.get(API.user + '?type=rentals'), $http.get(API.user + '?type=maintenance'),
                    $http.get(API.user + '?type=bookings'), $http.get(API.user + '?type=available_properties'), $http.get(API.amenities)
                ]).then(function(responses) {
                    $scope.rentals = (responses[0].data.data && responses[0].data.data.records) || [];
                    $scope.activeRentals = $scope.rentals.filter(r => r.status === 'active');
                    $scope.counts.rentals = $scope.activeRentals.length;
                    $scope.myMaintenance = (responses[1].data.data && responses[1].data.data.records) || [];
                    $scope.counts.maintenance = $scope.myMaintenance.filter(r => r.status !== 'completed').length;
                    $scope.myBookings = (responses[2].data.data && responses[2].data.data.records) || [];
                    $scope.counts.bookings = $scope.myBookings.filter(b => b.status !== 'cancelled' && b.status !== 'completed').length;
                    $scope.availableProperties = (responses[3].data.data && responses[3].data.data.records) || [];
                    $scope.amenities = (responses[4].data.data && responses[4].data.data.records) || [];
                });
            };

            var sseSource = null;
            $scope.startSSE = function() {
                $scope.isPolling = true;
                sseSource = new EventSource('../api/sse');
                sseSource.addEventListener('new_maintenance', function(e) {
                    $scope.$apply(function() { $scope._refreshTenantData(); });
                });
                sseSource.addEventListener('new_booking', function(e) {
                    $scope.$apply(function() { $scope._refreshTenantData(); });
                });
                sseSource.onerror = function() {
                    setTimeout(function() { $scope.$apply($scope.startSSE); }, 3000);
                };
            };

            $scope._refreshTenantData = function() {
                $http.get(API.user + '?type=maintenance').then(function(res) {
                    let newMaint = (res.data.data && res.data.data.records) || [];
                    if($scope.myMaintenance.length !== newMaint.length || (newMaint[0] && $scope.myMaintenance[0] && newMaint[0].status !== $scope.myMaintenance[0].status)) {
                        $scope.myMaintenance = newMaint;
                        $scope.counts.maintenance = $scope.myMaintenance.filter(r => r.status !== 'completed').length;
                    }
                });
                $http.get(API.user + '?type=bookings').then(function(res) {
                    let newBookings = (res.data.data && res.data.data.records) || [];
                    if($scope.myBookings.length !== newBookings.length || (newBookings[0] && $scope.myBookings[0] && newBookings[0].status !== $scope.myBookings[0].status)) {
                        $scope.myBookings = newBookings;
                        $scope.counts.bookings = $scope.myBookings.filter(b => b.status !== 'cancelled' && b.status !== 'completed').length;
                    }
                });
            };

            $scope.$on('$destroy', function() { if (sseSource) sseSource.close(); });

            $scope.animateTabEntry = function() {
                $timeout(function() {
                    var tl = gsap.timeline();
                    var tabId = "#tab-" + $scope.currentTab;

                    var listItems = document.querySelectorAll(tabId + " .list-item");
                    if (listItems.length) {
                        tl.fromTo(listItems,
                            { opacity: 0, y: 30 },
                            { opacity: 1, y: 0, duration: 0.7, stagger: 0.08, ease: "power4.out", clearProps: "transform" }
                        );
                    }

                    var innerItems = document.querySelectorAll(tabId + " .inner-item");
                    if (innerItems.length) {
                        tl.fromTo(innerItems,
                            { opacity: 0, x: -10 },
                            { opacity: 1, x: 0, duration: 0.5, stagger: 0.05, ease: "power3.out", clearProps: "transform" }
                        , "-=0.2");
                    }

                    tl.call(function() {
                        document.querySelectorAll(tabId + ' .stat-card').forEach(function(el) {
                            el.classList.add('animated');
                        });
                    }, null, "-=0.5");
                }, 50);
            };

            $scope.switchTab = function(tab) {
                if ($scope.currentTab === tab) return;
                gsap.to("#tab-" + $scope.currentTab, {
                    opacity: 0,
                    y: -12,
                    duration: 0.2,
                    ease: "power2.in",
                    onComplete: function() {
                        $scope.$apply(function() { $scope.currentTab = tab; });
                        gsap.set("#tab-" + tab, { opacity: 1, y: 0 });
                        $scope.animateTabEntry();
                    }
                });
            };

            $scope.submitMaint = function() {
                $scope.isSubmittingMaint = true;
                var payload = { user_id: $scope.user.id, tenant_name: $scope.user.name, tenant_email: $scope.user.email, property_id: $scope.maintForm.property_id, subject: $scope.maintForm.subject, description: $scope.maintForm.description, priority: $scope.maintForm.priority };
                $http.post(API.maintenance, payload).then(function(res) {
                    $scope.maintMessageClass = "bg-success/10 border border-success/20 text-success";
                    $scope.maintMessage = res.data.message;
                    $scope.maintForm = { priority: 'medium' };
                    $http.get(API.user + '?type=maintenance').then(function(res2) {
                        $scope.myMaintenance = (res2.data.data && res2.data.data.records) || [];
                        $scope.counts.maintenance = $scope.myMaintenance.filter(function(r) { return r.status !== 'completed'; }).length;
                        $scope.animateTabEntry();
                    });
                }, function(err) {
                    $scope.maintMessageClass = "bg-danger/10 border border-danger/20 text-danger";
                    $scope.maintMessage = err.data.message || "Failed to submit.";
                }).finally(function() { $scope.isSubmittingMaint = false; $timeout(function() { $scope.maintMessage = ''; }, 4000); });
            };

            $scope.submitBooking = function() {
                $scope.isSubmittingBooking = true;
                var formattedDate = '';
                if ($scope.bookForm.booking_date instanceof Date) { var d = $scope.bookForm.booking_date; formattedDate = d.getFullYear() + '-' + ('0'+(d.getMonth()+1)).slice(-2) + '-' + ('0'+d.getDate()).slice(-2); }
                else { formattedDate = $scope.bookForm.booking_date; }
                var payload = { user_id: $scope.user.id, guest_name: $scope.user.name, amenity_id: $scope.bookForm.amenity_id, booking_date: formattedDate, check_in_time: $scope.bookForm.start_time instanceof Date ? $scope.bookForm.start_time.toTimeString().split(' ')[0] : $scope.bookForm.start_time, check_out_time: $scope.bookForm.end_time instanceof Date ? $scope.bookForm.end_time.toTimeString().split(' ')[0] : $scope.bookForm.end_time };
                $http.post(API.bookings, payload).then(function(res) {
                    $scope.bookingMessageClass = "bg-success/10 border border-success/20 text-success";
                    $scope.bookingMessage = res.data.message;
                    $scope.bookForm = {};
                    $http.get(API.user + '?type=bookings').then(function(res2) {
                        $scope.myBookings = (res2.data.data && res2.data.data.records) || [];
                        $scope.counts.bookings = $scope.myBookings.filter(function(b) { return b.status !== 'cancelled' && b.status !== 'completed'; }).length;
                        $scope.animateTabEntry();
                    });
                }, function(err) {
                    $scope.bookingMessageClass = "bg-danger/10 border border-danger/20 text-danger";
                    $scope.bookingMessage = err.data.message || "Failed to book.";
                }).finally(function() { $scope.isSubmittingBooking = false; $timeout(function() { $scope.bookingMessage = ''; }, 5000); });
            };

            $scope.getPriorityClass = function(p) {
                var map = { low: 'text-muted bg-bg', medium: 'text-ink bg-ink/10', high: 'text-warning bg-warning/10', urgent: 'text-danger bg-danger/10' };
                return map[p] || 'text-muted bg-bg';
            };
            $scope.getStatusClass = function(s) {
                var map = { pending: 'text-warning bg-warning/10', in_progress: 'text-ink bg-ink/10', completed: 'text-success bg-success/10' };
                return map[s] || 'text-muted bg-bg';
            };
            $scope.getBookingStatusClass = function(s) {
                var map = { confirmed: 'text-warning bg-warning/10', checked_in: 'text-success bg-success/10', cancelled: 'text-danger bg-danger/10', completed: 'text-muted bg-bg' };
                return map[s] || 'text-muted bg-bg';
            };
        });
    </script>
</body>
</html>
