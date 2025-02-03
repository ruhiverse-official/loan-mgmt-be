<?php

class Expense {
    private $conn;
    private $table = "expenses";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT e.id, e.amount, e.description, e.expense_date, c.name AS category 
                  FROM " . $this->table . " e 
                  JOIN expense_categories c ON e.category_id = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " (category_id, amount, description, expense_date) 
                      VALUES (:category_id, :amount, :description, :expense_date)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":category_id", $data['category_id']);
            $stmt->bindParam(":amount", $data['amount']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":expense_date", $data['expense_date']);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
