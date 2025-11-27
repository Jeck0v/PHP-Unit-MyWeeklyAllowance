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
final class ParentAccountExpenseTest extends TestCase
{
    /**
     *  A parent can record an expense
     * Track teenager expenses and deduct from balance
     */
    public function testParentCanRecordExpense(): void
    {
        $parent = new ParentAccount("Thomas Blanc");
        $account = $parent->createAccountFor("Sophie");

        $parent->depositMoney($account, 100.0);
        $parent->recordExpense($account, 25.5, "Cinéma");

        $this->assertSame(74.5, $account->getBalance());
    }

    /**
     * An expense cannot create a negative balance (strict blocking)
     * No overdraft allowed - expense must be rejected if balance is insufficient
     */
    public function testCannotSpendMoreThanBalance(): void
    {
        $parent = new ParentAccount("Anne Moreau");
        $account = $parent->createAccountFor("Jules");

        $parent->depositMoney($account, 30.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Insufficient balance");

        $parent->recordExpense($account, 50.0, "Console de jeux");
    }
}
