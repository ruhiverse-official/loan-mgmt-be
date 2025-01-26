<?php
require_once __DIR__ . '/../models/Account.php';
require_once __DIR__ . '/../utils/Response.php';

class AccountController {
    private $accountModel;

    public function __construct() {
        $database = new Database();
        $this->accountModel = new Account($database->getConnection());
    }

    public function getAll() {
        $accounts = $this->accountModel->getAll();
        Response::send(true, 'Accounts retrieved successfully', $accounts);
    }

    public function getById($id) {
        $account = $this->accountModel->getById($id);
        if ($account) {
            Response::send(true, 'Account retrieved successfully', $account);
        } else {
            Response::send(false, 'Account not found');
        }
    }

    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);

        try {
            if ($this->accountModel->create($data)) {
                Response::send(true, 'Account created successfully');
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
        if ($this->accountModel->update($id, $data)) {
            Response::send(true, 'Account updated successfully');
        } else {
            Response::send(false, 'Failed to update account');
        }
    }

    public function delete($id) {
        if ($this->accountModel->delete($id)) {
            Response::send(true, 'Account deleted successfully');
        } else {
            Response::send(false, 'Failed to delete account');
        }
    }
}
