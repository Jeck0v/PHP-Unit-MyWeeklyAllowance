<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Security;

use InvalidArgumentException;
use MyWeeklyAllowance\Repository\UserRepository;

final class AuthenticationService
{
    private const MAX_FAILED_ATTEMPTS = 5;

    /** @var array<string, int> */
    private static array $failedAttempts = [];

    private UserRepository $userRepository;
    private JWTManager $jwtManager;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->jwtManager = new JWTManager();
    }

    /**
     * Authenticate user with email and password
     */
    public function authenticate(string $email, string $password): AuthenticationResult
    {
        $normalizedEmail = strtolower($email);

        // Check if account is locked
        if ($this->isAccountLocked($normalizedEmail)) {
            throw new InvalidArgumentException('Account temporarily locked');
        }

        // Find user by email (case-insensitive)
        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            $this->incrementFailedAttempts($normalizedEmail);
            throw new InvalidArgumentException('Invalid credentials');
        }

        // Verify password
        if (!$user->verifyPassword($password)) {
            $this->incrementFailedAttempts($normalizedEmail);
            throw new InvalidArgumentException('Invalid credentials');
        }

        // Reset failed attempts counter on successful login
        $this->resetFailedAttempts($normalizedEmail);

        // Generate JWT token
        $token = $this->jwtManager->generate($user);

        return new AuthenticationResult(
            successful: true,
            token: $token,
            userId: $user->getId(),
            email: $user->getEmail()
        );
    }

    /**
     * Get number of failed attempts for an email
     */
    public function getFailedAttempts(string $email): int
    {
        $normalizedEmail = strtolower($email);
        return self::$failedAttempts[$normalizedEmail] ?? 0;
    }

    private function isAccountLocked(string $email): bool
    {
        return $this->getFailedAttempts($email) >= self::MAX_FAILED_ATTEMPTS;
    }

    private function incrementFailedAttempts(string $email): void
    {
        $normalizedEmail = strtolower($email);

        if (!isset(self::$failedAttempts[$normalizedEmail])) {
            self::$failedAttempts[$normalizedEmail] = 0;
        }

        self::$failedAttempts[$normalizedEmail]++;
    }

    private function resetFailedAttempts(string $email): void
    {
        $normalizedEmail = strtolower($email);
        self::$failedAttempts[$normalizedEmail] = 0;
    }

    /**
     * Clear all failed attempts (for testing purposes)
     */
    public static function clearFailedAttempts(): void
    {
        self::$failedAttempts = [];
    }
}
