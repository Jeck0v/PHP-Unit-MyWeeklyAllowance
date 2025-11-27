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
#[Group("deposit")]
final class ParentAccountDepositTest extends TestCase
{
    /**
     * A parent can deposit money into an account
     * The parent must be able to fund the teenager's virtual wallet
     */
    public function testParentCanDepositMoney(): void
    {
        $parent = new ParentAccount("Luc Petit");
        $account = $parent->createAccountFor("Emma");

        $parent->depositMoney($account, 50.0);
        $this->assertSame(50.0, $account->getBalance());
    }

    /**
     * A deposit cannot have a negative or zero amount
     * Only strictly positive amounts are allowed for deposits
     */
    public function testCannotDepositNegativeOrZeroAmount(): void
    {
        $parent = new ParentAccount("Arnaud Fischer");
        $account = $parent->createAccountFor("Arnaud");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Deposit amount must be positive");

        $parent->depositMoney($account, 0.0);
    }

    /**
     * A deposit cannot have a negative amount
     * Negative deposits are not allowed
     */
    public function testCannotDepositNegativeAmount(): void
    {
        $parent = new ParentAccount("Claire Martin");
        $account = $parent->createAccountFor("Sophie");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Deposit amount must be positive");

        $parent->depositMoney($account, -100.0);
    }

    /**
     * Cannot deposit on account from different parent
     * A parent can only deposit money on their own children's accounts
     */
    public function testCannotDepositOnOtherParentAccount(): void
    {
        $parent1 = new ParentAccount("Parent One");
        $parent2 = new ParentAccount("Parent Two");

        $accountParent1 = $parent1->createAccountFor("Child1");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Account does not belong to this parent");

        $parent2->depositMoney($accountParent1, 50.0);
    }
}
