<?php

class Account {
    private $conn;
    private $table = "account_person";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all account persons
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
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
