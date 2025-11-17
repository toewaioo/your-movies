<?php

namespace App\Helpers;

class SecureStream
{
    private static $secret = "MY_SUPER_SECRET_KEY_32CHARS";

    public static function encryptUrl($realUrl)
    {
        $payload = json_encode(["url" => $realUrl]);

        $encrypted = openssl_encrypt(
            $payload,
            "AES-256-CBC",
            self::$secret,
            0,
            substr(self::$secret, 0, 16)
        );

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    public static function decryptUrl($token)
    {
        $data = base64_decode(strtr($token, '-_', '+/'));

        $decrypted = openssl_decrypt(
            $data,
            "AES-256-CBC",
            self::$secret,
            0,
            substr(self::$secret, 0, 16)
        );

        $json = json_decode($decrypted, true);
        return $json["url"] ?? null;
    }
}
