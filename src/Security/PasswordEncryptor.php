<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Security;

final class PasswordEncryptor
{
    private const CIPHER = "AES-256-CBC";
    private string $encryptionKey;

    public function __construct(?string $encryptionKey = null)
    {
        // Use provide d key or generate one for testing
        $this->encryptionKey = $encryptionKey ?? $this->generateKey();
    }

    /**
     * Encrypt a hashed password using AES-256-CBC
     */
    public function encrypt(string $data): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $encrypted = openssl_encrypt(
            $data,
            self::CIPHER,
            $this->encryptionKey,
            0,
            $iv,
        );

        // Return base64 encoded: IV + encrypted data
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt an encrypted hash
     */
    public function decrypt(string $encryptedData): string
    {
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length(self::CIPHER);

        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        return openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $this->encryptionKey,
            0,
            $iv,
        );
    }

    private function generateKey(): string
    {
        return bin2hex(random_bytes(32)); // 256 bits
    }
}
