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
}
