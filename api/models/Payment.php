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

    // Get total amount paid and pending by referral/account
    public function getTotalPaidAndPendingByPersonId($person_id, $person_type) {
        $column = $this->getColumnByPersonType($person_type);
        $commission_column = ($person_type === 'Referral') ? 'referral_commission' : 'account_commission';
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
        $loan_query = "SELECT SUM($commission_column) AS total_commission 
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

    public function getProfitSummary() {
        try {
            // 1️⃣ Get Total Commission Earned from loans
            $commission_query = "SELECT SUM(commission) AS total_commission_earned 
                                 FROM loans WHERE status = 'Approved'";
            $stmt = $this->conn->prepare($commission_query);
            $stmt->execute();
            $commission_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_commission_earned = $commission_result['total_commission_earned'] ?? 0;
    
            // 2️⃣ Get Total Commission Given from payments
            $given_query = "SELECT SUM(amount) AS total_commission_given FROM payments";
            $stmt = $this->conn->prepare($given_query);
            $stmt->execute();
            $given_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_commission_given = $given_result['total_commission_given'] ?? 0;
    
            // 3️⃣ Get Total Expenses
            $expense_query = "SELECT SUM(amount) AS total_expense FROM expenses";
            $stmt = $this->conn->prepare($expense_query);
            $stmt->execute();
            $expense_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_expense = $expense_result['total_expense'] ?? 0;
    
            // 4️⃣ Get Pending Commission to Give (Commission Earned - Commission Already Paid)
            $pending_query = "SELECT 
                                SUM(loans.referral_commission + loans.account_commission) AS total_commission, 
                                (SELECT IFNULL(SUM(amount), 0) FROM payments) AS total_paid,
                                (SUM(loans.referral_commission + loans.account_commission) - 
                                 (SELECT IFNULL(SUM(amount), 0) FROM payments)) AS pending_commission
                              FROM loans
                              WHERE loans.status = 'Approved'";
    
            $stmt = $this->conn->prepare($pending_query);
            $stmt->execute();
            $pending_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_commission_to_give = $pending_result['total_commission'] ?? 0;
            $total_paid = $pending_result['total_paid'] ?? 0;
            $pending_commission = max($total_commission_to_give - $total_paid, 0); // Ensure it doesn't go negative
    
            // 5️⃣ Calculate Net Profit
            $net_profit = $total_commission_earned - ($total_commission_given + $total_expense);
    
            return [
                'total_commission_earned' => (float) $total_commission_earned, // Total earned from approved loans
                'total_commission_given' => (float) $total_commission_given,   // Total paid out so far
                'total_expense' => (float) $total_expense,                     // Total operational expenses
                'pending_commission_to_give' => (float) $pending_commission,   // Remaining commission yet to be paid
                'net_profit' => (float) $net_profit                            // Final profit after all expenses
            ];
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }    
    
}
