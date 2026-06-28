<?php
include_once __DIR__ . '/../models/Testimonial.php';
include_once __DIR__ . '/../config/auth.php';

class TestimonialController {
    private $db;
    private $testimonial;

    public function __construct($db) {
        $this->db = $db;
        $this->testimonial = new Testimonial($db);
    }

    public function getAll($params = []) {
        return $this->testimonial->readPaginated($params);
    }

    public function getOne($id) {
        $this->testimonial->id = $id;
        $stmt = $this->testimonial->readOne();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) return json_encode($row);
        http_response_code(404);
        return json_encode(array("message" => "Not found."));
    }

    public function create($data) {
        if(!empty($data->name) && !empty($data->content)) {
            $this->testimonial->name = $data->name;
            $this->testimonial->location = $data->location ?? '';
            $this->testimonial->content = $data->content;
            $this->testimonial->rating = $data->rating ?? 5;
            $this->testimonial->avatar_url = $data->avatar_url ?? '';

            if($this->testimonial->create()) {
                http_response_code(201);
                return json_encode(array("message" => "Testimonial created."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to create testimonial."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Name and content are required."));
        }
    }

    public function update($data) {
        if(!empty($data->id)) {
            $this->testimonial->id = $data->id;
            $this->testimonial->name = $data->name ?? '';
            $this->testimonial->location = $data->location ?? '';
            $this->testimonial->content = $data->content ?? '';
            $this->testimonial->rating = $data->rating ?? 5;
            $this->testimonial->avatar_url = $data->avatar_url ?? '';

            if($this->testimonial->update()) {
                return json_encode(array("message" => "Testimonial updated."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to update testimonial."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "ID is required."));
        }
    }

    public function delete($id) {
        $this->testimonial->id = $id;
        if($this->testimonial->delete()) {
            return json_encode(array("message" => "Testimonial deleted."));
        } else {
            http_response_code(503);
            return json_encode(array("message" => "Unable to delete testimonial."));
        }
    }
}
