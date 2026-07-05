-- Add latitude and longitude columns to properties table
ALTER TABLE properties
    ADD COLUMN latitude DECIMAL(10, 7) DEFAULT NULL AFTER location,
    ADD COLUMN longitude DECIMAL(10, 7) DEFAULT NULL AFTER latitude;

-- Add index for geospatial queries
ALTER TABLE properties
    ADD INDEX idx_coordinates (latitude, longitude);

-- Geocode existing seed data (approximate real coordinates)
UPDATE properties SET latitude = 34.0736, longitude = -118.4004 WHERE location = 'Beverly Hills, CA';
UPDATE properties SET latitude = 34.0259, longitude = -118.7798 WHERE location = 'Malibu, CA';
UPDATE properties SET latitude = 40.9006, longitude = -72.3018 WHERE location = 'Hamptons, NY';
UPDATE properties SET latitude = 40.7178, longitude = -74.0060 WHERE location = 'Tribeca, NY';
