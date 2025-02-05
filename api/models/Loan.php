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
                      approved_loan_amount = :approved_loan_amount, 
                      status = :status, 
                      referral_person_id = :referral_person_id, 
                      referral_commission_rate = :referral_commission_rate, 
                      account_person_id = :account_person_id, 
                      account_commission_rate = :account_commission_rate
                  WHERE id = :id";
    
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
        $commission_column = ($person_type === 'Referral') ? 'referral_commission_rate' : 'account_commission_rate';
        $payment_column = ($person_type === 'Referral') ? 'referral_id' : 'account_id';

        // Calculate total commission for approved loans
        $query = "SELECT SUM(approved_loan_amount * $commission_column / 100) AS total_commission
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
        $commission_column = ($person_type === 'Referral') ? 'referral_commission_rate' : 'account_commission_rate';
        $payment_column = ($person_type === 'Referral') ? 'referral_id' : 'account_id';

        // Get all persons
        $query = "SELECT id, name FROM " . $table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($persons as &$person) {
            $person_id = $person['id'];

            // Get total commission from approved loans
            $loan_query = "SELECT SUM(approved_loan_amount * $commission_column / 100) AS total_commission
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
