
<?php

class Response {
    public static function send($status, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
        exit();
    }
}
