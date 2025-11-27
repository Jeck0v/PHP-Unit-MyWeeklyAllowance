<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Security;

final class PasswordHasher
{
    /**
     * Hash a password using Argon2id algorithm
     */
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify a password against its hash
     */
    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
