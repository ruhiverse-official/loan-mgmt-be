<?php
require_once __DIR__ . '/../auth/JwtHandler.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware {
    public static function checkAuth() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        if (!$token || !JwtHandler::validateToken($token)) {
            Response::send(false, "Unauthorized access");
        }
    }
}
