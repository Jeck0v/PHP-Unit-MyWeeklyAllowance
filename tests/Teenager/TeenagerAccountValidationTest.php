<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\Teenager;

use InvalidArgumentException;
use MyWeeklyAllowance\TeenagerAccount;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeenagerAccount::class)]
#[Group("teenager")]
#[Group("validation")]
final class TeenagerAccountValidationTest extends TestCase
{
    /**
     *Cannot deposit a negative amount
     * Only positive amounts are allowed for deposits
     */
    public function testCannotDepositNegativeAmount(): void
    {
        $account = new TeenagerAccount("Alice");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Deposit amount must be positive");

        $account->deposit(-50.0);
    }

    /**
     * Cannot deposit zero amount
     * Deposits must be strictly greater than zero
     */
    public function testCannotDepositZeroAmount(): void
    {
        $account = new TeenagerAccount("Bob");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Deposit amount must be positive");

        $account->deposit(0.0);
    }

    /**
     * Cannot spend a negative amount
     * Only positive amounts are allowed for expenses
     */
    public function testCannotSpendNegativeAmount(): void
    {
        $account = new TeenagerAccount("Charlie");
        $account->deposit(100.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expense amount must be positive");

        $account->spend(-20.0, "Invalid expense");
    }

    /**
     * Cannot spend zero amount
     * Expenses must be strictly greater than zero
     */
    public function testCannotSpendZeroAmount(): void
    {
        $account = new TeenagerAccount("Diana");
        $account->deposit(100.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expense amount must be positive");

        $account->spend(0.0, "Invalid expense");
    }

    /**
     * Cannot spend more than current balance
     * No overdraft allowed - strict balance checking
     */
    public function testCannotSpendMoreThanBalance(): void
    {
        $account = new TeenagerAccount("Ethan");
        $account->deposit(50.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Insufficient balance");

        $account->spend(75.0, "Too expensive");
    }

    /**
     * Cannot spend with empty description
     * Each expense must have a description for tracking purposes
     */
    public function testCannotSpendWithEmptyDescription(): void
    {
        $account = new TeenagerAccount("Fiona");
        $account->deposit(100.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expense description cannot be empty");

        $account->spend(25.0, "");
    }

    /**
     * Cannot set negative weekly allowance
     * Weekly allowance must be a positive amount
     */
    public function testCannotSetNegativeWeeklyAllowance(): void
    {
        $account = new TeenagerAccount("George");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Weekly allowance must be positive");

        $account->setWeeklyAllowance(-15.0);
    }

    /**
     * Cannot set zero weekly allowance
     * Weekly allowance must be strictly greater than zero
     */
    public function testCannotSetZeroWeeklyAllowance(): void
    {
        $account = new TeenagerAccount("Hannah");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Weekly allowance must be positive");

        $account->setWeeklyAllowance(0.0);
    }

    /**
     * New account has no weekly allowance by default
     * Default weekly allowance should be null or 0
     */
    public function testNewAccountHasNoWeeklyAllowanceByDefault(): void
    {
        $account = new TeenagerAccount("Ian");

        $this->assertNull($account->getWeeklyAllowance());
    }

    /**
     * Cannot apply weekly allowance if not configured
     * Weekly allowance must be set before it can be applied
     */
    public function testCannotApplyWeeklyAllowanceIfNotConfigured(): void
    {
        $account = new TeenagerAccount("Julia");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Weekly allowance not configured");

        $account->applyWeeklyAllowance();
    }
}
