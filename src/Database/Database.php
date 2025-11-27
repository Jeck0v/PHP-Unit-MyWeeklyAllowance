<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Database;

use PDO;

final class Database
{
    private static ?PDO $connection = null;
    private static bool $useSqlite = false;

    public static function connect(string $dbPath = __DIR__ . '/../../data/myweeklyallowance.db'): PDO
    {
        if (self::$connection === null) {
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            self::$connection = new PDO('sqlite:' . $dbPath);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$useSqlite = true;

            self::createTables();
        }

        return self::$connection;
    }

    public static function isUsingSqlite(): bool
    {
        return self::$useSqlite;
    }

    public static function disconnect(): void
    {
        self::$connection = null;
        self::$useSqlite = false;
    }

    private static function createTables(): void
    {
        $pdo = self::$connection;

        // Users table (storing serialized User objects)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id TEXT PRIMARY KEY,
                email TEXT NOT NULL UNIQUE,
                user_data BLOB NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)");

        // Parents table (storing serialized ParentAccount objects)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS parents (
                id TEXT PRIMARY KEY,
                parent_data BLOB NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Teenagers table (storing serialized TeenagerAccount objects)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS teenagers (
                parent_id TEXT NOT NULL,
                teenager_name TEXT NOT NULL,
                teenager_data BLOB NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (parent_id, teenager_name),
                FOREIGN KEY (parent_id) REFERENCES parents(id)
            )
        ");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_teenagers_parent ON teenagers(parent_id)");

        // Failed login attempts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS failed_attempts (
                email TEXT PRIMARY KEY,
                attempts INTEGER NOT NULL DEFAULT 0,
                last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}
