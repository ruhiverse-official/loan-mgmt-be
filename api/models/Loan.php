<?php

class Loan {
    private $conn;
    private $table = "loans";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (customer_name, customer_mobile, required_loan_amount, approved_loan_amount, status, referral_person_id, referral_commission_rate, account_person_id, account_commission_rate) 
                  VALUES (:customer_name, :customer_mobile, :required_loan_amount, :approved_loan_amount, :status, :referral_person_id, :referral_commission_rate, :account_person_id, :account_commission_rate)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":customer_name", $data['customer_name']);
        $stmt->bindParam(":customer_mobile", $data['customer_mobile']);
        $stmt->bindParam(":required_loan_amount", $data['required_loan_amount']);
        $stmt->bindParam(":approved_loan_amount", $data['approved_loan_amount']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":referral_person_id", $data['referral_person_id']);
        $stmt->bindParam(":referral_commission_rate", $data['referral_commission_rate']);
        $stmt->bindParam(":account_person_id", $data['account_person_id']);
        $stmt->bindParam(":account_commission_rate", $data['account_commission_rate']);

        return $stmt->execute();
    }

    // Get all loans
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a loan by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update a loan by ID
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET customer_name = :customer_name, 
                      customer_mobile = :customer_mobile, 
                      required_loan_amount = :required_loan_amount, 
                      status = :status 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":customer_name", $data['customer_name']);
        $stmt->bindParam(":customer_mobile", $data['customer_mobile']);
        $stmt->bindParam(":required_loan_amount", $data['required_loan_amount']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Delete a loan by ID
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Get total amount owed to referral/account
    public function getPendingCommission($person_id, $person_type) {
        $commission_field = ($person_type === 'Referral') ? 'referral_commission_rate' : 'account_commission_rate';

        // 1️⃣ Calculate total commission for approved loans
        $query = "SELECT SUM(approved_loan_amount * $commission_field / 100) AS total_commission
                  FROM " . $this->table . " 
                  WHERE status = 'Approved' 
                  AND " . ($person_type === 'Referral' ? 'referral_person_id' : 'account_person_id') . " = :person_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->execute();
        $commission_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_commission = $commission_result['total_commission'] ?? 0;

        // 2️⃣ Get total amount already paid
        $payment_query = "SELECT SUM(amount) AS total_paid 
                          FROM payments 
                          WHERE person_id = :person_id 
                          AND person_type = :person_type";

        $stmt = $this->conn->prepare($payment_query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->bindParam(":person_type", $person_type);
        $stmt->execute();
        $payment_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_paid = $payment_result['total_paid'] ?? 0;

        // 3️⃣ Calculate remaining amount
        $remaining_amount = $total_commission - $total_paid;

        return [
            'total_commission' => (float) $total_commission,
            'total_paid' => (float) $total_paid,
            'remaining_balance' => (float) $remaining_amount
        ];
    }
}
