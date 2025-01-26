<?php
require_once __DIR__ .  '/../models/Referral.php';

class ReferralController {
    private $referralModel;

    public function __construct() {
        $database = new Database();
        $this->referralModel = new Referral($database->getConnection());
    }

    public function getAll() {
        $referrals = $this->referralModel->getAll();
        echo json_encode(["status" => true, "data" => $referrals]);
    }

    public function getById($id) {
        $referral = $this->referralModel->getById($id);
        if ($referral) {
            echo json_encode(["status" => true, "data" => $referral]);
        } else {
            echo json_encode(["status" => false, "message" => "Referral not found"]);
        }
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->referralModel->create($data)) {
            echo json_encode(["status" => true, "message" => "Referral created successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to create referral"]);
        }
    }

    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->referralModel->update($id, $data)) {
            echo json_encode(["status" => true, "message" => "Referral updated successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to update referral"]);
        }
    }

    public function delete($id) {
        if ($this->referralModel->delete($id)) {
            echo json_encode(["status" => true, "message" => "Referral deleted successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to delete referral"]);
        }
    }
}
