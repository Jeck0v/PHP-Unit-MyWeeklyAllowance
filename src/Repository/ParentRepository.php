<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Repository;

use MyWeeklyAllowance\Database\Database;
use MyWeeklyAllowance\ParentAccount;
use PDO;

final class ParentRepository
{
    /** @var array<string, ParentAccount> In-memory storage for tests */
    private static array $parents = [];

    public function save(ParentAccount $parent): void
    {
        if (Database::isUsingSqlite()) {
            $this->saveToDB($parent);
        } else {
            self::$parents[$parent->getId()] = $parent;
        }
    }

    public function findById(string $id): ?ParentAccount
    {
        if (Database::isUsingSqlite()) {
            return $this->findByIdInDB($id);
        }

        return self::$parents[$id] ?? null;
    }

    /**
     * @return ParentAccount[]
     */
    public function findAll(): array
    {
        if (Database::isUsingSqlite()) {
            return $this->findAllInDB();
        }

        return array_values(self::$parents);
    }

    /**
     * Get all parent IDs (for debugging)
     * @return string[]
     */
    public function getAllIds(): array
    {
        return array_keys(self::$parents);
    }

    /**
     * Clear all parents (for testing purposes)
     */
    public static function clear(): void
    {
        if (Database::isUsingSqlite()) {
            $pdo = Database::connect();
            $pdo->exec('DELETE FROM parents');
        } else {
            self::$parents = [];
        }
    }

    private function saveToDB(ParentAccount $parent): void
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT OR REPLACE INTO parents (id, parent_data)
            VALUES (:id, :parent_data)
        ');

        $stmt->execute([
            'id' => $parent->getId(),
            'parent_data' => serialize($parent),
        ]);

        // Also cache in memory for this request
        self::$parents[$parent->getId()] = $parent;
    }

    private function findByIdInDB(string $id): ?ParentAccount
    {
        // Check cache first
        if (isset(self::$parents[$id])) {
            return self::$parents[$id];
        }

        // Query database
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT parent_data FROM parents WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        // Unserialize the ParentAccount object
        $parent = unserialize($row['parent_data']);

        // Cache it for this request
        if ($parent instanceof ParentAccount) {
            self::$parents[$parent->getId()] = $parent;
            return $parent;
        }

        return null;
    }

    /**
     * @return ParentAccount[]
     */
    private function findAllInDB(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('SELECT parent_data FROM parents');

        $parents = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $parent = unserialize($row['parent_data']);
            if ($parent instanceof ParentAccount) {
                $parents[] = $parent;
                self::$parents[$parent->getId()] = $parent;
            }
        }

        return $parents;
    }
}
