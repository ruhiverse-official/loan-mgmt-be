<?php

class Loan {
    private $conn;
    private $table = "loans";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (customer_name, customer_mobile, bank_name, required_loan_amount, status, 
                   referral_person_id, referral_commission, account_person_id, account_commission, commission) 
                  VALUES (:customer_name, :customer_mobile, :bank_name, :required_loan_amount, :status, 
                          :referral_person_id, :referral_commission, :account_person_id, :account_commission, :commission)";
        
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":customer_name", $data['customer_name']);
        $stmt->bindParam(":customer_mobile", $data['customer_mobile']);
        $stmt->bindParam(":bank_name", $data['bank_name']);
        $stmt->bindParam(":required_loan_amount", $data['required_loan_amount']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":referral_person_id", $data['referral_person_id']);
        $stmt->bindParam(":referral_commission", $data['referral_commission']);
        $stmt->bindParam(":account_person_id", $data['account_person_id']);
        $stmt->bindParam(":account_commission", $data['account_commission']);
        $stmt->bindParam(":commission", $data['commission']);
    
        return $stmt->execute();
    }    

    // Get all loans
    public function getAll() {
        $query = "SELECT loans.*, 
                         referral_person.name AS referral_name, 
                         account_person.name AS accountant_name,
                         -- Total Paid by Referral
                         COALESCE(SUM(CASE WHEN payments.person_type = 'Referral' THEN payments.amount ELSE 0 END), 0) AS total_paid_referral,
                         -- Total Paid by Accountant
                         COALESCE(SUM(CASE WHEN payments.person_type = 'Account' THEN payments.amount ELSE 0 END), 0) AS total_paid_account,
                         -- Total Remaining for Referral
                         (loans.referral_commission - 
                          COALESCE(SUM(CASE WHEN payments.person_type = 'Referral' THEN payments.amount ELSE 0 END), 0)) AS total_remaining_referral,
                         -- Total Remaining for Accountant
                         (loans.account_commission - 
                          COALESCE(SUM(CASE WHEN payments.person_type = 'Account' THEN payments.amount ELSE 0 END), 0)) AS total_remaining_account
                  FROM " . $this->table . "
                  LEFT JOIN referral_person ON loans.referral_person_id = referral_person.id
                  LEFT JOIN account_person ON loans.account_person_id = account_person.id
                  LEFT JOIN payments ON loans.id = payments.loan_id
                  GROUP BY loans.id";
    
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
                      bank_name = :bank_name, 
                      required_loan_amount = :required_loan_amount, 
                      status = :status, 
                      referral_person_id = :referral_person_id, 
                      referral_commission = :referral_commission, 
                      account_person_id = :account_person_id, 
                      account_commission = :account_commission, 
                      commission = :commission,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
    
        $stmt->bindParam(":customer_name", $data['customer_name']);
        $stmt->bindParam(":customer_mobile", $data['customer_mobile']);
        $stmt->bindParam(":bank_name", $data['bank_name']);
        $stmt->bindParam(":required_loan_amount", $data['required_loan_amount']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":referral_person_id", $data['referral_person_id']);
        $stmt->bindParam(":referral_commission", $data['referral_commission']);
        $stmt->bindParam(":account_person_id", $data['account_person_id']);
        $stmt->bindParam(":account_commission", $data['account_commission']);
        $stmt->bindParam(":commission", $data['commission']);
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
        $person_column = ($person_type === 'Referral') ? 'referral_person_id' : 'account_person_id';
        $commission_column = ($person_type === 'Referral') ? 'referral_commission' : 'account_commission';
        $payment_column = ($person_type === 'Referral') ? 'referral_id' : 'account_id';
    
        // Fetch total commission for approved loans
        $query = "SELECT SUM($commission_column) AS total_commission
                  FROM " . $this->table . " 
                  WHERE status = 'Approved' 
                  AND $person_column = :person_id";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->execute();
        $commission_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_commission = $commission_result['total_commission'] ?? 0;
    
        // Get total amount already paid
        $payment_query = "SELECT SUM(amount) AS total_paid 
                          FROM payments 
                          WHERE $payment_column = :person_id";
    
        $stmt = $this->conn->prepare($payment_query);
        $stmt->bindParam(":person_id", $person_id);
        $stmt->execute();
        $payment_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_paid = $payment_result['total_paid'] ?? 0;
    
        // Calculate remaining amount
        $remaining_amount = $total_commission - $total_paid;
    
        return [
            'total_commission' => (float) $total_commission,
            'total_paid' => (float) $total_paid,
            'remaining_balance' => (float) $remaining_amount
        ];
    }    

    // Get all referral/account persons with pending commission
    public function getAllPersonsWithPendingAmounts($person_type) {
        $table_name = ($person_type === 'Referral') ? 'referral_person' : 'account_person';
        $person_column = ($person_type === 'Referral') ? 'referral_person_id' : 'account_person_id';
        $commission_column = ($person_type === 'Referral') ? 'referral_commission' : 'account_commission';
        $payment_column = ($person_type === 'Referral') ? 'referral_id' : 'account_id';
    
        // Get all persons
        $query = "SELECT id, name FROM " . $table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        foreach ($persons as &$person) {
            $person_id = $person['id'];
    
            // Get total commission from approved loans
            $loan_query = "SELECT SUM($commission_column) AS total_commission
                           FROM " . $this->table . " 
                           WHERE status = 'Approved' 
                           AND $person_column = :person_id";
    
            $stmt = $this->conn->prepare($loan_query);
            $stmt->bindParam(":person_id", $person_id);
            $stmt->execute();
            $commission_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $person['total_commission'] = $commission_result['total_commission'] ?? 0;
    
            // Get total amount already paid
            $payment_query = "SELECT SUM(amount) AS total_paid FROM payments 
                              WHERE $payment_column = :person_id";
    
            $stmt = $this->conn->prepare($payment_query);
            $stmt->bindParam(":person_id", $person_id);
            $stmt->execute();
            $payment_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $person['total_paid'] = $payment_result['total_paid'] ?? 0;
    
            // Calculate remaining amount
            $person['remaining_balance'] = $person['total_commission'] - $person['total_paid'];
        }
    
        return $persons;
    }    
}
