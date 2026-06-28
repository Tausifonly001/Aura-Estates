<?php
include_once __DIR__ . '/../models/Inquiry.php';

class InquiryController {
    private $db;
    private $inquiry;

    public function __construct($db) {
        $this->db = $db;
        $this->inquiry = new Inquiry($db);
    }

    public function create($data) {
        // Validate inputs
        if(
            !empty($data->property_id) &&
            !empty($data->name) &&
            !empty($data->email) &&
            !empty($data->message)
        ){
            // Validate email format
            if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400); // Bad Request
                return json_encode(array("message" => "Invalid email format."));
            }

            $this->inquiry->property_id = $data->property_id;
            $this->inquiry->name = $data->name;
            $this->inquiry->email = $data->email;
            $this->inquiry->message = $data->message;

            if($this->inquiry->create()){
                http_response_code(201); // Created
                return json_encode(array("message" => "Inquiry was sent."));
            } else {
                http_response_code(503); // Service Unavailable
                return json_encode(array("message" => "Unable to send inquiry."));
            }
        } else {
            http_response_code(400); // Bad Request
            return json_encode(array("message" => "Unable to send inquiry. Data is incomplete."));
        }
    }
}
?>
