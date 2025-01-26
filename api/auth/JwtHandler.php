
<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler {
    private static $secret_key = "your_secret_key";
    private static $algorithm = "HS256";

    public static function generateToken($data) {
        $issuedAt = time();
        $expire = $issuedAt + 3600;

        $payload = array_merge($data, ['iat' => $issuedAt, 'exp' => $expire]);
        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function validateToken($token) {
        try {
            return (array) JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
        } catch (Exception $e) {
            return false;
        }
    }
}
