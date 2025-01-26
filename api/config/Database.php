<?php

require_once __DIR__ . '/../../vendor/autoload.php'; // Adjust path based on your project structure

use Dotenv\Dotenv;

class Database {
    private $conn;

    public function __construct() {
        // Load .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS']
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            die("Database error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
