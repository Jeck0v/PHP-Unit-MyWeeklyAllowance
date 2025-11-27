<?php

declare(strict_types=1);

namespace MyWeeklyAllowance;

use InvalidArgumentException;

final class ParentAccount
{
    private string $id;
    private string $name;
    /** @var TeenagerAccount[] */
    private array $accounts = [];

    public function __construct(string $name)
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('Parent name cannot be empty');
        }

        $this->id = $this->generateUniqueId();
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Create a teenager account
     */
    public function createAccountFor(string $teenagerName): TeenagerAccount
    {
        if (trim($teenagerName) === '') {
            throw new InvalidArgumentException('Teenager name cannot be empty');
        }

        $account = new TeenagerAccount($teenagerName);
        $account->setParentId($this->id);

        $this->accounts[] = $account;

        return $account;
    }

    /**
     * Get all managed teenager accounts
     */
    public function getAccounts(): array
    {
        return $this->accounts;
    }

    /**
     * Deposit money into a teenager account
     */
    public function depositMoney(TeenagerAccount $account, float $amount): void
    {
        $this->validateOwnership($account);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Deposit amount must be positive');
        }

        $account->deposit($amount);
    }

    /**
     * Record an expense on a teenager account
     */
    public function recordExpense(TeenagerAccount $account, float $amount, string $description): void
    {
        $this->validateOwnership($account);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Expense amount must be positive');
        }

        if (trim($description) === '') {
            throw new InvalidArgumentException('Expense description cannot be empty');
        }

        $account->spend($amount, $description);
    }

    /**
     * Set weekly allowance for a teenager account
     */
    public function setWeeklyAllowance(TeenagerAccount $account, float $amount): void
    {
        $this->validateOwnership($account);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Weekly allowance must be positive');
        }

        $account->setWeeklyAllowance($amount);
    }

    /**
     * Validate that this parent owns the account
     */
    private function validateOwnership(TeenagerAccount $account): void
    {
        if ($account->getParentId() !== $this->id) {
            throw new InvalidArgumentException('Account does not belong to this parent');
        }
    }

    private function generateUniqueId(): string
    {
        return bin2hex(random_bytes(16));
    }
}
