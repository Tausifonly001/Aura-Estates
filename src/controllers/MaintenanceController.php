<?php
include_once __DIR__ . '/../models/MaintenanceRequest.php';
include_once __DIR__ . '/../config/auth.php';

class MaintenanceController {
    private $db;
    private $maintenance;

    public function __construct($db) {
        $this->db = $db;
        $this->maintenance = new MaintenanceRequest($db);
    }

    public function getAll() {
        $stmt = $this->maintenance->read();
        $num = $stmt->rowCount();
        $arr = array();
        $arr["records"] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function getOne($id) {
        $this->maintenance->id = $id;
        $stmt = $this->maintenance->readOne();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            return json_encode($row);
        }
        return json_encode(array("message" => "Not found."));
    }

    public function getByEmail($email) {
        $this->maintenance->tenant_email = $email;
        $stmt = $this->maintenance->readByEmail();
        $arr = array();
        $arr["records"] = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function create($data) {
        $desc = !empty($data->issue_description) ? $data->issue_description : ($data->description ?? '');
        if(!empty($data->property_id) && !empty($desc)) {
            $user = Auth::getUser();
            $this->maintenance->property_id = $data->property_id;
            $this->maintenance->user_id = $data->user_id ?? ($user['id'] ?? null);
            $this->maintenance->tenant_name = $data->tenant_name ?? ($user['name'] ?? 'Anonymous');
            $this->maintenance->tenant_email = $data->tenant_email ?? ($user['email'] ?? 'anonymous@aura.com');
            $this->maintenance->issue_description = $desc;
            $this->maintenance->priority = !empty($data->priority) ? $data->priority : 'medium';

            if($this->maintenance->create()) {
                http_response_code(201);
                return json_encode(array("message" => "Maintenance request created.", "id" => $this->maintenance->id));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to create request."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data."));
        }
    }

    public function updateStatus($data) {
        if(!empty($data->id) && !empty($data->status)) {
            $this->maintenance->id = $data->id;
            $this->maintenance->status = $data->status;
            $this->maintenance->resolved_at = ($data->status == 'completed') ? date('Y-m-d H:i:s') : null;

            if($this->maintenance->update()) {
                return json_encode(array("message" => "Status updated."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to update."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data."));
        }
    }

    public function getByUser($user_id) {
        $stmt = $this->db->prepare("SELECT m.*, p.title as property_title, u.name as assigned_name
            FROM maintenance_requests m
            LEFT JOIN properties p ON m.property_id = p.id
            LEFT JOIN users u ON m.assigned_to = u.id
            WHERE m.user_id = ?
            ORDER BY m.created_at DESC");
        $stmt->execute([$user_id]);
        $arr = array("records" => array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }

    public function delete($id) {
        $this->maintenance->id = $id;
        if($this->maintenance->delete()) {
            return json_encode(array("message" => "Request deleted."));
        } else {
            http_response_code(503);
            return json_encode(array("message" => "Unable to delete."));
        }
    }

    public function getStats() {
        return json_encode($this->maintenance->getStats());
    }

    public function assignStaff($data) {
        if(!empty($data->id)) {
            $this->maintenance->id = $data->id;
            $this->maintenance->assigned_to = $data->assigned_to ?? null;
            if($this->maintenance->assign()) {
                return json_encode(array("message" => "Staff assigned."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Unable to assign."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Incomplete data."));
        }
    }

    public function getStaffList() {
        $stmt = $this->maintenance->getStaffList();
        $arr = array("records" => array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($arr["records"], $row);
        }
        return json_encode($arr);
    }
}
