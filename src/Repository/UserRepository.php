<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Repository;

use MyWeeklyAllowance\User;

final class UserRepository
{
    /** @var array<string, User> */
    private static array $users = [];

    public function save(User $user): void
    {
        self::$users[$user->getId()] = $user;
    }

    public function findByEmail(string $email): ?User
    {
        $normalizedEmail = strtolower($email);

        foreach (self::$users as $user) {
            if (strtolower($user->getEmail()) === $normalizedEmail) {
                return $user;
            }
        }

        return null;
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    /**
     * Clear all users (for testing purposes)
     */
    public static function clear(): void
    {
        self::$users = [];
    }
}
