<?php

declare(strict_types=1);

namespace MyWeeklyAllowance;

use InvalidArgumentException;

final class TeenagerAccount
{
    private string $teenagerName;
    private float $balance = 0.0;
    private ?float $weeklyAllowance = null;
    private array $transactionHistory = [];
    private ?string $parentId = null;

    public function __construct(string $teenagerName)
    {
        if (trim($teenagerName) === '') {
            throw new InvalidArgumentException('Teenager name cannot be empty');
        }

        $this->teenagerName = $teenagerName;
    }

    public function getTeenagerName(): string
    {
        return $this->teenagerName;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getWeeklyAllowance(): ?float
    {
        return $this->weeklyAllowance;
    }

    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Deposit amount must be positive');
        }

        $this->balance += $amount;

        $this->transactionHistory[] = [
            'type' => 'DEPOSIT',
            'amount' => $amount,
        ];
    }

    public function spend(float $amount, string $description): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Expense amount must be positive');
        }

        if (trim($description) === '') {
            throw new InvalidArgumentException('Expense description cannot be empty');
        }

        if ($amount > $this->balance) {
            throw new InvalidArgumentException('Insufficient balance');
        }

        $this->balance -= $amount;

        $this->transactionHistory[] = [
            'type' => 'EXPENSE',
            'amount' => $amount,
            'description' => $description,
        ];
    }

    public function setWeeklyAllowance(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Weekly allowance must be positive');
        }

        $this->weeklyAllowance = $amount;
    }

    public function applyWeeklyAllowance(): void
    {
        if ($this->weeklyAllowance === null) {
            throw new InvalidArgumentException('Weekly allowance not configured');
        }

        $this->balance += $this->weeklyAllowance;

        $this->transactionHistory[] = [
            'type' => 'WEEKLY_ALLOWANCE',
            'amount' => $this->weeklyAllowance,
        ];
    }

    public function getTransactionHistory(): array
    {
        return $this->transactionHistory;
    }

    /**
     * Set parent ID for ownership validation
     */
    public function setParentId(string $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * Get parent ID
     */
    public function getParentId(): ?string
    {
        return $this->parentId;
    }
}
