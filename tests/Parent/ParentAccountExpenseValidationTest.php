<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\Parent;

use InvalidArgumentException;
use MyWeeklyAllowance\ParentAccount;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParentAccount::class)]
#[Group("parent")]
#[Group("expense")]
#[Group("validation")]
final class ParentAccountExpenseValidationTest extends TestCase
{
    /**
     * Cannot record expense with negative amount
     * Only positive amounts are allowed for expenses
     */
    public function testCannotRecordExpenseWithNegativeAmount(): void
    {
        $parent = new ParentAccount("John Smith");
        $account = $parent->createAccountFor("Alice");
        $parent->depositMoney($account, 100.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expense amount must be positive");

        $parent->recordExpense($account, -25.0, "Invalid");
    }

    /**
     * Cannot record expense with zero amount
     * Expenses must be strictly greater than zero
     */
    public function testCannotRecordExpenseWithZeroAmount(): void
    {
        $parent = new ParentAccount("Jane Doe");
        $account = $parent->createAccountFor("Bob");
        $parent->depositMoney($account, 100.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expense amount must be positive");

        $parent->recordExpense($account, 0.0, "Invalid");
    }

    /**
     * Cannot record expense with empty description
     * Each expense must have a description for tracking
     */
    public function testCannotRecordExpenseWithEmptyDescription(): void
    {
        $parent = new ParentAccount("Robert Johnson");
        $account = $parent->createAccountFor("Charlie");
        $parent->depositMoney($account, 100.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expense description cannot be empty");

        $parent->recordExpense($account, 30.0, "");
    }

    /**
     * Cannot record expense on account from different parent
     * A parent can only record expenses on their own children's accounts
     */
    public function testCannotRecordExpenseOnOtherParentAccount(): void
    {
        $parent1 = new ParentAccount("Parent One");
        $parent2 = new ParentAccount("Parent Two");

        $accountParent1 = $parent1->createAccountFor("Child1");
        $parent1->depositMoney($accountParent1, 100.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Account does not belong to this parent");

        $parent2->recordExpense($accountParent1, 20.0, "Unauthorized");
    }
}
