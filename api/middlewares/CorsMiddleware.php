<?php

class CorsMiddleware {
    public static function handle() {
        // Allow all origins (change "*" to specific domain if needed)
        header("Access-Control-Allow-Origin: *");
        // Allow specific HTTP methods
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        // Allow specific headers
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
}
