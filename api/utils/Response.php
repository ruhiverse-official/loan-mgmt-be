<?php

class Response {
    public static function send($status, $message, $data = null) {
        // Clear any existing output buffer
        if (ob_get_length()) {
            ob_clean();
        }

        // Set the Content-Type header
        header('Content-Type: application/json');

        // Output the JSON response
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit();
    }
}
