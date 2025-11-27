<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Repository;

use MyWeeklyAllowance\Database\Database;
use MyWeeklyAllowance\TeenagerAccount;
use PDO;

final class TeenagerRepository
{
    /** @var array<string, TeenagerAccount> In-memory storage for tests */
    private static array $teenagers = [];

    /**
     * Save teenager with composite key: parentId_teenagerName
     */
    public function save(TeenagerAccount $teenager): void
    {
        if (Database::isUsingSqlite()) {
            $this->saveToDB($teenager);
        } else {
            $key = $this->generateKey($teenager->getParentId(), $teenager->getTeenagerName());
            self::$teenagers[$key] = $teenager;
        }
    }

    public function findByParentAndName(string $parentId, string $teenagerName): ?TeenagerAccount
    {
        if (Database::isUsingSqlite()) {
            return $this->findByParentAndNameInDB($parentId, $teenagerName);
        }

        $key = $this->generateKey($parentId, $teenagerName);
        return self::$teenagers[$key] ?? null;
    }

    /**
     * @return TeenagerAccount[]
     */
    public function findByParentId(string $parentId): array
    {
        if (Database::isUsingSqlite()) {
            return $this->findByParentIdInDB($parentId);
        }

        $result = [];
        foreach (self::$teenagers as $key => $teenager) {
            if (str_starts_with($key, $parentId . '_')) {
                $result[] = $teenager;
            }
        }
        return $result;
    }

    /**
     * @return TeenagerAccount[]
     */
    public function findAll(): array
    {
        if (Database::isUsingSqlite()) {
            return $this->findAllInDB();
        }

        return array_values(self::$teenagers);
    }

    private function generateKey(?string $parentId, string $teenagerName): string
    {
        return $parentId . '_' . $teenagerName;
    }

    /**
     * Clear all teenagers (for testing purposes)
     */
    public static function clear(): void
    {
        if (Database::isUsingSqlite()) {
            $pdo = Database::connect();
            $pdo->exec('DELETE FROM teenagers');
        } else {
            self::$teenagers = [];
        }
    }

    private function saveToDB(TeenagerAccount $teenager): void
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            INSERT OR REPLACE INTO teenagers (parent_id, teenager_name, teenager_data)
            VALUES (:parent_id, :teenager_name, :teenager_data)
        ');

        $stmt->execute([
            'parent_id' => $teenager->getParentId(),
            'teenager_name' => $teenager->getTeenagerName(),
            'teenager_data' => serialize($teenager),
        ]);

        // Also cache in memory for this request
        $key = $this->generateKey($teenager->getParentId(), $teenager->getTeenagerName());
        self::$teenagers[$key] = $teenager;
    }

    private function findByParentAndNameInDB(string $parentId, string $teenagerName): ?TeenagerAccount
    {
        // Check cache first
        $key = $this->generateKey($parentId, $teenagerName);
        if (isset(self::$teenagers[$key])) {
            return self::$teenagers[$key];
        }

        // Query database
        $pdo = Database::connect();
        $stmt = $pdo->prepare('
            SELECT teenager_data FROM teenagers
            WHERE parent_id = :parent_id AND teenager_name = :teenager_name
        ');
        $stmt->execute([
            'parent_id' => $parentId,
            'teenager_name' => $teenagerName,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        // Unserialize the TeenagerAccount object
        $teenager = unserialize($row['teenager_data']);

        // Cache it for this request
        if ($teenager instanceof TeenagerAccount) {
            self::$teenagers[$key] = $teenager;
            return $teenager;
        }

        return null;
    }

    /**
     * @return TeenagerAccount[]
     */
    private function findByParentIdInDB(string $parentId): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT teenager_data FROM teenagers WHERE parent_id = :parent_id');
        $stmt->execute(['parent_id' => $parentId]);

        $teenagers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $teenager = unserialize($row['teenager_data']);
            if ($teenager instanceof TeenagerAccount) {
                $teenagers[] = $teenager;
                $key = $this->generateKey($teenager->getParentId(), $teenager->getTeenagerName());
                self::$teenagers[$key] = $teenager;
            }
        }

        return $teenagers;
    }

    /**
     * @return TeenagerAccount[]
     */
    private function findAllInDB(): array
    {
        $pdo = Database::connect();
        $stmt = $pdo->query('SELECT teenager_data FROM teenagers');

        $teenagers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $teenager = unserialize($row['teenager_data']);
            if ($teenager instanceof TeenagerAccount) {
                $teenagers[] = $teenager;
                $key = $this->generateKey($teenager->getParentId(), $teenager->getTeenagerName());
                self::$teenagers[$key] = $teenager;
            }
        }

        return $teenagers;
    }
}
