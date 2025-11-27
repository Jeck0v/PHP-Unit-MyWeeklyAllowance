<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Security;

use MyWeeklyAllowance\User;

final class JWTManager
{
    private const ALGORITHM = 'HS256';
    private const DEFAULT_EXPIRATION = 3600; // 1 hour in seconds

    private string $secretKey;

    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? bin2hex(random_bytes(32));
    }

    /**
     * Generate a JWT token for a user
     */
    public function generate(User $user, ?int $expiresInSeconds = null): string
    {
        $expiration = $expiresInSeconds ?? self::DEFAULT_EXPIRATION;

        $header = [
            'typ' => 'JWT',
            'alg' => self::ALGORITHM,
        ];

        $payload = [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'exp' => time() + $expiration,
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = $this->sign($headerEncoded . '.' . $payloadEncoded);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    /**
     * Decode JWT token and return payload
     */
    public function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid token format');
        }

        $payload = json_decode($this->base64UrlDecode($parts[1]), true);

        return $payload;
    }

    /**
     * Verify token signature and expiration
     */
    public function verify(string $token): bool
    {
        try {
            $parts = explode('.', $token);

            if (count($parts) !== 3) {
                return false;
            }

            [$headerEncoded, $payloadEncoded, $signatureProvided] = $parts;

            // Verify signature
            $expectedSignature = $this->sign($headerEncoded . '.' . $payloadEncoded);
            if (!hash_equals($expectedSignature, $signatureProvided)) {
                return false;
            }

            // Verify expiration
            $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function sign(string $data): string
    {
        return $this->base64UrlEncode(
            hash_hmac('sha256', $data, $this->secretKey, true)
        );
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
