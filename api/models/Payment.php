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
            $query = "INSERT INTO " . $this->table . " (person_type, person_id, amount, remarks) 
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
            $query = "UPDATE " . $this->table . " 
                      SET person_type = :person_type, person_id = :person_id, amount = :amount, remarks = :remarks 
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
    public function getTotalPaidByPersonId($person_id, $person_type) {
        $query = "SELECT SUM(amount) as total_paid FROM " . $this->table . " 
                  WHERE person_id = :person_id AND person_type = :person_type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->bindParam(":person_type", $person_type);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get payment history by referral/account ID
    public function getPaymentsByPerson($person_id, $person_type) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE person_id = :person_id AND person_type = :person_type
                  ORDER BY paid_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->bindParam(":person_type", $person_type);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
