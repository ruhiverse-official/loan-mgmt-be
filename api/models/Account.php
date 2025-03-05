<?php

class Account {
    private $conn;
    private $table = "account_person";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getCommissionDetails($id, $personColumn, $commissionColumn) {
        // Map column correctly based on type
        $paymentColumn = ($personColumn === 'account_person_id') ? 'account_id' : 'referral_id';
        $personType = ($personColumn === 'account_person_id') ? 'Account' : 'Referral';
    
        $query = "SELECT
                    l.id AS loan_id,
                    l.customer_name, 
                    l.required_loan_amount AS loan_amount, 
                    l.$commissionColumn AS fees, 
                    COALESCE(SUM(p.amount), 0) AS paid_fees,
                    (l.$commissionColumn - COALESCE(SUM(p.amount), 0)) AS balance_fees
                  FROM loans l
                  LEFT JOIN payments p 
                    ON l.id = p.loan_id 
                    AND p.person_type = :personType 
                    AND p.$paymentColumn = :id
                  WHERE l.$personColumn = :id
                  GROUP BY l.id, l.customer_name, l.required_loan_amount, l.$commissionColumn";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":personType", $personType, PDO::PARAM_STR);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    // Get all account persons
    public function getAll() {
        $query = "SELECT 
                    a.*, 
                    COALESCE(SUM(p.amount), 0) AS total_fees, 
                    COALESCE(SUM(CASE WHEN p.paid_at IS NOT NULL THEN p.amount ELSE 0 END), 0) AS paid_fees
                  FROM account_person a
                  LEFT JOIN payments p 
                    ON a.id = p.account_id 
                    AND p.person_type = 'Account' 
                    AND p.account_id IS NOT NULL
                  GROUP BY a.id, a.name, a.mobile, a.email, a.created_at, a.updated_at";
    
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    // Get an account person by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create a new account person
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " (name, mobile, email) VALUES (:name, :mobile, :email)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":mobile", $data['mobile']);
        $stmt->bindParam(":email", $data['email']);

        return $stmt->execute();
    }

    // Update an account person by ID
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET name = :name, mobile = :mobile, email = :email WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $data['name']);
        $stmt->bindParam(":mobile", $data['mobile']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Delete an account person by ID
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
