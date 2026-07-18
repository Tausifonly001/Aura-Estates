var app = angular.module('auraApp', ['apiHttp']);

app.controller('PropertyController', function($scope, $http, $timeout) {
    $scope.properties = [];
    $scope.loading = true;
    $scope.error = false;
    $scope.selectedProperty = null;
    $scope.currentUser = null;
    $scope.mobileNavOpen = false;
    $scope.activePropertyScene = null;
    $scope.heroSceneReady = false;
    $scope.buildingSceneReady = false;

    $http.get('api/auth?action=me').then(function(res) {
        var data = res.data && res.data.data ? res.data.data : {};
        if (data.user) {
            $scope.currentUser = data.user;
        }
    });

    $scope.filters = {
        maxPrice: 10000000,
        propertyType: '',
        status: ''
    };
    $scope.searchQuery = '';

    $scope.inquiry = {};
    $scope.successMessage = '';
    $scope.errorMessage = '';

    $scope.maint = {};
    $scope.maintenanceRequests = [];
    $scope.maintenanceSuccess = '';
    $scope.maintenanceError = '';

    $scope.amenityBookings = [];
    $scope.bookingMsg = '';

    $scope.fallbackProperties = [
        { id: 1, title: 'The Sapphire Penthouse', description: 'A stunning penthouse with panoramic ocean views and private elevator access.', price: 5000000, location: 'Beverly Hills, CA', property_type: 'Penthouse', bedrooms: 4, bathrooms: 5, area_sqft: 4500, main_image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 2, title: 'Onyx Villa', description: 'Modern architectural masterpiece nestled in the hills with infinity pool.', price: 3500000, location: 'Malibu, CA', property_type: 'Villa', bedrooms: 5, bathrooms: 6, area_sqft: 6000, main_image: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 3, title: 'Emerald Estate', description: 'Classic luxury estate with sprawling gardens and tennis court.', price: 8200000, location: 'Hamptons, NY', property_type: 'Estate', bedrooms: 7, bathrooms: 8, area_sqft: 12000, main_image: 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 4, title: 'Golden Loft', description: 'Industrial chic loft in the heart of the city with floor-to-ceiling windows.', price: 1200000, location: 'Tribeca, NY', property_type: 'Loft', bedrooms: 2, bathrooms: 2, area_sqft: 2500, main_image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 5, title: 'Crystal Waters Estate', description: 'A breathtaking waterfront estate with private dock, infinity pool, and panoramic ocean views.', price: 7200000, location: 'Miami Beach, FL', property_type: 'Estate', bedrooms: 6, bathrooms: 7, area_sqft: 8500, main_image: 'https://images.unsplash.com/photo-1600607687644-c7171b42498b?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 6, title: 'The Ivory Tower', description: 'Minimalist penthouse occupying the entire top floor with 360-degree city views.', price: 4500000, location: 'Manhattan, NY', property_type: 'Penthouse', bedrooms: 3, bathrooms: 4, area_sqft: 3800, main_image: 'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 7, title: 'Villa del Sol', description: 'Mediterranean-inspired villa surrounded by olive groves with a private vineyard.', price: 2800000, location: 'Santa Barbara, CA', property_type: 'Villa', bedrooms: 4, bathrooms: 5, area_sqft: 5500, main_image: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 8, title: 'The Industrial Loft', description: 'Converted warehouse with exposed brick walls, 20-foot ceilings, and curated interiors.', price: 950000, location: 'Brooklyn, NY', property_type: 'Loft', bedrooms: 2, bathrooms: 2, area_sqft: 2200, main_image: 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 9, title: 'Azure Cliffs Residence', description: 'Sculptural concrete and glass masterpiece cantilevered over the Pacific Ocean.', price: 9800000, location: 'Big Sur, CA', property_type: 'Estate', bedrooms: 5, bathrooms: 6, area_sqft: 7200, main_image: 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 10, title: 'The Metropolitan', description: 'Sleek modern penthouse in the financial district with smart home automation.', price: 3200000, location: 'San Francisco, CA', property_type: 'Penthouse', bedrooms: 3, bathrooms: 3, area_sqft: 3100, main_image: 'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 11, title: 'Amalfi Cliff Residence', description: 'Perched above the Pacific, this glass-and-stone villa features cantilevered terraces over the ocean.', price: 9750000, location: 'Pacific Palisades, CA', property_type: 'Villa', bedrooms: 6, bathrooms: 7, area_sqft: 7800, main_image: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 12, title: 'The Monolith Tower Penthouse', description: 'A triple-height penthouse crowning a 60-storey tower with 360-degree glazing.', price: 14500000, location: 'Manhattan, NY', property_type: 'Penthouse', bedrooms: 5, bathrooms: 6, area_sqft: 8200, main_image: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 13, title: 'Maison du Vignoble', description: 'A 19th-century French estate reimagined with steel-and-glass extensions.', price: 6400000, location: 'Napa Valley, CA', property_type: 'Estate', bedrooms: 8, bathrooms: 9, area_sqft: 14500, main_image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 14, title: 'Glacier Point Lodge', description: 'A timber-and-glass mountain retreat inspired by Scandinavian stave churches.', price: 4200000, location: 'Aspen, CO', property_type: 'Lodge', bedrooms: 5, bathrooms: 5, area_sqft: 5600, main_image: 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 15, title: 'Dune House', description: 'An earth-sheltered residence built into coastal dunes with a living green roof.', price: 5800000, location: 'Montauk, NY', property_type: 'House', bedrooms: 4, bathrooms: 4, area_sqft: 4200, main_image: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 16, title: 'The Glass Pavilion', description: 'A Miesian glass box reinterpreted for the desert with polished concrete floors.', price: 7200000, location: 'Scottsdale, AZ', property_type: 'Villa', bedrooms: 4, bathrooms: 5, area_sqft: 6100, main_image: 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 17, title: 'Harbour View Tower', description: 'A 42nd-floor residence in a sculptural waterfront tower with wraparound terrace.', price: 8900000, location: 'Sydney, NSW', property_type: 'Penthouse', bedrooms: 3, bathrooms: 4, area_sqft: 3800, main_image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 18, title: 'Palazzo Nero', description: 'A Venetian palazzo restored with museum-grade precision and private canal mooring.', price: 11500000, location: 'Venice, IT', property_type: 'Estate', bedrooms: 7, bathrooms: 8, area_sqft: 11000, main_image: 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 19, title: 'Cedar Bridge Farmhouse', description: 'A timber-frame farmhouse on 12 acres with a geothermal-heated indoor pool.', price: 3650000, location: 'Hudson Valley, NY', property_type: 'Farmhouse', bedrooms: 5, bathrooms: 4, area_sqft: 5200, main_image: 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 20, title: 'The Vertex', description: 'A 28-storey sculptural tower with rotating floor plates and sky gardens.', price: 6800000, location: 'Miami Beach, FL', property_type: 'Penthouse', bedrooms: 3, bathrooms: 4, area_sqft: 3600, main_image: 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 21, title: 'Amanoi Retreat', description: 'A resort-inspired residence nestled in hillside jungle with private plunge pools.', price: 4500000, location: 'Tulum, MX', property_type: 'Villa', bedrooms: 4, bathrooms: 5, area_sqft: 4800, main_image: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 22, title: 'The Foundry', description: 'A converted ironworks with triple-height spaces and raw steel trusses.', price: 5100000, location: 'Brooklyn, NY', property_type: 'Loft', bedrooms: 3, bathrooms: 3, area_sqft: 4500, main_image: 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 23, title: 'Villa Aether', description: 'A cantilevered concrete and timber villa hovering above a private cove.', price: 12800000, location: 'Santorini, GR', property_type: 'Villa', bedrooms: 6, bathrooms: 6, area_sqft: 7200, main_image: 'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 24, title: 'Maison Terre', description: 'A rammed-earth compound in the hills above Malibu with reflecting pool.', price: 7500000, location: 'Malibu, CA', property_type: 'Compound', bedrooms: 6, bathrooms: 7, area_sqft: 8500, main_image: 'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 25, title: 'The Observatory', description: 'A cylindrical glass residence with a rotating living room platform.', price: 5400000, location: 'Joshua Tree, CA', property_type: 'House', bedrooms: 3, bathrooms: 3, area_sqft: 3200, main_image: 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 26, title: 'Schwarzwald Chalet', description: 'A Black Forest-inspired timber chalet with heated infinity pool.', price: 3900000, location: 'Whistler, BC', property_type: 'Chalet', bedrooms: 6, bathrooms: 5, area_sqft: 6400, main_image: 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 27, title: 'Skybridge Residences', description: 'Two towers connected by a sky bridge with shared infinity pool on 40th floor.', price: 8200000, location: 'Dubai, UAE', property_type: 'Penthouse', bedrooms: 4, bathrooms: 5, area_sqft: 5100, main_image: 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 28, title: 'The Copper House', description: 'A weathered copper-clad residence that evolves with the seasons.', price: 4800000, location: 'Portland, OR', property_type: 'House', bedrooms: 4, bathrooms: 4, area_sqft: 4100, main_image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 29, title: 'Marina Bay Grand', description: 'A waterfront duplex penthouse with private marina berth.', price: 10200000, location: 'Singapore', property_type: 'Penthouse', bedrooms: 4, bathrooms: 5, area_sqft: 5800, main_image: 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 30, title: 'The Lighthouse', description: 'A converted Victorian lighthouse with glass-walled upper floor.', price: 2800000, location: 'Big Sur, CA', property_type: 'House', bedrooms: 3, bathrooms: 3, area_sqft: 2800, main_image: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1000', is_available: 1 },
        { id: 31, title: 'Orchid Court', description: 'A heritage-listed Georgian townhouse with subterranean spa.', price: 9100000, location: 'London, UK', property_type: 'Townhouse', bedrooms: 6, bathrooms: 5, area_sqft: 6800, main_image: 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000', is_available: 1 }
    ];

    $scope.fetchProperties = function() {
        $http.get('api/properties')
        .then(function(response) {
            if (response.data && response.data.records && response.data.records.length > 0) {
                $scope.properties = response.data.records;
            } else {
                $scope.properties = $scope.fallbackProperties;
            }
            $scope.loading = false;
            $timeout(function() {
                AuraAnimations.initPage();
            }, 300);
        }, function(error) {
            $scope.properties = $scope.fallbackProperties;
            $scope.loading = false;
            $timeout(function() {
                AuraAnimations.initPage();
            }, 300);
        });
    };

    $scope.fetchProperties();

    $scope.openDetail = function(property) {
        $scope.selectedProperty = property;
        document.body.style.overflow = 'hidden';
        if (window.lenis) lenis.stop();
        $timeout(function() {
            if (window.AuraThree && $scope.selectedProperty) {
                var container = document.getElementById('property-viewer-3d');
                if (container && !$scope.activePropertyScene) {
                    $scope.activePropertyScene = AuraThree.createPropertyViewer(container, {
                        backgroundColor: 0xf2efe9,
                        autoRotate: true
                    });
                }
            }
        }, 100);
    };

    $scope.closeDetail = function() {
        if ($scope.activePropertyScene) {
            $scope.activePropertyScene.dispose();
            $scope.activePropertyScene = null;
        }
        $scope.selectedProperty = null;
        document.body.style.overflow = '';
        if (window.lenis) lenis.start();
    };

    $scope.submitInquiry = function(propertyId) {
        if (!$scope.inquiry.name || !$scope.inquiry.email) return;

        var data = {
            property_id: propertyId,
            name: $scope.inquiry.name,
            email: $scope.inquiry.email,
            message: $scope.inquiry.message || 'Interested in leasing this property'
        };

        $http.post('api/inquiry', data)
        .then(function(response) {
            $scope.successMessage = "Thank you! We will contact you shortly.";
            $scope.inquiry = {};
            $scope.selectedProperty = null;
            $timeout(function() { $scope.successMessage = ''; }, 3000);
        }, function(error) {
            $scope.errorMessage = (error && error.data && error.data.message) ? error.data.message : "Failed to send inquiry. Please try again.";
            $timeout(function() { $scope.errorMessage = ''; }, 3000);
        });
    };

    $scope.submitMaintenanceRequest = function() {
        if (!$scope.maint.property_id || !$scope.maint.description) return;
        if (!$scope.maint.name || !$scope.maint.email) {
            $scope.maintenanceError = "Please provide your name and email.";
            $timeout(function() { $scope.maintenanceError = ''; }, 4000);
            return;
        }

        var data = {
            property_id: $scope.maint.property_id,
            priority: $scope.maint.priority || 'medium',
            description: $scope.maint.description,
            tenant_name: $scope.maint.name,
            tenant_email: $scope.maint.email
        };

        $http.post('api/maintenance', data)
        .then(function(response) {
            $scope.maintenanceSuccess = "Request submitted successfully!";
            $scope.maintenanceError = '';
            $scope.maint = {};
            $timeout(function() { $scope.maintenanceSuccess = ''; }, 2000);
        }, function(error) {
            $scope.maintenanceError = (error && error.data && error.data.message) ? error.data.message : "Failed to submit request.";
            $timeout(function() { $scope.maintenanceError = ''; }, 4000);
        });
    };

    $scope.bookAmenity = function(amenityName, timeSlot) {
        $scope.bookingMsg = "Booking " + amenityName + " — " + timeSlot + "...";
        var data = {
            amenity_id: 1,
            guest_name: 'Guest',
            booking_date: new Date().toISOString().split('T')[0],
            check_in_time: timeSlot.split('-')[0].trim(),
            check_out_time: timeSlot.split('-')[1].trim()
        };

        $http.post('api/amenity_bookings', data)
        .then(function(response) {
            $scope.bookingMsg = amenityName + " booked successfully!";
            $timeout(function() { $scope.bookingMsg = ''; }, 3000);
        }, function(error) {
            $scope.bookingMsg = "Booking failed. Please sign in.";
            $timeout(function() { $scope.bookingMsg = ''; }, 3000);
        });
    };

    $scope.propertyFilter = function(property) {
        var priceMatch = parseFloat(property.price) <= $scope.filters.maxPrice;
        var typeMatch = $scope.filters.propertyType === '' || property.property_type === $scope.filters.propertyType;
        var searchMatch = !$scope.searchQuery ||
            property.title.toLowerCase().includes($scope.searchQuery.toLowerCase()) ||
            property.location.toLowerCase().includes($scope.searchQuery.toLowerCase());
        return priceMatch && typeMatch && searchMatch;
    };
});

app.directive('fallbackSrc', function() {
    return {
        link: function(scope, element, attrs) {
            element.bind('error', function() {
                if (element.attr('src') !== attrs.fallbackSrc) {
                    element.attr('src', attrs.fallbackSrc);
                }
            });
        }
    }
});

app.directive('heroParticles', function($timeout) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            $timeout(function() {
                if (window.AuraThree) {
                    scope.heroScene = AuraThree.createParticleField(element[0], {
                        autoRotateSpeed: 0.12
                    });
                    scope.heroSceneReady = true;
                    element[0].style.opacity = '1';
                }
            }, 200);

            scope.$on('$destroy', function() {
                if (window.AuraThree && scope.heroScene) {
                    scope.heroScene.dispose();
                }
            });
        }
    };
});

