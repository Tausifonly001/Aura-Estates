<?php
include_once __DIR__ . '/../models/Amenity.php';
include_once __DIR__ . '/../models/AmenityBooking.php';
include_once __DIR__ . '/../config/auth.php';

class AmenityController {
    private $db;
    private $amenity;
    private $booking;

    public function __construct($db) {
        $this->db = $db;
        $this->amenity = new Amenity($db);
        $this->booking = new AmenityBooking($db);
    }

    public function getAllAmenities() {
        $stmt = $this->amenity->read();
        $arr = array();
        $arr["records"] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function getActiveAmenities() {
        $stmt = $this->amenity->readActive();
        $arr = array();
        $arr["records"] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function getAmenity($id) {
        $this->amenity->id = $id;
        $stmt = $this->amenity->readOne();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) return json_encode($row);
        return json_encode(array("message" => "Not found."));
    }

    public function createAmenity($data) {
        if(!empty($data->property_id) && !empty($data->name)) {
            $this->amenity->property_id = $data->property_id;
            $this->amenity->name = $data->name;
            $this->amenity->description = $data->description ?? '';
            $this->amenity->capacity = $data->capacity ?? 1;
            $this->amenity->location = $data->location ?? '';
            $this->amenity->image = $data->image ?? '';

            if($this->amenity->create()) {
                http_response_code(201);
                return json_encode(array("message" => "Amenity created."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to create."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data."));
        }
    }

    public function updateAmenity($data) {
        if(!empty($data->id)) {
            $this->amenity->id = $data->id;
            $this->amenity->property_id = $data->property_id;
            $this->amenity->name = $data->name;
            $this->amenity->description = $data->description ?? '';
            $this->amenity->capacity = $data->capacity ?? 1;
            $this->amenity->location = $data->location ?? '';
            $this->amenity->image = $data->image ?? '';
            $this->amenity->is_active = $data->is_active ?? 1;

            if($this->amenity->update()) {
                return json_encode(array("message" => "Amenity updated."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to update."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data."));
        }
    }

    public function deleteAmenity($id) {
        $this->amenity->id = $id;
        if($this->amenity->delete()) {
            return json_encode(array("message" => "Amenity deleted."));
        } else {
            http_response_code(503);
            return json_encode(array("message" => "Unable to delete."));
        }
    }

    public function getAllBookings() {
        $stmt = $this->booking->read();
        $arr = array();
        $arr["records"] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function getUserBookings($user_id) {
        $this->booking->user_id = $user_id;
        $stmt = $this->booking->readByUser();
        $arr = array();
        $arr["records"] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function createBooking($data) {
        if(!empty($data->amenity_id) && !empty($data->booking_date) && !empty($data->check_in_time) && !empty($data->check_out_time)) {
            $user = Auth::getUser();
            $this->booking->amenity_id = $data->amenity_id;
            $this->booking->user_id = $user['id'] ?? $data->user_id ?? 1;
            $this->booking->guest_name = $data->guest_name ?? ($user['name'] ?? 'Guest');
            $this->booking->booking_date = $data->booking_date;
            $this->booking->check_in_time = $data->check_in_time;
            $this->booking->check_out_time = $data->check_out_time;

            $result = $this->booking->create();
            if($result === true) {
                http_response_code(201);
                return json_encode(array("message" => "Booking confirmed."));
            } elseif($result === "conflict") {
                http_response_code(409);
                return json_encode(array("message" => "Time slot conflict. Please choose another time."));
            } elseif($result === "capacity") {
                http_response_code(409);
                return json_encode(array("message" => "Amenity is fully booked at this time slot. Please choose another time or date."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to create booking."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data."));
        }
    }

    public function updateBookingStatus($data) {
        if(!empty($data->id) && !empty($data->status)) {
            $this->booking->id = $data->id;
            $this->booking->status = $data->status;
            if($this->booking->updateStatus()) {
                return json_encode(array("message" => "Booking status updated."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to update."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data."));
        }
    }

    public function deleteBooking($id) {
        $this->booking->id = $id;
        if($this->booking->delete()) {
            return json_encode(array("message" => "Booking cancelled."));
        } else {
            http_response_code(503);
            return json_encode(array("message" => "Unable to delete."));
        }
    }

    public function getStats() {
        $amenityStats = $this->amenity->getStats();
        $bookingStats = $this->booking->getStats();
        return json_encode(array_merge($amenityStats, $bookingStats));
    }

    public function checkCapacity($amenity_id, $date) {
        $result = $this->booking->checkDateAvailability($amenity_id, $date);
        return json_encode($result);
    }
}
