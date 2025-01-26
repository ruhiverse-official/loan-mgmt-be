<?php
require_once __DIR__ . '/../models/Loan.php';

class LoanController {
    private $loanModel;

    public function __construct() {
        $database = new Database();
        $this->loanModel = new Loan($database->getConnection());
    }

    // List all loans
    public function getAllLoans() {
        $loans = $this->loanModel->getAll();
        echo json_encode(["status" => true, "data" => $loans]);
    }

    // Get a loan by ID
    public function getLoanById($id) {
        $loan = $this->loanModel->getById($id);
        if ($loan) {
            echo json_encode(["status" => true, "data" => $loan]);
        } else {
            echo json_encode(["status" => false, "message" => "Loan not found"]);
        }
    }

    // Update a loan by ID
    public function updateLoan($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->loanModel->update($id, $data)) {
            echo json_encode(["status" => true, "message" => "Loan updated successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to update loan"]);
        }
    }

    // Delete a loan by ID
    public function deleteLoan($id) {
        if ($this->loanModel->delete($id)) {
            echo json_encode(["status" => true, "message" => "Loan deleted successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Failed to delete loan"]);
        }
    }
}
