<?php
namespace App\Lib;

class FileCrypto
{
    public static function getKey(): string
    {
        $key = getenv('ENCRYPTION_KEY') ?: ($_ENV['ENCRYPTION_KEY'] ?? '');
        return str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7)) : $key;
    }

    public static function encrypt(string $source, string $dest): bool
    {
        $data = file_get_contents($source);
        $iv = random_bytes(16);
        $cipher = openssl_encrypt($data, 'aes-256-cbc', self::getKey(), OPENSSL_RAW_DATA, $iv);
        return file_put_contents($dest, $iv . $cipher) !== false;
    }

    public static function decrypt(string $source, string $dest): bool
    {
        $data = file_get_contents($source);
        $iv = substr($data, 0, 16);
        $cipher = substr($data, 16);
        $plain = openssl_decrypt($cipher, 'aes-256-cbc', self::getKey(), OPENSSL_RAW_DATA, $iv);
        return file_put_contents($dest, $plain) !== false;
    }
}
