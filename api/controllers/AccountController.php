<?php
require_once __DIR__ . '/../models/Account.php';

class AccountController {
    private $accountModel;

    public function __construct() {
        $database = new Database();
        $this->accountModel = new Account($database->getConnection());
    }

    public function getAll() {
        $accounts = $this->accountModel->getAll();
        echo json_encode(["status" => true, "data" => $accounts]);
    }

    public function getById($id) {
        $account = $this->accountModel->getById($id);
        if ($account) {
            echo json_encode(["status" => true, "data" => $account]);
        } else {
            echo json_encode(["status" => false, "message" => "Account not found"]);
        }
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);
    
        try {
            if ($this->accountModel->create($data)) {
                echo json_encode(["status" => true, "message" => "Account created successfully"]);
            }
        } catch (Exception $e) {
            // Check for specific errors, like duplicate entry
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                echo json_encode([
                    "status" => false,
                    "message" => "Mobile number already exists",
                    "error" => $e->getMessage()
                ]);
            } else {
                echo json_encode([
                    "status" => false,
                    "message" => "Failed to create account",
                    "error" => $e->getMessage()
                ]);
            }
        }
    }    

    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->accountModel->update($id, $data)) {
            echo json_encode(["status" => true, "message" => "Account updated successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to update account"]);
        }
    }

    public function delete($id) {
        if ($this->accountModel->delete($id)) {
            echo json_encode(["status" => true, "message" => "Account deleted successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to delete account"]);
        }
    }
}
