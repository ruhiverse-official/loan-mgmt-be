
<?php

class Admin {
    private $conn;
    private $table = "admin";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAdminById($id) {
        $query = "SELECT id, username, first_name, last_name FROM admin WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function login($username, $password) {
        $query = "SELECT * FROM {$this->table} WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }

    public function adminExists() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Create the first admin
    public function createFirstAdmin() {
        if (!$this->adminExists()) {
            $query = "INSERT INTO " . $this->table . " (username, password) VALUES (:username, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $password);

            $username = "admin";
            $password = password_hash("admin123", PASSWORD_DEFAULT); // Use hashed password for security

            if ($stmt->execute()) {
                echo "Admin created with username: 'admin' and password: 'admin123'.\n";
            } else {
                echo "Failed to create the first admin.\n";
            }
        }
    }
}
