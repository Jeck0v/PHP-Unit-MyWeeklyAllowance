<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\Teenager;

use MyWeeklyAllowance\TeenagerAccount;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeenagerAccount::class)]
#[Group("teenager")]
#[Group("history")]
final class TeenagerAccountHistoryTest extends TestCase
{
    /**
     * Transaction history records deposits
     * All deposit operations must be kept in history for traceability
     */
    public function testTransactionHistoryRecordsDeposits(): void
    {
        $account = new TeenagerAccount("Charlie");

        $account->deposit(50.0);
        $account->deposit(25.0);

        $history = $account->getTransactionHistory();

        $this->assertCount(2, $history);
        $this->assertSame("DEPOSIT", $history[0]["type"]);
        $this->assertSame(50.0, $history[0]["amount"]);
        $this->assertSame("DEPOSIT", $history[1]["type"]);
        $this->assertSame(25.0, $history[1]["amount"]);
    }

    /**
     * Transaction history records expenses with description
     * Each expense must be tracked with its description to know how money was spent
     */
    public function testTransactionHistoryRecordsExpenses(): void
    {
        $account = new TeenagerAccount("Diana");

        $account->deposit(100.0);
        $account->spend(15.5, "Pizza");
        $account->spend(30.0, "Livre");

        $history = $account->getTransactionHistory();

        $this->assertCount(3, $history);
        $this->assertSame("DEPOSIT", $history[0]["type"]);
        $this->assertSame("EXPENSE", $history[1]["type"]);
        $this->assertSame(15.5, $history[1]["amount"]);
        $this->assertSame("Pizza", $history[1]["description"]);
        $this->assertSame("EXPENSE", $history[2]["type"]);
        $this->assertSame(30.0, $history[2]["amount"]);
        $this->assertSame("Livre", $history[2]["description"]);
    }

    /**
     * Transaction history records weekly allowances
     * Automatic payments must be tracked like other operations for complete traceability
     */
    public function testTransactionHistoryRecordsWeeklyAllowances(): void
    {
        $account = new TeenagerAccount("Ethan");

        $account->setWeeklyAllowance(20.0);
        $account->applyWeeklyAllowance();

        $history = $account->getTransactionHistory();

        $this->assertCount(1, $history);
        $this->assertSame("WEEKLY_ALLOWANCE", $history[0]["type"]);
        $this->assertSame(20.0, $history[0]["amount"]);
    }
}
