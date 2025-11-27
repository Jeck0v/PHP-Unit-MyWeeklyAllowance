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
#[Group("allowance")]
final class ParentAccountAllowanceTest extends TestCase
{
    /**
     * A parent can configure a weekly allowance
     * Automate regular pocket money payments to the teenager
     */
    public function testParentCanSetWeeklyAllowance(): void
    {
        $parent = new ParentAccount("David Leroy");
        $account = $parent->createAccountFor("Léa");

        $parent->setWeeklyAllowance($account, 15.0);

        $this->assertSame(15.0, $account->getWeeklyAllowance());
    }

    /**
     * A weekly allowance must be strictly positive
     * Validation: only positive amounts are allowed for allowances
     */
    public function testWeeklyAllowanceMustBePositive(): void
    {
        $parent = new ParentAccount("Isabelle Garnier");
        $account = $parent->createAccountFor("Noah");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Weekly allowance must be positive");

        $parent->setWeeklyAllowance($account, -10.0);
    }

    /**
     * Weekly allowance cannot be zero
     * Allowance must be strictly greater than zero
     */
    public function testWeeklyAllowanceCannotBeZero(): void
    {
        $parent = new ParentAccount("Marie Dubois");
        $account = $parent->createAccountFor("Lucas");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Weekly allowance must be positive");

        $parent->setWeeklyAllowance($account, 0.0);
    }

    /**
     * Cannot set allowance on account from different parent
     * A parent can only configure allowances on their own children's accounts
     */
    public function testCannotSetAllowanceOnOtherParentAccount(): void
    {
        $parent1 = new ParentAccount("Parent One");
        $parent2 = new ParentAccount("Parent Two");

        $accountParent1 = $parent1->createAccountFor("Child1");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Account does not belong to this parent");

        $parent2->setWeeklyAllowance($accountParent1, 20.0);
    }
}
