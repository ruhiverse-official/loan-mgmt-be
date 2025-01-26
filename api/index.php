
<?php
require_once "config/Database.php";
require_once "controllers/AuthController.php";
require_once "middlewares/AuthMiddleware.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$method = $_SERVER['REQUEST_METHOD'];

$authController = new AuthController();

if ($uri === "/be/api/index.php/login" && $method === "POST") {
    $authController->login();
} else {
    echo json_encode(["status" => false, "message" => "Route not found"]);
}
