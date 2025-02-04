<?php

class Payment {
    private $conn;
    private $table = "payments";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all payments
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get payment by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new payment
    public function create($data) {
        try {
            $column = $this->getColumnByPersonType($data['person_type']);

            if (!$column) {
                throw new Exception("Invalid person type");
            }

            $query = "INSERT INTO " . $this->table . " (person_type, $column, amount, remarks) 
                      VALUES (:person_type, :person_id, :amount, :remarks)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":person_type", $data['person_type']);
            $stmt->bindParam(":person_id", $data['person_id']);
            $stmt->bindParam(":amount", $data['amount']);
            $stmt->bindParam(":remarks", $data['remarks']);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Update payment by ID
    public function update($id, $data) {
        try {
            $column = $this->getColumnByPersonType($data['person_type']);

            if (!$column) {
                throw new Exception("Invalid person type");
            }

            $query = "UPDATE " . $this->table . " 
                      SET person_type = :person_type, $column = :person_id, amount = :amount, remarks = :remarks 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":person_type", $data['person_type']);
            $stmt->bindParam(":person_id", $data['person_id']);
            $stmt->bindParam(":amount", $data['amount']);
            $stmt->bindParam(":remarks", $data['remarks']);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    // Delete payment by ID
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Get total amount paid by referral/account
    public function getTotalPaidAndPendingByPersonId($person_id, $person_type) {
        $column = $this->getColumnByPersonType($person_type);
        $commission_column = ($person_type === 'Referral') ? 'referral_commission_rate' : 'account_commission_rate';
        $loan_column = ($person_type === 'Referral') ? 'referral_person_id' : 'account_person_id';
    
        if (!$column) {
            throw new Exception("Invalid person type");
        }
    
        // 1️⃣ Get Total Paid Amount from `payments`
        $query = "SELECT SUM(amount) as total_paid FROM payments WHERE $column = :person_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->execute();
        $payment_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_paid = $payment_result['total_paid'] ?? 0;
    
        // 2️⃣ Get Total Commission from `loans`
        $loan_query = "SELECT SUM(approved_loan_amount * $commission_column / 100) AS total_commission 
                       FROM loans WHERE status = 'Approved' AND $loan_column = :person_id";
        $stmt = $this->conn->prepare($loan_query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->execute();
        $commission_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_commission = $commission_result['total_commission'] ?? 0;
    
        // 3️⃣ Calculate Total Pending Amount
        $total_pending = $total_commission - $total_paid;
    
        return [
            'total_paid' => (float) $total_paid,
            'total_pending' => (float) $total_pending
        ];
    }
    

    // Get payment history by referral/account ID
    public function getPaymentsByPerson($person_id, $person_type) {
        $column = $this->getColumnByPersonType($person_type);

        if (!$column) {
            throw new Exception("Invalid person type");
        }

        $query = "SELECT * FROM " . $this->table . " 
                  WHERE $column = :person_id
                  ORDER BY paid_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper function to get correct column based on person_type
    private function getColumnByPersonType($person_type) {
        $map = [
            'Referral' => 'referral_id',
            'Account' => 'account_id'
        ];
        return $map[$person_type] ?? null;
    }
}
