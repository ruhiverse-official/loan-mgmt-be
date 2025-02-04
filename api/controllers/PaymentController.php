<?php
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../utils/Response.php';

class PaymentController {
    private $paymentModel;

    public function __construct() {
        $database = new Database();
        $this->paymentModel = new Payment($database->getConnection());
    }

    // Get all payments
    public function getAll() {
        $payments = $this->paymentModel->getAll();
        Response::send(true, "Payments retrieved successfully", $payments);
    }

    // Get payment by ID
    public function getById($id) {
        $payment = $this->paymentModel->getById($id);
        if ($payment) {
            Response::send(true, "Payment retrieved successfully", $payment);
        } else {
            Response::send(false, "Payment not found");
        }
    }

    // Create payment
    public function create() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['person_type']) || empty($data['person_id']) || empty($data['amount'])) {
            Response::send(false, "Invalid input data");
        }

        if ($this->paymentModel->create($data)) {
            Response::send(true, "Payment recorded successfully");
        } else {
            Response::send(false, "Failed to record payment");
        }
    }

    // Update payment
    public function update($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->paymentModel->update($id, $data)) {
            Response::send(true, "Payment updated successfully");
        } else {
            Response::send(false, "Failed to update payment");
        }
    }

    // Delete payment
    public function delete($id) {
        if ($this->paymentModel->delete($id)) {
            Response::send(true, "Payment deleted successfully");
        } else {
            Response::send(false, "Failed to delete payment");
        }
    }

    // Get total payment made by referral/account
    public function getTotalPaidAndPendingByPersonId($person_id, $person_type) {
        $payment = $this->paymentModel->getTotalPaidAndPendingByPersonId($person_id, $person_type);
        Response::send(true, "Total payment retrieved successfully", $payment);
    }

    // Get all payments by referral/account
    public function getPaymentsByPerson($person_id, $person_type) {
        $payments = $this->paymentModel->getPaymentsByPerson($person_id, $person_type);
        Response::send(true, "Payments retrieved successfully", $payments);
    }
    
}
