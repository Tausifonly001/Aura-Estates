<?php
include_once __DIR__ . '/../models/Property.php';

class PropertyController {
    private $db;
    private $property;

    public function __construct($db) {
        $this->db = $db;
        $this->property = new Property($db);
    }

    public function getAll() {
        $stmt = $this->property->read();
        $num = $stmt->rowCount();

        if($num > 0){
            $properties_arr = array();
            $properties_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                extract($row);
                $property_item = array(
                    "id" => $id,
                    "title" => $title,
                    "description" => $description,
                    "price" => $price,
                    "location" => $location,
                    "property_type" => $property_type,
                    "bedrooms" => $bedrooms,
                    "bathrooms" => $bathrooms,
                    "area_sqft" => $area_sqft,
                    "main_image" => $main_image,
                    "is_available" => $is_available ?? 1
                );
                array_push($properties_arr["records"], $property_item);
            }
            return json_encode($properties_arr);
        } else {
            return json_encode(array("message" => "No properties found."));
        }
    }
}
?>
