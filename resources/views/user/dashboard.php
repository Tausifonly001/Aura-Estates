<?php
require_once __DIR__ . '/../../../src/config/auth.php';
Auth::startSession();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" ng-app="tenantApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal — Aura Estates</title>
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
        .tab-btn { position: relative; transition: all 0.3s ease; }
        .tab-btn::after { content: ''; position: absolute; bottom: -2px; left: 50%; right: 50%; height: 2px; background: #3a322c; transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1); border-radius: 1px; }
        .tab-btn.active::after { left: 0; right: 0; }
        .tab-btn:not(.active):hover::after { left: 30%; right: 30%; background: #d6d2c8; }
        .list-item { opacity: 0; }
        .inner-item { }
        .stat-card { position: relative; overflow: hidden; }
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 2px;
            background: #3a322c;
            transform: scaleX(0);
            transform-origin: left;
        }
        .stat-card.animated::after { animation: statBarIn 0.6s ease forwards; }
        @keyframes statBarIn { to { transform: scaleX(1); } }
        .tab-content { will-change: transform, opacity; }
        .pulse-ring { animation: pulseRing 2s ease infinite; }
        @keyframes pulseRing {
            0% { transform: scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
    </style>
</head>
<body ng-controller="TenantController" ng-cloak>

    <!-- Navigation -->
    <nav class="bg-surface border-b border-border-light relative z-10 h-16">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 h-full flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="../index.html" class="flex items-center gap-3 font-sans font-medium text-[0.875rem] tracking-[0.15em] uppercase text-ink no-underline">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-accent text-bg text-[0.625rem] font-semibold leading-none">A</span>
                    AURA
                </a>
                <span class="text-border">|</span>
                <span class="font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Tenant Portal</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="font-sans text-[0.875rem] text-ink-secondary">Welcome, <strong class="text-ink font-medium">{{user.name}}</strong></span>
                <a href="logout.php" class="inline-flex items-center gap-2 font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary hover:text-ink transition-colors px-4 py-2 border border-border rounded-full no-underline">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8 relative">
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

        <div ng-show="!pageLoading">
            <!-- Tabs -->
            <div class="border-b border-border-light mb-8 overflow-x-auto">
                <nav class="flex gap-8 min-w-max">
                    <button ng-click="switchTab('overview')" ng-class="{'text-ink active': currentTab == 'overview', 'text-ink-secondary opacity-65 hover:text-ink': currentTab != 'overview'}" class="tab-btn pb-3 px-1 font-sans font-medium text-[0.875rem] transition-all whitespace-nowrap bg-none border-none cursor-pointer">Overview</button>
                    <button ng-click="switchTab('browse')" ng-class="{'text-ink active': currentTab == 'browse', 'text-ink-secondary opacity-65 hover:text-ink': currentTab != 'browse'}" class="tab-btn pb-3 px-1 font-sans font-medium text-[0.875rem] transition-all whitespace-nowrap bg-none border-none cursor-pointer">Browse</button>
                    <button ng-click="switchTab('rentals')" ng-class="{'text-ink active': currentTab == 'rentals', 'text-ink-secondary opacity-65 hover:text-ink': currentTab != 'rentals'}" class="tab-btn pb-3 px-1 font-sans font-medium text-[0.875rem] transition-all whitespace-nowrap bg-none border-none cursor-pointer">Rentals</button>
                    <button ng-click="switchTab('maintenance')" ng-class="{'text-ink active': currentTab == 'maintenance', 'text-ink-secondary opacity-65 hover:text-ink': currentTab != 'maintenance'}" class="tab-btn pb-3 px-1 font-sans font-medium text-[0.875rem] transition-all whitespace-nowrap bg-none border-none cursor-pointer">Maintenance</button>
                    <button ng-click="switchTab('amenities')" ng-class="{'text-ink active': currentTab == 'amenities', 'text-ink-secondary opacity-65 hover:text-ink': currentTab != 'amenities'}" class="tab-btn pb-3 px-1 font-sans font-medium text-[0.875rem] transition-all whitespace-nowrap bg-none border-none cursor-pointer">Amenities</button>
                </nav>
            </div>

            <div class="tab-container relative min-h-[400px]">
                <!-- Overview -->
                <div ng-show="currentTab == 'overview'" class="tab-content w-full" id="tab-overview">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="bg-surface border border-border-light p-6 list-item stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Active Rentals</p>
                                    <p class="font-sans font-medium text-[2rem] text-ink mt-2">{{counts.rentals}}</p>
                                </div>
                                <div class="w-12 h-12 border border-border flex items-center justify-center">
                                    <svg class="w-5 h-5 text-ink-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="bg-surface border border-border-light p-6 list-item stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Open Requests</p>
                                    <p class="font-sans font-medium text-[2rem] text-ink mt-2">{{counts.maintenance}}</p>
                                </div>
                                <div class="w-12 h-12 border border-border flex items-center justify-center">
                                    <svg class="w-5 h-5 text-ink-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="bg-surface border border-border-light p-6 list-item stat-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65">Upcoming Bookings</p>
                                    <p class="font-sans font-medium text-[2rem] text-ink mt-2">{{counts.bookings}}</p>
                                </div>
                                <div class="w-12 h-12 border border-border flex items-center justify-center">
                                    <svg class="w-5 h-5 text-ink-secondary opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-surface border border-border-light p-6 list-item">
                        <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-6">My Properties</h3>
                        <div class="flex flex-col gap-3">
                            <p ng-if="!activeRentals || activeRentals.length == 0" class="text-ink-secondary text-[0.875rem] font-sans">No active rentals. <a href="#" ng-click="switchTab('browse'); $event.preventDefault();" class="text-ink hover:underline font-medium">Browse properties</a></p>
                            <div ng-repeat="r in activeRentals" class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 border border-border-light hover:border-border transition inner-item">
                                <div class="mb-2 md:mb-0">
                                    <p class="font-sans font-medium text-ink text-[1rem]">{{r.property_title}}</p>
                                    <p class="font-sans text-[0.875rem] text-ink-secondary">{{r.location}} · {{r.property_type}} · <span class="text-ink font-medium">{{r.monthly_rent | currency:"$":0}}/mo</span></p>
                                </div>
                                <span class="font-mono text-[0.5625rem] px-3 py-1.5 bg-ink/10 text-ink font-medium uppercase tracking-[0.02em]">Active</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Browse -->
                <div ng-show="currentTab == 'browse'" class="tab-content absolute top-0 w-full" id="tab-browse">
                    <div class="bg-surface border border-border-light p-6">
                        <h2 class="font-sans font-medium text-[1.125rem] text-ink mb-6">Available Properties</h2>
                        <p ng-if="!availableProperties || availableProperties.length == 0" class="text-ink-secondary text-[0.875rem] font-sans">No properties available at this time.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div ng-repeat="p in availableProperties" class="bg-bg border border-border-light overflow-hidden list-item group">
                                <div class="h-48 relative overflow-hidden">
                                    <img ng-if="p.main_image" ng-src="{{p.main_image}}" class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105">
                                    <div ng-if="!p.main_image" class="bg-bg-alt w-full h-full flex items-center justify-center text-muted text-4xl">+</div>
                                </div>
                                <div class="p-5">
                                    <h4 class="font-sans font-medium text-ink text-[1rem]">{{p.title}}</h4>
                                    <p class="font-mono text-[0.625rem] text-ink-secondary mt-1 uppercase tracking-[0.02em]">{{p.location}} · {{p.property_type}}</p>
                                    <div class="flex gap-6 mt-3 pt-3 border-t border-border-light">
                                        <div><span class="block font-sans font-medium text-ink">{{p.bedrooms}}</span><span class="font-mono text-[0.5rem] text-ink-secondary uppercase tracking-[0.02em]">Beds</span></div>
                                        <div><span class="block font-sans font-medium text-ink">{{p.bathrooms}}</span><span class="font-mono text-[0.5rem] text-ink-secondary uppercase tracking-[0.02em]">Baths</span></div>
                                        <div><span class="block font-sans font-medium text-ink">{{p.area_sqft}}</span><span class="font-mono text-[0.5rem] text-ink-secondary uppercase tracking-[0.02em]">SqFt</span></div>
                                    </div>
                                    <p class="font-sans font-medium text-[1.125rem] text-ink mt-4">{{p.price | currency:"$":0}}/mo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rentals -->
                <div ng-show="currentTab == 'rentals'" class="tab-content absolute top-0 w-full" id="tab-rentals">
                    <div class="bg-surface border border-border-light p-6">
                        <h2 class="font-sans font-medium text-[1.125rem] text-ink mb-6">Rental History</h2>
                        <p ng-if="!rentals || rentals.length == 0" class="text-ink-secondary text-[0.875rem] font-sans">No rental history found.</p>
                        <div class="flex flex-col gap-3">
                            <div ng-repeat="r in rentals" class="flex flex-col md:flex-row items-start md:items-center justify-between p-5 border border-border-light hover:border-border transition list-item">
                                <div>
                                    <p class="font-sans font-medium text-ink text-[1rem]">{{r.property_title}}</p>
                                    <p class="font-sans text-[0.875rem] text-ink-secondary mb-1">{{r.location}} · <span class="font-medium text-ink">{{r.monthly_rent | currency:"$":0}}/mo</span></p>
                                    <p class="font-mono text-[0.625rem] text-muted tabular-nums">{{r.start_date}} — {{r.end_date || 'Ongoing'}}</p>
                                </div>
                                <span class="mt-3 md:mt-0 font-mono text-[0.5625rem] px-4 py-1.5 font-medium uppercase tracking-[0.02em]" ng-class="{'bg-ink/10 text-ink': r.status=='active', 'bg-border text-ink-secondary': r.status=='terminated', 'bg-bg text-muted': r.status=='expired'}">{{r.status}}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance -->
                <div ng-show="currentTab == 'maintenance'" class="tab-content absolute top-0 w-full" id="tab-maintenance">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-surface border border-border-light p-6 list-item">
                            <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-6 pb-3 border-b border-border-light">Submit Request</h3>
                            <form ng-submit="submitMaint()" class="flex flex-col gap-4">
                                <div>
                                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Property</label>
                                    <select ng-model="maintForm.property_id" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 outline-none focus:border-accent appearance-none">
                                        <option value="">Select property</option>
                                        <option ng-repeat="r in activeRentals" value="{{r.property_id}}">{{r.property_title}}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Subject</label>
                                    <input type="text" ng-model="maintForm.subject" placeholder="Brief description" required class="w-full font-sans text-[0.875rem] text-ink bg-transparent border border-border px-4 py-3 outline-none focus:border-accent placeholder:text-muted">
                                </div>
                                <div>
                                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Details</label>
                                    <textarea ng-model="maintForm.description" rows="3" placeholder="More context..." required class="w-full font-sans text-[0.875rem] text-ink bg-transparent border border-border px-4 py-3 outline-none focus:border-accent resize-none placeholder:text-muted"></textarea>
                                </div>
                                <div>
                                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Priority</label>
                                    <select ng-model="maintForm.priority" class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 outline-none focus:border-accent appearance-none">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-3 font-mono text-[0.6875rem] tracking-[0.02em] uppercase text-bg bg-accent px-5 py-3 rounded-full hover:bg-accent-hover transition-colors" ng-disabled="isSubmittingMaint">
                                    {{ isSubmittingMaint ? 'Submitting...' : 'Submit' }}
                                </button>
                            </form>
                            <div ng-if="maintMessage" class="mt-4 p-4 font-mono text-[0.75rem]" ng-class="maintMessageClass">{{maintMessage}}</div>
                        </div>
                        <div class="bg-surface border border-border-light p-6 list-item flex flex-col">
                            <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-6 pb-3 border-b border-border-light flex justify-between items-center">
                                My Requests
                                <span ng-if="isPolling" class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent/60"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-accent"></span>
                                </span>
                            </h3>
                            <p ng-if="!myMaintenance || myMaintenance.length == 0" class="text-ink-secondary text-[0.875rem] font-sans text-center my-auto">No requests yet.</p>
                            <div class="flex flex-col gap-3 overflow-y-auto max-h-[500px] pr-2">
                                <div ng-repeat="m in myMaintenance" class="p-4 border border-border-light hover:border-border transition inner-item" ng-class="{'border-border': m._updated}">
                                    <div class="flex items-start justify-between mb-2">
                                        <p class="font-sans font-medium text-ink text-[0.875rem] w-3/5 truncate">{{m.subject || (m.issue_description | limitTo:30)}}</p>
                                        <div class="flex gap-2 w-2/5 justify-end">
                                            <span class="font-mono text-[0.5rem] px-2 py-1 font-medium uppercase tracking-[0.02em]" ng-class="getPriorityClass(m.priority)">{{m.priority}}</span>
                                            <span class="font-mono text-[0.5rem] px-2 py-1 font-medium uppercase tracking-[0.02em]" ng-class="getStatusClass(m.status)">{{m.status.replace('_',' ')}}</span>
                                        </div>
                                    </div>
                                    <p class="font-sans text-[0.875rem] text-ink-secondary mb-2">{{m.issue_description}}</p>
                                    <p class="font-mono text-[0.625rem] text-muted tabular-nums">{{m.created_at}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Amenities -->
                <div ng-show="currentTab == 'amenities'" class="tab-content absolute top-0 w-full" id="tab-amenities">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-surface border border-border-light p-6 list-item">
                            <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-6 pb-3 border-b border-border-light">Book Amenity</h3>
                            <form ng-submit="submitBooking()" class="flex flex-col gap-4">
                                <div>
                                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Amenity</label>
                                    <select ng-model="bookForm.amenity_id" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 outline-none focus:border-accent appearance-none" ng-change="onAmenityChange()">
                                        <option value="">Select amenity</option>
                                        <option ng-repeat="a in amenities | filter:{is_active:1}" value="{{a.id}}">{{a.name}} ({{a.capacity}} ppl · {{a.location || 'N/A'}})</option>
                                    </select>
                                    <div ng-if="bookForm.amenity_id && bookForm.booking_date && bookForm.start_time && bookForm.end_time" class="mt-2 flex items-center gap-2">
                                        <span class="font-mono text-[0.5rem] text-ink-secondary opacity-65">AVAILABILITY</span>
                                        <span ng-if="selectedAmenityCapacity > 0" class="font-mono text-[0.5625rem] text-success">{{selectedAmenityCapacity}} slot(s) available</span>
                                        <span ng-if="selectedAmenityCapacity == 0" class="font-mono text-[0.5625rem] text-danger">Fully booked</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">Date</label>
                                    <input type="date" ng-model="bookForm.booking_date" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 outline-none focus:border-accent">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">From</label>
                                        <input type="time" ng-model="bookForm.start_time" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 outline-none focus:border-accent">
                                    </div>
                                    <div>
                                        <label class="block font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary opacity-65 mb-2">To</label>
                                        <input type="time" ng-model="bookForm.end_time" required class="w-full font-sans text-[0.875rem] text-ink bg-surface border border-border px-4 py-3 outline-none focus:border-accent">
                                    </div>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-3 font-mono text-[0.6875rem] tracking-[0.02em] uppercase text-bg bg-accent px-5 py-3 rounded-full hover:bg-accent-hover transition-colors" ng-disabled="isSubmittingBooking">
                                    {{ isSubmittingBooking ? 'Reserving...' : 'Book Now' }}
                                </button>
                            </form>
                            <div ng-if="bookingMessage" class="mt-4 p-4 font-mono text-[0.75rem]" ng-class="bookingMessageClass">{{bookingMessage}}</div>
                        </div>
                        <div class="bg-surface border border-border-light p-6 list-item flex flex-col">
                            <h3 class="font-sans font-medium text-[1.125rem] text-ink mb-6 pb-3 border-b border-border-light flex justify-between items-center">
                                My Bookings
                                <span ng-if="isPolling" class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent/60"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-accent"></span>
                                </span>
                            </h3>
                            <p ng-if="!myBookings || myBookings.length == 0" class="text-ink-secondary text-[0.875rem] font-sans text-center my-auto">No bookings yet.</p>
                            <div class="flex flex-col gap-3 overflow-y-auto max-h-[500px] pr-2">
                                <div ng-repeat="b in myBookings" class="p-4 border border-border-light hover:border-border transition inner-item" ng-class="{'border-border': b._updated}">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="font-sans font-medium text-ink">{{b.amenity_name}}</p>
                                        <span class="font-mono text-[0.5625rem] px-3 py-1 font-medium uppercase tracking-[0.02em]" ng-class="getBookingStatusClass(b.status)">{{b.status.replace('_',' ')}}</span>
                                    </div>
                                    <div class="flex items-center gap-2 font-sans text-[0.875rem] text-ink-secondary">{{b.booking_date}}</div>
                                    <div class="flex items-center gap-2 font-sans text-[0.875rem] text-ink-secondary mt-1">{{formatTime(b.check_in_time)}} — {{formatTime(b.check_out_time)}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var app = angular.module('tenantApp', []);

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
                    $scope.selectedAmenityCapacity = res.data.available || 0;
                }, function() { $scope.selectedAmenityCapacity = null; });
            });

            $scope.onAmenityChange = function() {
                var d = $scope.bookForm.booking_date;
                if (d instanceof Date) { d = d.getFullYear() + '-' + ('0'+(d.getMonth()+1)).slice(-2) + '-' + ('0'+d.getDate()).slice(-2); }
                if ($scope.bookForm.amenity_id && d) {
                    $http.get(API.amenities + '?action=check_capacity&id=' + $scope.bookForm.amenity_id + '&date=' + encodeURIComponent(d)).then(function(res) {
                        $scope.selectedAmenityCapacity = res.data.available || 0;
                    });
                }
            };

            $scope.isPolling = false;

            const API = { auth: '../api/auth.php', user: '../api/user_data.php', maintenance: '../api/maintenance.php', amenities: '../api/amenities.php', bookings: '../api/amenity_bookings.php' };

            $scope.formatTime = function(tStr) {
                if(!tStr) return '';
                let parts = tStr.split(':');
                if(parts.length < 2) return tStr;
                let h = parseInt(parts[0]), m = parts[1], ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12; h = h ? h : 12;
                return h + ':' + m + ' ' + ampm;
            };

            $http.get(API.auth + '?action=me').then(function(res) {
                if(!res.data.authenticated) { $window.location.href = 'login.php'; return; }
                $scope.user = res.data.user;
                $scope.loadAllData().then(function() {
                    $scope.pageLoading = false;
                    $scope.animateTabEntry();
                    $scope.startSSE();
                });
            }, function() { $window.location.href = 'login.php'; });

            $scope.loadAllData = function() {
                return $q.all([
                    $http.get(API.user + '?type=rentals'), $http.get(API.user + '?type=maintenance'),
                    $http.get(API.user + '?type=bookings'), $http.get(API.user + '?type=available_properties'), $http.get(API.amenities)
                ]).then(function(responses) {
                    $scope.rentals = responses[0].data.records || [];
                    $scope.activeRentals = $scope.rentals.filter(r => r.status === 'active');
                    $scope.counts.rentals = $scope.activeRentals.length;
                    $scope.myMaintenance = responses[1].data.records || [];
                    $scope.counts.maintenance = $scope.myMaintenance.filter(r => r.status !== 'completed').length;
                    $scope.myBookings = responses[2].data.records || [];
                    $scope.counts.bookings = $scope.myBookings.filter(b => b.status !== 'cancelled' && b.status !== 'completed').length;
                    $scope.availableProperties = responses[3].data.records || [];
                    $scope.amenities = responses[4].data.records || [];
                });
            };

            var sseSource = null;
            $scope.startSSE = function() {
                $scope.isPolling = true;
                sseSource = new EventSource('../api/sse.php');
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
                    let newMaint = res.data.records || [];
                    if($scope.myMaintenance.length !== newMaint.length || (newMaint[0] && $scope.myMaintenance[0] && newMaint[0].status !== $scope.myMaintenance[0].status)) {
                        $scope.myMaintenance = newMaint;
                        $scope.counts.maintenance = $scope.myMaintenance.filter(r => r.status !== 'completed').length;
                    }
                });
                $http.get(API.user + '?type=bookings').then(function(res) {
                    let newBookings = res.data.records || [];
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
                        $scope.myMaintenance = res2.data.records || [];
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
                        $scope.myBookings = res2.data.records || [];
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
