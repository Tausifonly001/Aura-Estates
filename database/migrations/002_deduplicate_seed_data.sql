SET @existing_titles = (SELECT COUNT(*) FROM properties);

DELETE p1 FROM properties p1
INNER JOIN properties p2
WHERE p1.id > p2.id AND p1.title = p2.title;

ALTER TABLE properties ADD UNIQUE INDEX idx_property_title (title);

ALTER TABLE amenities ADD UNIQUE INDEX idx_amenity_name_property (name, property_id);

DELETE FROM `amenities` WHERE `property_id` NOT IN (SELECT `id` FROM `properties`);

DELETE FROM `maintenance_requests` WHERE `property_id` NOT IN (SELECT `id` FROM `properties`);

DELETE FROM `amenity_bookings` WHERE `amenity_id` NOT IN (SELECT `id` FROM `amenities`);
