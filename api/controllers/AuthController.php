<?php
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../auth/JwtHandler.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthController {
    private $admin;

    public function __construct() {
        $database = new Database();
        $this->admin = new Admin($database->getConnection());
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['username']) || empty($data['password'])) {
            Response::send(false, "Invalid input");
        }

        $admin = $this->admin->login($data['username'], $data['password']);
        if ($admin) {
            $token = JwtHandler::generateToken(['id' => $admin['id'], 'username' => $admin['username']]);
            Response::send(true, "Login successful", ['token' => $token]);
        } else {
            Response::send(false, "Invalid credentials", null, 401);
        }
    }

    public function getProfile() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            Response::send(false, "Unauthorized", null, 401);
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $decoded = JwtHandler::validateToken($token);

        if (!$decoded) {
            Response::send(false, "Invalid token", null, 401);
        }

        $admin = $this->admin->getAdminById($decoded['id']);
        if ($admin) {
            Response::send(true, "Profile retrieved", [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'first_name' => $admin['first_name'],
                'last_name' => $admin['last_name']
            ]);
        } else {
            Response::send(false, "Admin not found", null, 404);
        }
    }
}
