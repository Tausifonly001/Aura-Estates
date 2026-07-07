<?php
require_once __DIR__ . '/../../../src/config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    $path = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('#/property-detail/(\d+)#', $path, $m)) {
        $id = (int)$m[1];
    }
}
if ($id === 0 && !empty($_SERVER['QUERY_STRING'])) {
    parse_str($_SERVER['QUERY_STRING'], $qs);
    $id = isset($qs['id']) ? (int)$qs['id'] : 0;
}
$property = null;
try {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

if (!$property) {
    $fallback = [
        ['id'=>1,'title'=>'The Sapphire Penthouse','description'=>"A stunning penthouse with panoramic ocean views and private elevator access. This architectural masterpiece spans the entire top floor of one of Beverly Hills' most exclusive towers, offering unobstructed views from the Santa Monica Mountains to the Pacific Ocean.\n\nThe open-plan living space features floor-to-ceiling glass walls, imported Italian marble flooring, and a chef's kitchen with Gaggenau appliances. The primary suite includes a spa-inspired bathroom with a soaking tub overlooking the ocean, dual walk-in closets, and a private terrace.\n\nBuilding amenities include 24/7 concierge, private elevator, infinity pool, fitness center, and secured parking with EV charging stations.",'price'=>5000000,'location'=>'Beverly Hills, CA','property_type'=>'Penthouse','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>4500,'main_image'=>'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1200','features'=>"Panoramic ocean views\nPrivate elevator access\nFloor-to-ceiling glass walls\nChef's kitchen with Gaggenau appliances\nSpa-inspired primary bathroom\nPrivate terrace\n24/7 concierge\nInfinity pool\nFitness center\nEV charging stations",'latitude'=>34.0736,'longitude'=>-118.4004],
        ['id'=>2,'title'=>'Onyx Villa','description'=>"Modern architectural masterpiece nestled in the hills with infinity pool. This estate is a study in contrast — raw concrete volumes softened by floor-to-ceiling glass and warm timber accents.\n\nThe main level flows seamlessly from an open living area to an outdoor terrace with an infinity pool that seems to merge with the Pacific horizon. The lower level houses a cinema room, wine cellar, and gym.\n\nSustainable features include solar panels, rainwater harvesting, and a geothermal heating system.",'price'=>3500000,'location'=>'Malibu, CA','property_type'=>'Villa','bedrooms'=>5,'bathrooms'=>6,'area_sqft'=>6000,'main_image'=>'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1200','features'=>"Infinity pool with ocean views\nCinema room\nWine cellar\nHome gym\nSolar panels\nRainwater harvesting\nGeothermal heating\nSmart home automation",'latitude'=>34.0259,'longitude'=>-118.7798],
        ['id'=>3,'title'=>'Emerald Estate','description'=>"Classic luxury estate with sprawling gardens and tennis court. Set on 3.5 acres of manicured grounds, this Georgian-inspired estate blends timeless elegance with modern amenities.\n\nThe residence features 12,000 square feet of living space across three floors, including a grand foyer with double staircase, formal living and dining rooms, a library, and a sunroom overlooking the gardens.\n\nThe property includes a separate guest house, heated swimming pool, championship tennis court, and a three-car garage.",'price'=>8200000,'location'=>'Hamptons, NY','property_type'=>'Estate','bedrooms'=>7,'bathrooms'=>8,'area_sqft'=>12000,'main_image'=>'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1200','features'=>"3.5 acres of manicured grounds\nGrand foyer with double staircase\nLibrary and sunroom\nSeparate guest house\nHeated swimming pool\nChampionship tennis court\nThree-car garage\nStaff quarters",'latitude'=>40.9006,'longitude'=>-72.3018],
        ['id'=>4,'title'=>'Golden Loft','description'=>"Industrial chic loft in the heart of the city with floor-to-ceiling windows. This converted warehouse space offers soaring 14-foot ceilings, exposed brick walls, and original timber beams.\n\nThe open floor plan is perfect for entertaining, with a chef's kitchen featuring custom cabinetry and premium appliances. The mezzanine level houses the primary suite with skylights.\n\nLocated in Tribeca's cobblestone streets, steps from world-class dining and galleries.",'price'=>1200000,'location'=>'Tribeca, NY','property_type'=>'Loft','bedrooms'=>2,'bathrooms'=>2,'area_sqft'=>2500,'main_image'=>'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1200','features'=>"14-foot ceilings\nExposed brick walls\nOriginal timber beams\nChef's kitchen\nMezzanine primary suite\nSkylights\nHardwood floors\nCentral air conditioning",'latitude'=>40.7178,'longitude'=>-74.0060],
        ['id'=>5,'title'=>'Crystal Waters Estate','description'=>"A breathtaking waterfront estate with private dock, infinity pool, and panoramic ocean views. This contemporary masterpiece sits on a double lot with 120 feet of water frontage.\n\nThe residence features an open-concept design with retractable glass walls that blur the line between indoor and outdoor living. The rooftop deck offers 360-degree views of Biscayne Bay.\n\nIncludes a private dock for vessels up to 60 feet, infinity pool with spa, and outdoor kitchen.",'price'=>7200000,'location'=>'Miami Beach, FL','property_type'=>'Estate','bedrooms'=>6,'bathrooms'=>7,'area_sqft'=>8500,'main_image'=>'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1200','features'=>"120 feet water frontage\nPrivate dock for 60ft vessel\nInfinity pool with spa\nRetractable glass walls\nRooftop deck with 360° views\nOutdoor kitchen\nSmart home system\nHurricane-rated windows",'latitude'=>25.7907,'longitude'=>-80.1300],
        ['id'=>6,'title'=>'The Ivory Tower','description'=>"Minimalist penthouse occupying the entire top floor with 360-degree city views. This penthouse is a study in restrained luxury — every detail has been considered.\n\nThe open living space features polished concrete floors, custom millwork, and motorized shades throughout. The kitchen is a masterpiece of minimalist design with integrated appliances.\n\nA private elevator opens directly into the residence, and the wraparound terrace offers unobstructed views from the Hudson River to the East River.",'price'=>4500000,'location'=>'Manhattan, NY','property_type'=>'Penthouse','bedrooms'=>3,'bathrooms'=>4,'area_sqft'=>3800,'main_image'=>'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&q=80&w=1200','features'=>"Entire top floor\n360-degree city views\nPrivate elevator\nMotorized shades\nPolished concrete floors\nCustom millwork\nWraparound terrace\nIntegrated appliances",'latitude'=>40.7614,'longitude'=>-73.9716],
        ['id'=>7,'title'=>'Villa del Sol','description'=>"Mediterranean-inspired villa surrounded by olive groves with a private vineyard. This estate captures the essence of Tuscan living on the California coast.\n\nHand-laid stone walls, terra cotta roof tiles, and reclaimed wood beams create an atmosphere of timeless warmth. The property includes a working vineyard producing approximately 200 cases of Pinot Noir annually.\n\nA separate guest casita, infinity pool with canyon views, and outdoor dining pavilion complete the estate.",'price'=>2800000,'location'=>'Santa Barbara, CA','property_type'=>'Villa','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>5500,'main_image'=>'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1200','features'=>"Private vineyard\nWorking wine production\nGuest casita\nInfinity pool\nOutdoor dining pavilion\nOlive groves\nHand-laid stone walls\nReclaimed wood beams",'latitude'=>34.4208,'longitude'=>-119.6982],
        ['id'=>8,'title'=>'The Industrial Loft','description'=>"Converted warehouse with exposed brick walls, 20-foot ceilings, and curated interiors. This loft space celebrates its industrial heritage while offering every modern comfort.\n\nOriginal steel trusses and concrete floors are complemented by custom lighting and built-in storage. The open plan is anchored by a chef's kitchen with a 12-foot island.\n\nLocated in a landmarked building with a shared rooftop terrace and courtyard garden.",'price'=>950000,'location'=>'Brooklyn, NY','property_type'=>'Loft','bedrooms'=>2,'bathrooms'=>2,'area_sqft'=>2200,'main_image'=>'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&q=80&w=1200','features'=>"20-foot ceilings\nExposed brick walls\nOriginal steel trusses\n12-foot kitchen island\nCustom lighting\nBuilt-in storage\nRooftop terrace\nCourtyard garden",'latitude'=>40.7128,'longitude'=>-73.9654],
        ['id'=>9,'title'=>'Azure Cliffs Residence','description'=>"Sculptural concrete and glass masterpiece cantilevered over the Pacific Ocean. This residence is an architectural landmark, perched on a dramatic cliffside site.\n\nThe main living volume悬挑s 15 feet over the ocean, creating a sense of floating above the waves. Floor-to-ceiling glass panels dissolve the boundary between interior and landscape.\n\nA meditation room, infinity pool, and private trail to the beach complete this extraordinary property.",'price'=>9800000,'location'=>'Big Sur, CA','property_type'=>'Estate','bedrooms'=>5,'bathrooms'=>6,'area_sqft'=>7200,'main_image'=>'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1200','features'=>"Cantilevered over ocean\n15-foot cantilever\nFloor-to-ceiling glass\nMeditation room\nInfinity pool\nPrivate beach trail\nConcrete construction\nPacific Ocean views",'latitude'=>36.2704,'longitude'=>-121.8081],
        ['id'=>10,'title'=>'The Metropolitan','description'=>"Sleek modern penthouse in the financial district with smart home automation. This residence represents the pinnacle of urban living with cutting-edge technology integrated throughout.\n\nEvery system — lighting, climate, audio, security, shades — is controlled via a custom Crestron system or voice commands. The open plan features a chef's kitchen with Sub-Zero and Wolf appliances.\n\nBuilding amenities include a rooftop infinity pool, private screening room, and 24-hour valet.",'price'=>3200000,'location'=>'San Francisco, CA','property_type'=>'Penthouse','bedrooms'=>3,'bathrooms'=>3,'area_sqft'=>3100,'main_image'=>'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&q=80&w=1200','features'=>"Full smart home automation\nCrestron control system\nSub-Zero & Wolf appliances\nRooftop infinity pool\nPrivate screening room\n24-hour valet\nWine storage\nDog spa",'latitude'=>37.7749,'longitude'=>-122.4194],
        ['id'=>11,'title'=>'Amalfi Cliff Residence','description'=>"Perched above the Pacific, this glass-and-stone villa features cantilevered terraces over the ocean. Inspired by the cliffs of the Amalfi Coast, this residence is both dramatic and serene.\n\nThree levels of living space cascade down the hillside, each with its own terrace and ocean views. The infinity pool appears to spill into the Pacific.\n\nA guest suite, home theater, and gym occupy the lower level, with direct access to a private hiking trail.",'price'=>9750000,'location'=>'Pacific Palisades, CA','property_type'=>'Villa','bedrooms'=>6,'bathrooms'=>7,'area_sqft'=>7800,'main_image'=>'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1200','features'=>"Cantilevered terraces\nInfinity pool\nThree-level design\nHome theater\nGuest suite\nHome gym\nPrivate hiking trail\nPacific Ocean views",'latitude'=>34.0459,'longitude'=>-118.5260],
        ['id'=>12,'title'=>'The Monolith Tower Penthouse','description'=>"A triple-height penthouse crowning a 60-storey tower with 360-degree glazing. This penthouse occupies the top three floors of one of Manhattan's most iconic residential towers.\n\nThe crown jewel is the triple-height great room with a 30-foot wall of glass offering unobstructed views in every direction. A private rooftop terrace includes a heated pool and outdoor kitchen.\n\nTwo private elevators, a wine cellar with 500-bottle capacity, and a staff apartment complete this extraordinary residence.",'price'=>14500000,'location'=>'Manhattan, NY','property_type'=>'Penthouse','bedrooms'=>5,'bathrooms'=>6,'area_sqft'=>8200,'main_image'=>'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1200','features'=>"Triple-height great room\n30-foot glass wall\nPrivate rooftop terrace\nHeated pool\nTwo private elevators\n500-bottle wine cellar\nStaff apartment\n360-degree views",'latitude'=>40.7614,'longitude'=>-73.9716],
        ['id'=>13,'title'=>'Maison du Vignoble','description'=>"A 19th-century French estate reimagined with steel-and-glass extensions. This property harmonizes historic architecture with bold contemporary intervention.\n\nThe original stone farmhouse has been meticulously restored, while new glass-and-steel volumes house a modern kitchen, art gallery, and meditation pavilion.\n\nSurrounded by 8 acres of vineyards and lavender fields, with a restored barn for events and a producer's cottage.",'price'=>6400000,'location'=>'Napa Valley, CA','property_type'=>'Estate','bedrooms'=>8,'bathrooms'=>9,'area_sqft'=>14500,'main_image'=>'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1200','features'=>"19th-century stone farmhouse\nSteel-and-glass extensions\nArt gallery\nMeditation pavilion\n8 acres of vineyards\nLavender fields\nRestored event barn\nProducer's cottage",'latitude'=>38.2975,'longitude'=>-122.2869],
        ['id'=>14,'title'=>'Glacier Point Lodge','description'=>"A timber-and-glass mountain retreat inspired by Scandinavian stave churches. This lodge sits at 8,000 feet elevation with panoramic views of the Elk Mountains.\n\nMassive timber frames and floor-to-ceiling glass create a cathedral-like living space warmed by a central stone fireplace. The property includes a ski-in/ski-out trail, hot tub, and sauna.\n\nA separate caretaker's cabin and equipment storage complete this mountain estate.",'price'=>4200000,'location'=>'Aspen, CO','property_type'=>'Lodge','bedrooms'=>5,'bathrooms'=>5,'area_sqft'=>5600,'main_image'=>'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1200','features'=>"Ski-in/ski-out access\nCentral stone fireplace\nHot tub and sauna\nTimber frame construction\nPanoramic mountain views\nCaretaker's cabin\nEquipment storage\nRadiant floor heating",'latitude'=>39.1869,'longitude'=>-106.8178],
        ['id'=>15,'title'=>'Dune House','description'=>"An earth-sheltered residence built into coastal dunes with a living green roof. This home disappears into the landscape, offering privacy and thermal efficiency.\n\nThe green roof insulates the home while supporting native grasses and wildflowers. Interior spaces are oriented toward the ocean through a dramatic glass facade.\n\nGeothermal heating, solar panels, and rainwater collection make this one of the most sustainable luxury homes on the East Coast.",'price'=>5800000,'location'=>'Montauk, NY','property_type'=>'House','bedrooms'=>4,'bathrooms'=>4,'area_sqft'=>4200,'main_image'=>'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1200','features'=>"Earth-sheltered design\nLiving green roof\nGeothermal heating\nSolar panels\nRainwater collection\nOcean-facing glass facade\nNative landscaping\nPassive cooling",'latitude'=>41.0704,'longitude'=>-71.9235],
        ['id'=>16,'title'=>'The Glass Pavilion','description'=>"A Miesian glass box reinterpreted for the desert with polished concrete floors. This residence is a masterwork of minimalist architecture in the Sonoran Desert.\n\nTransparent glass walls on all four sides frame the desert landscape like living artwork. The flat roof extends beyond the glass walls to create shaded outdoor living areas.\n\nA reflection pool, desert garden, and detached studio complete this serene compound.",'price'=>7200000,'location'=>'Scottsdale, AZ','property_type'=>'Villa','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>6100,'main_image'=>'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&q=80&w=1200','features'=>"Full glass walls\nPolished concrete floors\nExtended roof overhangs\nReflection pool\nDesert garden\nDetached studio\nMiesian design\nDesert integration",'latitude'=>33.4942,'longitude'=>-111.9261],
        ['id'=>17,'title'=>'Harbour View Tower','description'=>"A 42nd-floor residence in a sculptural waterfront tower with wraparound terrace. This apartment offers commanding views of Sydney Harbour and the Opera House.\n\nThe open-plan living area features floor-to-ceiling glass, European oak flooring, and a gourmet kitchen with Miele appliances. The wraparound terrace provides 270-degree harbor views.\n\nResidents enjoy access to a infinity pool, private dining room, and concierge service.",'price'=>8900000,'location'=>'Sydney, NSW','property_type'=>'Penthouse','bedrooms'=>3,'bathrooms'=>4,'area_sqft'=>3800,'main_image'=>'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&q=80&w=1200','features'=>"42nd floor\nWraparound terrace\nSydney Harbour views\nMiele appliances\nEuropean oak flooring\nInfinity pool\nPrivate dining room\nConcierge service",'latitude'=>-33.8568,'longitude'=>151.2153],
        ['id'=>18,'title'=>'Palazzo Nero','description'=>"A Venetian palazzo restored with museum-grade precision and private canal mooring. This 16th-century palace has been sensitively restored to its former glory.\n\nOriginal frescoes, terrazzo floors, and hand-carved marble details have been preserved alongside modern systems for climate control and seismic reinforcement.\n\nThe property includes a private boat dock on the Grand Canal, a rooftop terrace with panoramic views, and a restored ground-floor commercial space.",'price'=>11500000,'location'=>'Venice, IT','property_type'=>'Estate','bedrooms'=>7,'bathrooms'=>8,'area_sqft'=>11000,'main_image'=>'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1200','features'=>"16th-century palazzo\nOriginal frescoes\nTerrazzo floors\nPrivate canal mooring\nRooftop terrace\nSeismic reinforcement\nClimate control\nGrand Canal access",'latitude'=>45.4408,'longitude'=>12.3155],
        ['id'=>19,'title'=>'Cedar Bridge Farmhouse','description'=>"A timber-frame farmhouse on 12 acres with a geothermal-heated indoor pool. This property blends rustic charm with modern luxury in the Hudson Valley.\n\nHand-hewn timber frames, wide-plank pine floors, and a massive stone fireplace define the main living space. The attached barn has been converted into a spectacular indoor pool house.\n\nThe property includes a restored carriage house, organic vegetable garden, and walking trails through mature hardwood forest.",'price'=>3650000,'location'=>'Hudson Valley, NY','property_type'=>'Farmhouse','bedrooms'=>5,'bathrooms'=>4,'area_sqft'=>5200,'main_image'=>'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1200','features'=>"12 acres\nGeothermal-heated indoor pool\nHand-hewn timber frames\nRestored carriage house\nOrganic vegetable garden\nWalking trails\nStone fireplace\nWide-plank pine floors",'latitude'=>41.9845,'longitude'=>-73.9080],
        ['id'=>20,'title'=>'The Vertex','description'=>"A 28-storey sculptural tower with rotating floor plates and sky gardens. This architectural landmark features floors that rotate independently, offering ever-changing views.\n\nEach residence is unique, with floor plans that shift orientation throughout the day. Sky gardens on every fifth floor provide communal green space with panoramic views.\n\nAmenities include a rooftop helipad, infinity pool, private cinema, and automated parking system.",'price'=>6800000,'location'=>'Miami Beach, FL','property_type'=>'Penthouse','bedrooms'=>3,'bathrooms'=>4,'area_sqft'=>3600,'main_image'=>'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&q=80&w=1200','features'=>"Rotating floor plates\nSky gardens\nRooftop helipad\nInfinity pool\nPrivate cinema\nAutomated parking\nEver-changing views\n28-storey tower",'latitude'=>25.7907,'longitude'=>-80.1300],
        ['id'=>21,'title'=>'Amanoi Retreat','description'=>"A resort-inspired residence nestled in hillside jungle with private plunge pools. Inspired by the Aman resorts, this home offers absolute tranquility in a tropical setting.\n\nThatched-roof pavilions connected by covered walkways surround a central reflection pool. Each bedroom opens to a private plunge pool and jungle canopy.\n\nA meditation pavilion, yoga deck, and spa treatment room complete this wellness-focused retreat.",'price'=>4500000,'location'=>'Tulum, MX','property_type'=>'Villa','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>4800,'main_image'=>'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1200','features'=>"Private plunge pools\nReflection pool\nMeditation pavilion\nYoga deck\nSpa treatment room\nThatched-roof pavilions\nJungle canopy setting\nSustainable design",'latitude'=>20.2145,'longitude'=>-87.4291],
        ['id'=>22,'title'=>'The Foundry','description'=>"A converted ironworks with triple-height spaces and raw steel trusses. This industrial conversion celebrates the building's manufacturing heritage.\n\nOriginal overhead cranes have been preserved as sculptural elements. The triple-height main space is perfect for art collectors, with gallery-grade lighting and climate control.\n\nA mezzanine loft overlooks the main space, and a rooftop terrace provides outdoor entertaining with city views.",'price'=>5100000,'location'=>'Brooklyn, NY','property_type'=>'Loft','bedrooms'=>3,'bathrooms'=>3,'area_sqft'=>4500,'main_image'=>'https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?auto=format&fit=crop&q=80&w=1200','features'=>"Triple-height spaces\nOriginal steel trusses\nPreserved overhead cranes\nGallery-grade lighting\nMezzanine loft\nRooftop terrace\nClimate control\nIndustrial heritage",'latitude'=>40.7128,'longitude'=>-73.9654],
        ['id'=>23,'title'=>'Villa Aether','description'=>"A cantilevered concrete and timber villa hovering above a private cove. This Aegean masterpiece appears to float above the volcanic landscape of Santorini.\n\nCantilevered terraces extend over the caldera, each with private infinity pools that merge with the Aegean Sea. Interior spaces are carved into the volcanic rock, creating cave-like bedrooms with surprising volume.\n\nA private elevator descends to the cove beach, and a wine cellar showcases local Assyrtiko wines.",'price'=>12800000,'location'=>'Santorini, GR','property_type'=>'Villa','bedrooms'=>6,'bathrooms'=>6,'area_sqft'=>7200,'main_image'=>'https://images.unsplash.com/photo-1600585154363-67eb9e2e2099?auto=format&fit=crop&q=80&w=1200','features'=>"Cantilevered over caldera\nPrivate infinity pools\nCave-like bedrooms\nPrivate elevator to beach\nWine cellar\nAegean Sea views\nVolcanic rock construction\nSunset views",'latitude'=>36.3932,'longitude'=>25.4615],
        ['id'=>24,'title'=>'Maison Terre','description'=>"A rammed-earth compound in the hills above Malibu with reflecting pool. This compound is a meditation on materiality, built entirely from earth sourced on-site.\n\nRammed-earth walls create a warm, monolithic interior that changes color with the light. Floor-to-ceiling glass panels frame views of the Pacific.\n\nThe compound includes a main house, guest pavilion, studio, and meditation garden with a reflecting pool.",'price'=>7500000,'location'=>'Malibu, CA','property_type'=>'Compound','bedrooms'=>6,'bathrooms'=>7,'area_sqft'=>8500,'main_image'=>'https://images.unsplash.com/photo-1600573472591-ee6b68d14c68?auto=format&fit=crop&q=80&w=1200','features'=>"Rammed-earth construction\nReflecting pool\nGuest pavilion\nStudio\nMeditation garden\nPacific Ocean views\nOn-site earth sourcing\nMonolithic interior",'latitude'=>34.0259,'longitude'=>-118.7798],
        ['id'=>25,'title'=>'The Observatory','description'=>"A cylindrical glass residence with a rotating living room platform. This home is an astronomical instrument as much as a residence.\n\nThe cylindrical form features floor-to-ceiling glass on all sides, with a motorized rotating platform in the living area that completes one revolution per hour. The rooftop observatory houses a professional-grade telescope.\n\nSet on 5 acres in the high desert with some of the darkest skies in North America.",'price'=>5400000,'location'=>'Joshua Tree, CA','property_type'=>'House','bedrooms'=>3,'bathrooms'=>3,'area_sqft'=>3200,'main_image'=>'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&q=80&w=1200','features'=>"Rotating living room\nCylindrical glass design\nProfessional observatory\n5 acres\nDarkest skies in North America\nMotorized platform\nDesert landscape\nSustainable systems",'latitude'=>34.1226,'longitude'=>-116.3131],
        ['id'=>26,'title'=>'Schwarzwald Chalet','description'=>"A Black Forest-inspired timber chalet with heated infinity pool. This alpine retreat combines traditional craftsmanship with contemporary luxury.\n\nHand-cut timber, stone fireplaces, and copper fixtures create a warm, authentic atmosphere. The heated infinity pool overlooks snow-capped peaks year-round.\n\nA ski room, wine cellar, and home cinema occupy the lower level, while the upper level houses four en-suite bedrooms with mountain views.",'price'=>3900000,'location'=>'Whistler, BC','property_type'=>'Chalet','bedrooms'=>6,'bathrooms'=>5,'area_sqft'=>6400,'main_image'=>'https://images.unsplash.com/photo-1518780664697-55e3ad937233?auto=format&fit=crop&q=80&w=1200','features'=>"Heated infinity pool\nHand-cut timber\nStone fireplaces\nSki room\nWine cellar\nHome cinema\nMountain views\nRadiant floor heating",'latitude'=>50.1163,'longitude'=>-122.9574],
        ['id'=>27,'title'=>'Skybridge Residences','description'=>"Two towers connected by a sky bridge with shared infinity pool on 40th floor. This architectural marvel links two residential towers with a dramatic cantilevered bridge.\n\nThe sky bridge houses a shared infinity pool, lounge, and fitness center with panoramic views. Each tower features floor-to-ceiling glass and private balconies.\n\nResidents enjoy concierge service, private elevators, and access to both tower amenities.",'price'=>8200000,'location'=>'Dubai, UAE','property_type'=>'Penthouse','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>5100,'main_image'=>'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1200','features'=>"Sky bridge infinity pool\nTwo-tower design\n40th floor amenities\nPanoramic views\nPrivate elevators\nConcierge service\nPrivate balconies\nFitness center",'latitude'=>25.1972,'longitude'=>55.2744],
        ['id'=>28,'title'=>'The Copper House','description'=>"A weathered copper-clad residence that evolves with the seasons. The copper exterior patinas naturally, creating a living facade that changes color over time.\n\nInside, warm wood and concrete complement the metallic exterior. Floor-to-ceiling windows frame views of the Cascade Range.\n\nA geothermal system, solar panels, and rainwater collection make this one of the most sustainable homes in the Pacific Northwest.",'price'=>4800000,'location'=>'Portland, OR','property_type'=>'House','bedrooms'=>4,'bathrooms'=>4,'area_sqft'=>4100,'main_image'=>'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1200','features'=>"Copper cladding\nLiving facade\nGeothermal system\nSolar panels\nRainwater collection\nCascade Range views\nSustainable design\nNatural patina",'latitude'=>45.5155,'longitude'=>-122.6789],
        ['id'=>29,'title'=>'Marina Bay Grand','description'=>"A waterfront duplex penthouse with private marina berth. This duplex occupies the top two floors of Singapore's most prestigious waterfront address.\n\nThe upper level features a rooftop terrace with infinity pool and panoramic views of Marina Bay and the city skyline. The lower level houses the main living areas with floor-to-ceiling glass.\n\nA private elevator, wine cellar, and direct marina access complete this exceptional residence.",'price'=>10200000,'location'=>'Singapore','property_type'=>'Penthouse','bedrooms'=>4,'bathrooms'=>5,'area_sqft'=>5800,'main_image'=>'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1200','features'=>"Duplex penthouse\nPrivate marina berth\nRooftop infinity pool\nMarina Bay views\nPrivate elevator\nWine cellar\nCity skyline views\nDirect marina access",'latitude'=>1.2647,'longitude'=>103.8222],
        ['id'=>30,'title'=>'The Lighthouse','description'=>"A converted Victorian lighthouse with glass-walled upper floor. This historic lighthouse has been transformed into a unique residence with breathtaking ocean views.\n\nThe original lantern room has been converted into a glass-walled living space offering 360-degree views of the Pacific. The tower houses three bedrooms, each with ocean views.\n\nA keeper's cottage, stone garage, and cliffside garden complete this one-of-a-kind property.",'price'=>2800000,'location'=>'Big Sur, CA','property_type'=>'House','bedrooms'=>3,'bathrooms'=>3,'area_sqft'=>2800,'main_image'=>'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&q=80&w=1200','features'=>"Converted lighthouse\n360-degree ocean views\nGlass-walled living space\nVictorian architecture\nKeeper's cottage\nStone garage\nCliffside garden\nHistoric landmark",'latitude'=>36.2704,'longitude'=>-121.8081],
        ['id'=>31,'title'=>'Orchid Court','description'=>"A heritage-listed Georgian townhouse with subterranean spa. This London townhouse has been meticulously restored to its 18th-century glory.\n\nOriginal plasterwork, marble fireplaces, and mahogany paneling have been preserved alongside a spectacular subterranean extension housing a spa, cinema, and wine vault.\n\nA private walled garden, mews house, and underground parking complete this rare London property.",'price'=>9100000,'location'=>'London, UK','property_type'=>'Townhouse','bedrooms'=>6,'bathrooms'=>5,'area_sqft'=>6800,'main_image'=>'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&q=80&w=1200','features'=>"Heritage-listed Georgian\nSubterranean spa\nOriginal plasterwork\nMarble fireplaces\nPrivate walled garden\nMews house\nUnderground parking\nCinema room",'latitude'=>51.5074,'longitude'=>-0.1278],
    ];
    foreach ($fallback as $fp) {
        if ((int)$fp['id'] === $id) {
            $property = $fp;
            break;
        }
    }
}

if (!$property) {
    header('Location: properties');
    exit;
}

$pageTitle = $property['title'];
$currentPage = 'properties';
?>
<?php include __DIR__ . '/../partials/header.php'; ?>

<section class="page-hero" style="background: linear-gradient(180deg, #f2efe9 0%, #e8e5db 100%); padding: 6rem 0 2.5rem;">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12 w-full relative z-10">
        <a href="/properties" class="inline-flex items-center gap-2 font-mono text-[0.625rem] tracking-[0.02em] uppercase text-muted hover:text-ink transition-colors no-underline mb-6">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M11 7H3M3 7l4-4M3 7l4 4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back to Properties
        </a>
        <div class="flex flex-wrap gap-3 font-mono text-[0.625rem] lg:text-[0.75rem] tracking-[-0.02em] uppercase text-muted mb-4">
            <span class="border border-border px-3 py-1 rounded-full"><?php echo htmlspecialchars($property['property_type']); ?></span>
            <span class="border border-border px-3 py-1 rounded-full"><?php echo htmlspecialchars($property['location']); ?></span>
            <?php if (!empty($property['status'])): ?>
            <span class="border border-border px-3 py-1 rounded-full"><?php echo htmlspecialchars($property['status']); ?></span>
            <?php endif; ?>
        </div>
        <h1 class="font-sans font-medium text-[2.5rem] lg:text-[4rem] leading-[1.05] text-ink max-w-[30ch]" data-split><?php echo htmlspecialchars($property['title']); ?></h1>
    </div>
</section>

<section class="py-12 lg:py-20">
    <div class="max-w-[120rem] mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-10 lg:gap-16">

            <div class="lg:col-span-3">
                <div class="aspect-[16/10] bg-surface border border-border-light overflow-hidden rounded-xl mb-8">
                    <?php if (!empty($property['main_image'])): ?>
                    <img src="<?php echo htmlspecialchars($property['main_image']); ?>" class="w-full h-full object-cover" alt="<?php echo htmlspecialchars($property['title']); ?>" onerror="this.onerror=null;this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-bg-alt\'><i class=\'fas fa-image text-4xl text-muted\'></i></div>';">
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-bg-alt"><i class="fas fa-image text-4xl text-muted"></i></div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-wrap gap-6 mb-8 py-5 px-6 bg-bg-alt rounded-xl border border-border-light">
                    <div class="flex items-center gap-2 font-mono text-[0.75rem] text-muted">
                        <i class="fas fa-bed text-accent"></i>
                        <span><strong class="text-ink"><?php echo $property['bedrooms']; ?></strong> Bedrooms</span>
                    </div>
                    <div class="flex items-center gap-2 font-mono text-[0.75rem] text-muted">
                        <i class="fas fa-bath text-accent"></i>
                        <span><strong class="text-ink"><?php echo $property['bathrooms']; ?></strong> Bathrooms</span>
                    </div>
                    <div class="flex items-center gap-2 font-mono text-[0.75rem] text-muted">
                        <i class="fas fa-ruler-combined text-accent"></i>
                        <span><strong class="text-ink"><?php echo number_format($property['area_sqft']); ?></strong> sq ft</span>
                    </div>
                    <?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
                    <div class="flex items-center gap-2 font-mono text-[0.75rem] text-muted">
                        <i class="fas fa-map-marker-alt text-accent"></i>
                        <span><?php echo number_format((float)$property['latitude'], 4); ?>&deg;, <?php echo number_format((float)$property['longitude'], 4); ?>&deg;</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mb-10">
                    <h2 class="font-sans font-medium text-[1.25rem] text-ink mb-4">About This Property</h2>
                    <div class="font-sans text-[0.9375rem] leading-[1.8] text-ink-secondary space-y-4">
                        <?php foreach (explode("\n", $property['description'] ?? '') as $para): ?>
                        <?php if (trim($para)): ?>
                        <p><?php echo htmlspecialchars(trim($para)); ?></p>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (!empty($property['features'])): ?>
                <div class="mb-10">
                    <h2 class="font-sans font-medium text-[1.25rem] text-ink mb-5">Features &amp; Amenities</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php foreach (explode("\n", $property['features']) as $feature): ?>
                        <?php if (trim($feature)): ?>
                        <div class="flex items-center gap-3 py-3 px-4 bg-bg-alt rounded-lg border border-border-light">
                            <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="font-sans text-[0.875rem] text-ink-secondary"><?php echo htmlspecialchars(trim($feature)); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
                <div>
                    <h2 class="font-sans font-medium text-[1.25rem] text-ink mb-5">Location</h2>
                    <div id="property-map" class="w-full h-[350px] lg:h-[450px] bg-bg-alt border border-border-light rounded-xl overflow-hidden mb-4"></div>
                    <div class="flex flex-wrap gap-4">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo htmlspecialchars($property['latitude'] . ',' . $property['longitude']); ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 font-mono text-[0.625rem] tracking-[0.02em] uppercase text-ink-secondary hover:text-accent transition-colors no-underline border border-border-light px-4 py-2 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            Get Directions
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-24">
                    <div class="bg-surface border border-border-light rounded-xl p-6 lg:p-8 mb-6">
                        <p class="font-sans font-medium text-[1.75rem] lg:text-[2.25rem] text-ink mb-1">$<?php echo number_format($property['price']); ?></p>
                        <p class="font-mono text-[0.625rem] tracking-[0.02em] uppercase text-muted mb-6"><?php echo htmlspecialchars($property['property_type']); ?> &middot; <?php echo htmlspecialchars($property['location']); ?></p>

                        <p class="font-sans font-medium text-[1rem] text-ink mb-4">Interested in this property?</p>
                        <form action="/contact" method="GET" class="flex flex-col gap-3">
                            <input type="hidden" name="property" value="<?php echo $property['id']; ?>">
                            <input type="text" name="name" class="input-field" placeholder="Your name" required>
                            <input type="email" name="email" class="input-field" placeholder="Your email" required>
                            <textarea name="message" class="input-field resize-y leading-relaxed" rows="3" placeholder="I'm interested in <?php echo htmlspecialchars($property['title']); ?>..." required></textarea>
                            <button type="submit" class="btn-primary w-full justify-center mt-2">Send Inquiry</button>
                        </form>
                    </div>

                    <div class="bg-surface border border-border-light rounded-xl p-6 lg:p-8">
                        <p class="font-mono text-[0.625rem] tracking-[0.1em] uppercase text-muted mb-4">Property Summary</p>
                        <div class="space-y-3">
                            <div class="flex justify-between font-sans text-[0.875rem]">
                                <span class="text-muted">Type</span>
                                <span class="text-ink font-medium"><?php echo htmlspecialchars($property['property_type']); ?></span>
                            </div>
                            <div class="flex justify-between font-sans text-[0.875rem]">
                                <span class="text-muted">Bedrooms</span>
                                <span class="text-ink font-medium"><?php echo $property['bedrooms']; ?></span>
                            </div>
                            <div class="flex justify-between font-sans text-[0.875rem]">
                                <span class="text-muted">Bathrooms</span>
                                <span class="text-ink font-medium"><?php echo $property['bathrooms']; ?></span>
                            </div>
                            <div class="flex justify-between font-sans text-[0.875rem]">
                                <span class="text-muted">Area</span>
                                <span class="text-ink font-medium"><?php echo number_format($property['area_sqft']); ?> sq ft</span>
                            </div>
                            <div class="flex justify-between font-sans text-[0.875rem]">
                                <span class="text-muted">Location</span>
                                <span class="text-ink font-medium"><?php echo htmlspecialchars($property['location']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
<script>
(function() {
    function initMap() {
        if (typeof AuraMaps !== 'undefined' && window.__googleMapsReady) {
            AuraMaps.initSinglePropertyMap('property-map',
                '<?php echo htmlspecialchars($property['latitude']); ?>',
                '<?php echo htmlspecialchars($property['longitude']); ?>',
                <?php echo json_encode([
                    'title' => $property['title'],
                    'location' => $property['location'],
                    'property_type' => $property['property_type'],
                    'bedrooms' => $property['bedrooms'],
                    'bathrooms' => $property['bathrooms'],
                    'area_sqft' => $property['area_sqft'],
                    'price' => $property['price'],
                    'main_image' => $property['main_image']
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
            );
        }
    }
    if (window.__googleMapsReady) { initMap(); }
    else { document.addEventListener('google-maps-ready', initMap); }
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
