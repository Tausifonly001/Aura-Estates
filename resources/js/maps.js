/**
 * Aura Estates — Google Maps Utility Module
 * Provides map rendering for property detail, listing, and contact pages.
 */
var AuraMaps = (function() {
    'use strict';

    var defaultCenter = { lat: 40.7128, lng: -74.0060 };
    var defaultZoom = 12;

    var mapStyles = [
        { elementType: 'geometry', stylers: [{ color: '#f5f3f0' }] },
        { elementType: 'labels.text.stroke', stylers: [{ color: '#f5f3f0' }] },
        { elementType: 'labels.text.fill', stylers: [{ color: '#555555' }] },
        { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#e8e6e1' }] },
        { featureType: 'road', elementType: 'labels.text.fill', stylers: [{ color: '#888888' }] },
        { featureType: 'road.highway', elementType: 'geometry', stylers: [{ color: '#e8e6e1' }] },
        { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#d6e4e0' }] },
        { featureType: 'poi', elementType: 'geometry', stylers: [{ color: '#eae7df' }] },
        { featureType: 'poi', elementType: 'labels.text.fill', stylers: [{ color: '#888888' }] },
        { featureType: 'transit', elementType: 'geometry', stylers: [{ color: '#e8e6e1' }] },
        { featureType: 'administrative', elementType: 'labels.text.fill', stylers: [{ color: '#555555' }] }
    ];

    function createMarkerIcon(color) {
        color = color || '#8c7b6c';
        return {
            path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z',
            fillColor: color,
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            scale: 2,
            anchor: { x: 12, y: 24 }
        };
    }

    function createInfoWindowContent(property) {
        var imgHtml = property.main_image
            ? '<img src="' + property.main_image + '" alt="" style="width:100%;height:120px;object-fit:cover;border-radius:8px 8px 0 0;margin:-15px -15px 10px -15px;">'
            : '';
        return '<div style="max-width:280px;font-family:DM Sans,sans-serif;">' +
            imgHtml +
            '<div style="padding:4px 0;">' +
            '<div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">' +
            (property.property_type || '') + (property.location ? ' &middot; ' + property.location : '') +
            '</div>' +
            '<div style="font-size:15px;font-weight:500;color:#111;margin-bottom:6px;">' + (property.title || '') + '</div>' +
            '<div style="font-size:13px;color:#555;margin-bottom:8px;">' +
            (property.bedrooms || 0) + ' bed &middot; ' + (property.bathrooms || 0) + ' bath &middot; ' + (property.area_sqft || 0) + ' ft&sup2;' +
            '</div>' +
            '<div style="font-size:15px;font-weight:500;color:#8c7b6c;">$' + (property.price ? Number(property.price).toLocaleString() : '') + '<span style="font-size:12px;color:#888;font-weight:400;">/mo</span></div>' +
            '</div>' +
            '</div>';
    }

    function initSinglePropertyMap(containerId, lat, lng, property) {
        if (typeof google === 'undefined' || !google.maps) {
            console.warn('Google Maps API not loaded.');
            return null;
        }
        var container = document.getElementById(containerId);
        if (!container) return null;

        var position = { lat: parseFloat(lat), lng: parseFloat(lng) };
        var map = new google.maps.Map(container, {
            center: position,
            zoom: 15,
            styles: mapStyles,
            disableDefaultUI: true,
            zoomControl: true,
            scrollwheel: false,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false
        });

        var marker = new google.maps.Marker({
            position: position,
            map: map,
            icon: createMarkerIcon(),
            animation: google.maps.Animation.DROP
        });

        if (property) {
            var infoWindow = new google.maps.InfoWindow({
                content: createInfoWindowContent(property)
            });
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
            infoWindow.open(map, marker);
        }

        return map;
    }

    function initMultiPropertyMap(containerId, properties, options) {
        if (typeof google === 'undefined' || !google.maps) {
            console.warn('Google Maps API not loaded.');
            return null;
        }
        options = options || {};
        var container = document.getElementById(containerId);
        if (!container) return null;

        var bounds = new google.maps.LatLngBounds();
        var mapOptions = {
            center: defaultCenter,
            zoom: defaultZoom,
            styles: mapStyles,
            disableDefaultUI: false,
            zoomControl: true,
            scrollwheel: false,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        };

        if (options.zoom) mapOptions.zoom = options.zoom;

        var map = new google.maps.Map(container, mapOptions);
        var markers = [];
        var infoWindows = [];

        properties.forEach(function(p) {
            var lat = parseFloat(p.latitude);
            var lng = parseFloat(p.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            var position = { lat: lat, lng: lng };
            var marker = new google.maps.Marker({
                position: position,
                map: map,
                icon: createMarkerIcon('#8c7b6c'),
                animation: google.maps.Animation.DROP,
                title: p.title
            });

            var infoWindow = new google.maps.InfoWindow({
                content: createInfoWindowContent(p)
            });

            marker.addListener('click', function() {
                infoWindows.forEach(function(iw) { iw.close(); });
                infoWindow.open(map, marker);
            });

            markers.push(marker);
            infoWindows.push(infoWindow);
            bounds.extend(position);
        });

        if (markers.length > 1) {
            map.fitBounds(bounds, { top: 50, right: 50, bottom: 50, left: 50 });
        } else if (markers.length === 1) {
            map.setCenter(markers[0].getPosition());
            map.setZoom(15);
        }

        return map;
    }

    function initOfficeMap(containerId, lat, lng, title) {
        if (typeof google === 'undefined' || !google.maps) {
            console.warn('Google Maps API not loaded.');
            return null;
        }
        var container = document.getElementById(containerId);
        if (!container) return null;

        var position = { lat: lat, lng: lng };
        var map = new google.maps.Map(container, {
            center: position,
            zoom: 16,
            styles: mapStyles,
            disableDefaultUI: true,
            zoomControl: true,
            scrollwheel: false,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false
        });

        var marker = new google.maps.Marker({
            position: position,
            map: map,
            icon: createMarkerIcon('#4c6a46'),
            animation: google.maps.Animation.DROP,
            title: title || 'Aura Estates Office'
        });

        var infoWindow = new google.maps.InfoWindow({
            content: '<div style="font-family:DM Sans,sans-serif;max-width:220px;">' +
                '<div style="font-size:15px;font-weight:500;color:#111;margin-bottom:4px;">' + (title || 'Aura Estates') + '</div>' +
                '<div style="font-size:13px;color:#555;">Linienstrasse 156<br>10115 Berlin, Germany</div>' +
                '</div>'
        });
        marker.addListener('click', function() {
            infoWindow.open(map, marker);
        });
        infoWindow.open(map, marker);

        return map;
    }

    return {
        initSinglePropertyMap: initSinglePropertyMap,
        initMultiPropertyMap: initMultiPropertyMap,
        initOfficeMap: initOfficeMap,
        styles: mapStyles
    };
})();
