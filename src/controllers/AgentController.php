<?php
include_once __DIR__ . '/../models/Agent.php';
include_once __DIR__ . '/../config/auth.php';

class AgentController {
    private $db;
    private $agent;

    public function __construct($db) {
        $this->db = $db;
        $this->agent = new Agent($db);
    }

    public function getAll($params = []) {
        return $this->agent->readPaginated($params);
    }

    public function getOne($id) {
        $this->agent->id = $id;
        $stmt = $this->agent->readOne();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) return json_encode($row);
        http_response_code(404);
        return json_encode(array("message" => "Not found."));
    }

    public function create($data) {
        if(!empty($data->name)) {
            $this->agent->name = $data->name;
            $this->agent->title = $data->title ?? '';
            $this->agent->bio = $data->bio ?? '';
            $this->agent->image_url = $data->image_url ?? '';

            if($this->agent->create()) {
                http_response_code(201);
                return json_encode(array("message" => "Agent created."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to create agent."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Name is required."));
        }
    }

    public function update($data) {
        if(!empty($data->id)) {
            $this->agent->id = $data->id;
            $this->agent->name = $data->name ?? '';
            $this->agent->title = $data->title ?? '';
            $this->agent->bio = $data->bio ?? '';
            $this->agent->image_url = $data->image_url ?? '';

            if($this->agent->update()) {
                return json_encode(array("message" => "Agent updated."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to update agent."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "ID is required."));
        }
    }

    public function delete($id) {
        $this->agent->id = $id;
        if($this->agent->delete()) {
            return json_encode(array("message" => "Agent deleted."));
        } else {
            http_response_code(503);
            return json_encode(array("message" => "Unable to delete agent."));
        }
    }
}
