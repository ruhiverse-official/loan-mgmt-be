<?php
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../utils/Response.php';

class LoanController {
    private $loanModel;

    public function __construct() {
        $database = new Database();
        $this->loanModel = new Loan($database->getConnection());
    }

    // Create loan
    public function createLoan() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['customer_name']) || empty($data['customer_mobile']) || empty($data['required_loan_amount'])) {
            Response::send(false, "Invalid input data");
        }

        $data['status'] = $data['status'] ?? 'Pending';
        $data['referral_person_id'] = $data['referral_person_id'] ?? null;
        $data['referral_commission_rate'] = $data['referral_commission_rate'] ?? 0;
        $data['account_person_id'] = $data['account_person_id'] ?? null;
        $data['account_commission_rate'] = $data['account_commission_rate'] ?? 0;

        $result = $this->loanModel->create($data);

        if ($result) {
            Response::send(true, "Loan created successfully");
        } else {
            Response::send(false, "Failed to create loan");
        }
    }

    // List all loans
    public function getAllLoans() {
        $loans = $this->loanModel->getAll();
        Response::send(true, "Loans retrieved successfully", $loans);
    }

    // Get a loan by ID
    public function getLoanById($id) {
        $loan = $this->loanModel->getById($id);
        if ($loan) {
            Response::send(true, "Loan retrieved successfully", $loan);
        } else {
            Response::send(false, "Loan not found");
        }
    }

    // Update a loan by ID
    public function updateLoan($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if ($this->loanModel->update($id, $data)) {
            Response::send(true, "Loan updated successfully");
        } else {
            Response::send(false, "Failed to update loan");
        }
    }

    // Delete a loan by ID
    public function deleteLoan($id) {
        if ($this->loanModel->delete($id)) {
            Response::send(true, "Loan deleted successfully");
        } else {
            Response::send(false, "Failed to delete loan");
        }
    }

    // Get pending commission amount for a Referral or Account person
    public function getPendingCommission($person_type, $person_id) {
        if (!in_array($person_type, ['Referral', 'Account'])) {
            Response::send(false, "Invalid person type. Must be 'Referral' or 'Account'.");
        }

        $pending_commission = $this->loanModel->getPendingCommission($person_id, $person_type);
        Response::send(true, "Pending commission retrieved successfully", $pending_commission);
    }
}
