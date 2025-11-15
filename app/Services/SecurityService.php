<?php 

namespace App\Services;


class SecurityService{

    protected static string $ENCRYPTION_KEY;
    protected static string $ENCRYPTION_METHOD;
    protected static string $ENCRYPTION_IV;

    public function __construct()
    {
        self::$ENCRYPTION_KEY = hash('sha256', env('ENCRYPTION_KEY'), true);
        self::$ENCRYPTION_METHOD = env('ENCRYPTION_METHOD');
        // self::$ENCRYPTION_IV = substr(hash('sha256', env('ENCRYPTION_IV')), 0, 16);
        self::$ENCRYPTION_IV = substr(hash('sha256', $this->generateRandomString()), 0, 16);
    }

    public function encrypt(string $payload) : string
    {
        $encrypted = openssl_encrypt(
                        $payload, 
                        self::$ENCRYPTION_METHOD, 
                        self::$ENCRYPTION_KEY, 
                        OPENSSL_RAW_DATA, 
                        self::$ENCRYPTION_IV
                    );

        $raw = self::$ENCRYPTION_IV . $encrypted;
        $output = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

        return $output;
    }

    public function decrypt(string $payload): string|false
    {
        $b64 = strtr($payload, '-_', '+/');
        $b64 .= str_repeat('=', 3 - (3 + strlen($b64)) % 4);

        $decoded = base64_decode($b64);
        
        $iv = substr($decoded, 0, 16);
        $ciphertext = substr($decoded, 16);

        return openssl_decrypt(
            $ciphertext,
            self::$ENCRYPTION_METHOD,
            self::$ENCRYPTION_KEY,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    private function generateRandomString($length = 16) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}