app.directive('buildingSkyline', function($timeout) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            $timeout(function() {
                if (window.AuraThree) {
                    scope.buildingScene = AuraThree.createBuildingSkyline(element[0], {
                        autoRotateSpeed: 0.1,
                        buildingCount: 35
                    });
                    scope.buildingSceneReady = true;
                    element[0].style.opacity = '1';
                }
            }, 400);

            scope.$on('$destroy', function() {
                if (window.AuraThree && scope.buildingScene) {
                    scope.buildingScene.dispose();
                }
            });
        }
    };
});

app.directive('magneticHover', function() {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            if (window.AuraAnimations) {
                AuraAnimations.magneticHover(element[0]);
            }
        }
    };
});

document.addEventListener("DOMContentLoaded", function() {
    var script = document.createElement('script');
    script.src = 'https://unpkg.com/lenis@1.1.13/dist/lenis.min.js';
    script.onload = function() {
        var lenis = new Lenis({
            duration: 1.3,
            easing: function(t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); },
            orientation: 'vertical',
            gestureOrientation: 'vertical',
            smoothWheel: true,
            wheelMultiplier: 0.8,
            touchMultiplier: 1.5
        });

        window.lenis = lenis;

        lenis.on('scroll', function(e) {
            if (window.ScrollTrigger) ScrollTrigger.update();
        });

        if (window.gsap) {
            gsap.ticker.add(function(time) {
                lenis.raf(time * 1000);
            });
            gsap.ticker.lagSmoothing(0);
        }
    };
    document.head.appendChild(script);
});
