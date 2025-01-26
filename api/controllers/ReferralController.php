<?php
require_once __DIR__ . '/../models/Referral.php';
require_once __DIR__ . '/../utils/Response.php';

class ReferralController {
    private $referralModel;

    public function __construct() {
        $database = new Database();
        $this->referralModel = new Referral($database->getConnection());
    }

    public function getAll() {
        $referrals = $this->referralModel->getAll();
        Response::send(true, 'Referrals retrieved successfully', $referrals);
    }

    public function getById($id) {
        $referral = $this->referralModel->getById($id);
        if ($referral) {
            Response::send(true, 'Referral retrieved successfully', $referral);
        } else {
            Response::send(false, 'Referral not found');
        }
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        try {
            if ($this->referralModel->create($data)) {
                Response::send(true, 'Referral created successfully');
            }
        } catch (Exception $e) {
            // Check for specific errors, like duplicate entry
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                Response::send(false, 'Mobile number already exists', ['error' => $e->getMessage()]);
            } else {
                Response::send(false, 'Failed to create account', ['error' => $e->getMessage()]);
            }
        }
    }

    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->referralModel->update($id, $data)) {
            Response::send(true, 'Referral updated successfully');
        } else {
            Response::send(false, 'Failed to update referral');
        }
    }

    public function delete($id) {
        if ($this->referralModel->delete($id)) {
            Response::send(true, 'Referral deleted successfully');
        } else {
            Response::send(false, 'Failed to delete referral');
        }
    }
}
