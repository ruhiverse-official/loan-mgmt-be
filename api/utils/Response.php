<?php
class Response {
    public static function send($status, $message, $data = null, $http_code = 200) {
        // Set the HTTP response code
        http_response_code($http_code);

        // Start output buffering if not already started
        if (!ob_get_level()) {
            ob_start();
        }
        
        // Ensure headers are not already sent
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        // Clean buffer before sending JSON response
        ob_clean();
        
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);

        // Send output and stop execution
        ob_end_flush();
        exit();
    }
}
