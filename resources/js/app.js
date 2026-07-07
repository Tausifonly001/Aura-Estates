var app = angular.module('auraApp', []);

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
        if (res.data.authenticated) {
            $scope.currentUser = res.data.user;
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
        {
            id: 1,
            title: 'The Sapphire Penthouse',
            description: 'A stunning penthouse with panoramic ocean views and private elevator access.',
            price: 5000000,
            location: 'Beverly Hills, CA',
            property_type: 'Penthouse',
            bedrooms: 4,
            bathrooms: 5,
            area_sqft: 4500,
            main_image: 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000',
            status: 'Available'
        },
        {
            id: 2,
            title: 'Onyx Villa',
            description: 'Modern architectural masterpiece nestled in the hills with infinity pool.',
            price: 3500000,
            location: 'Malibu, CA',
            property_type: 'Villa',
            bedrooms: 5,
            bathrooms: 6,
            area_sqft: 6000,
            main_image: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000',
            status: 'Available'
        },
        {
            id: 3,
            title: 'Emerald Estate',
            description: 'Classic luxury estate with sprawling gardens and tennis court.',
            price: 8200000,
            location: 'Hamptons, NY',
            property_type: 'Villa',
            bedrooms: 7,
            bathrooms: 8,
            area_sqft: 12000,
            main_image: 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000',
            status: 'Available'
        },
        {
            id: 4,
            title: 'Golden Loft',
            description: 'Industrial chic loft in the heart of the city with floor-to-ceiling windows.',
            price: 1200000,
            location: 'Tribeca, NY',
            property_type: 'Apartment',
            bedrooms: 2,
            bathrooms: 2,
            area_sqft: 2500,
            main_image: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000',
            status: 'Available'
        }
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
            $scope.errorMessage = "Failed to send inquiry. Please try again.";
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
            $scope.maintenanceSuccess = '';
            $scope.maintenanceError = "Failed to submit. " + (error.data && error.data.message || 'Please try again.');
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
