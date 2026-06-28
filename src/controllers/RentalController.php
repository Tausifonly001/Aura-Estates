<?php
include_once __DIR__ . '/../models/Rental.php';

class RentalController {
    private $db;
    private $rental;

    public function __construct($db) {
        $this->db = $db;
        $this->rental = new Rental($db);
    }

    public function getAll() {
        $stmt = $this->rental->readAll();
        $arr = array("records" => array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function getByUser($user_id) {
        $this->rental->user_id = $user_id;
        $stmt = $this->rental->readByUser();
        $arr = array("records" => array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function getOne($id) {
        $this->rental->id = $id;
        $stmt = $this->rental->readOne();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) return json_encode($row);
        return json_encode(array("message" => "Not found."));
    }

    public function create($data) {
        if(!empty($data->user_id) && !empty($data->property_id) && !empty($data->start_date) && !empty($data->monthly_rent)) {
            $this->rental->user_id = $data->user_id;
            $this->rental->property_id = $data->property_id;
            $this->rental->start_date = $data->start_date;
            $this->rental->end_date = $data->end_date ?? date('Y-m-d', strtotime('+1 year'));
            $this->rental->monthly_rent = $data->monthly_rent;
            if($this->rental->create()) {
                http_response_code(201);
                return json_encode(array("message" => "Rental created."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to create rental."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data."));
        }
    }

    public function terminate($id) {
        $this->rental->id = $id;
        if($this->rental->terminate()) {
            return json_encode(array("message" => "Rental terminated."));
        } else {
            http_response_code(503);
            return json_encode(array("message" => "Unable to terminate."));
        }
    }

    public function getMyRentalProperties($user_id) {
        // Get properties that user actively rents
        $this->rental->user_id = $user_id;
        $stmt = $this->rental->readByUser();
        $arr = array("records" => array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if($row['status'] == 'active') {
                array_push($arr["records"], $row);
            }
        }
        return json_encode($arr);
    }
}
