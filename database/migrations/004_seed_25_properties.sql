-- Seed 21 additional luxury properties (total 25)
-- Uses ON CONFLICT DO NOTHING for PostgreSQL

INSERT INTO properties (title, description, price, location, latitude, longitude, property_type, bedrooms, bathrooms, area_sqft, main_image) VALUES

('Amalfi Cliff Residence', 'Perched above the Pacific, this glass-and-stone villa features cantilevered terraces over the ocean with an infinity edge that merges into the horizon.', 9750000.00, 'Pacific Palisades, CA', 34.0459, -118.5260, 'Villa', 6, 7, 7800, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000'),

('The Monolith Tower Penthouse', 'A triple-height penthouse crowning a 60-storey tower. Floor-to-ceiling glazing wraps 360 degrees, framing the entire city skyline.', 14500000.00, 'Manhattan, NY', 40.7614, -73.9716, 'Penthouse', 5, 6, 8200, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000'),

('Maison du Vignoble', 'A 19th-century French estate reimagined for modern living. Original limestone walls meet contemporary steel-and-glass extensions across three connected pavilions.', 6400000.00, 'Napa Valley, CA', 38.2975, -122.2869, 'Estate', 8, 9, 14500, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000'),

('Glacier Point Lodge', 'A timber-and-glass mountain retreat inspired by Scandinavian stave churches. Double-height living spaces open to alpine meadows and glacier views.', 4200000.00, 'Aspen, CO', 39.1869, -106.8178, 'Lodge', 5, 5, 5600, 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1000'),

('Dune House', 'An earth-sheltered residence built into coastal dunes. Rammed-earth walls, a living green roof, and floor-to-ceiling ocean-facing glass define this sustainable masterpiece.', 5800000.00, 'Montauk, NY', 41.0704, -71.9235, 'House', 4, 4, 4200, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1000'),

('The Glass Pavilion', 'A Miesian glass box reinterpreted for the desert. Steel columns, polished concrete floors, and floor-to-ceiling panels frame the Sonoran landscape.', 7200000.00, 'Scottsdale, AZ', 33.4942, -111.9261, 'Villa', 4, 5, 6100, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&q=80&w=1000'),

('Harbour View Tower', 'A 42nd-floor residence in a sculptural waterfront tower. Every room frames the harbour, with private lift lobby and wraparound terrace.', 8900000.00, 'Sydney, NSW', -33.8568, 151.2153, 'Penthouse', 3, 4, 3800, 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1000'),

('Palazzo Nero', 'A Venetian palazzo restored with museum-grade precision. Original frescoed ceilings, Carrara marble bathrooms, and a private canal mooring.', 11500000.00, 'Venice, IT', 45.4408, 12.3155, 'Estate', 7, 8, 11000, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),

('Cedar Bridge Farmhouse', 'A timber-frame farmhouse on 12 acres of rolling pasture. Board-formed concrete, charred cedar cladding, and a geothermal-heated indoor pool.', 3650000.00, 'Hudson Valley, NY', 41.9845, -73.9080, 'Farmhouse', 5, 4, 5200, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000'),

('The Vertex', 'A 28-storey sculptural tower with rotating floor plates. Each unit offers a unique vantage, with private sky gardens on every fifth floor.', 6800000.00, 'Miami Beach, FL', 25.7907, -80.1300, 'Penthouse', 3, 4, 3600, 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&q=80&w=1000'),

('Amanoi Retreat', 'A resort-inspired residence nestled in hillside jungle. Open-air pavilions, private plunge pools, and wraparound verandas blur the line between indoors and out.', 4500000.00, 'Tulum, MX', 20.2145, -87.4291, 'Villa', 4, 5, 4800, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'),

('The Foundry', 'A converted ironworks with triple-height spaces, raw steel trusses, and 14-foot windows. The industrial shell houses a refined, light-filled interior.', 5100000.00, 'Brooklyn, NY', 40.7128, -73.9654, 'Loft', 3, 3, 4500, 'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&q=80&w=1000'),

('Villa Aether', 'A cantilevered concrete and timber villa hovering above a private cove. The cantilever extends 12 metres over the cliff edge, sheltering a natural swimming pool below.', 12800000.00, 'Santorini, GR', 36.3932, 25.4615, 'Villa', 6, 6, 7200, 'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?auto=format&fit=crop&q=80&w=1000'),

('Maison Terre', 'A rammed-earth compound in the hills above Malibu. Three interconnected pods share a central courtyard with a reflecting pool and mature olive trees.', 7500000.00, 'Malibu, CA', 34.0259, -118.7798, 'Compound', 6, 7, 8500, 'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?auto=format&fit=crop&q=80&w=1000'),

('The Observatory', 'A cylindrical glass residence perched on a desert bluff. A rotating living room platform offers 360-degree views from the Mojave to the Pacific.', 5400000.00, 'Joshua Tree, CA', 34.1226, -116.3131, 'House', 3, 3, 3200, 'https://images.unsplash.com/photo-1600596542815-2a4d9fdb252b?auto=format&fit=crop&q=80&w=1000'),

('Schwarzwald Chalet', 'A Black Forest-inspired timber chalet with hand-carved details, a heated outdoor infinity pool, and a private forest trail network.', 3900000.00, 'Whistler, BC', 50.1163, -122.9574, 'Chalet', 6, 5, 6400, 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1000'),

('Skybridge Residences', 'Two towers connected by a sky bridge on the 40th floor. The bridge houses a shared infinity pool, gym, and residents lounge with 360-degree views.', 8200000.00, 'Dubai, UAE', 25.1972, 55.2744, 'Penthouse', 4, 5, 5100, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000'),

('The Copper House', 'A weathered copper-clad residence that evolves with the seasons. The patina shifts from burnished orange to verdigris green over the years.', 4800000.00, 'Portland, OR', 45.5155, -122.6789, 'House', 4, 4, 4100, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000'),

('Marina Bay Grand', 'A waterfront duplex penthouse with a private marina berth. Floor-to-ceiling glass walls fold open to merge the living room with the 800 sq ft terrace.', 10200000.00, 'Singapore', 1.2647, 103.8222, 'Penthouse', 4, 5, 5800, 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000'),

('The Lighthouse', 'A converted Victorian lighthouse keeper residence, fully modernised with a glass-walled upper floor offering unobstructed 360-degree ocean views.', 2800000.00, 'Big Sur, CA', 36.2704, -121.8081, 'House', 3, 3, 2800, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1000'),

('Orchid Court', 'A heritage-listed Georgian townhouse with five floors, original plasterwork, a private garden, and a subterranean spa with plunge pool.', 9100000.00, 'London, UK', 51.5074, -0.1278, 'Townhouse', 6, 5, 6800, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1000');
