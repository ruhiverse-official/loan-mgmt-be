
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
            Response::send(false, "Invalid credentials");
        }
    }
}
