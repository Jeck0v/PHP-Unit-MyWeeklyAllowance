<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Repository;

use MyWeeklyAllowance\Database\Database;
use MyWeeklyAllowance\User;
use PDO;

final class UserRepository
{
    /** @var array<string, User> In-memory storage for tests */
    private static array $users = [];

    public function save(User $user): void
    {
        if (Database::isUsingSqlite()) {
            $this->saveToDB($user);
        } else {
            self::$users[$user->getId()] = $user;
        }
    }

    public function findByEmail(string $email): ?User
    {
        if (Database::isUsingSqlite()) {
            return $this->findByEmailInDB($email);
        }

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
        if (Database::isUsingSqlite()) {
            $pdo = Database::connect();
            $pdo->exec('DELETE FROM users');
        } else {
            self::$users = [];
        }
    }

    private function saveToDB(User $user): void
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT OR REPLACE INTO users (id, email, user_data)
            VALUES (:id, :email, :user_data)
        ');

        $stmt->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'user_data' => serialize($user),
        ]);

        // Also cache in memory for this request
        self::$users[$user->getId()] = $user;
    }

    private function findByEmailInDB(string $email): ?User
    {
        // Check cache first
        $normalizedEmail = strtolower($email);
        foreach (self::$users as $user) {
            if (strtolower($user->getEmail()) === $normalizedEmail) {
                return $user;
            }
        }

        // Query database
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT user_data FROM users WHERE LOWER(email) = LOWER(:email)');
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        // Unserialize the User object
        $user = unserialize($row['user_data']);

        // Cache it for this request
        if ($user instanceof User) {
            self::$users[$user->getId()] = $user;
            return $user;
        }

        return null;
    }
}
