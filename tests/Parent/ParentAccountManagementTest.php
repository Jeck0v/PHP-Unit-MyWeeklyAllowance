<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\Parent;

use InvalidArgumentException;
use MyWeeklyAllowance\ParentAccount;
use MyWeeklyAllowance\TeenagerAccount;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParentAccount::class)]
#[Group("parent")]
#[Group("management")]
final class ParentAccountManagementTest extends TestCase
{
    /**
     * A parent can create an account for a teenager
     * Main responsibility is to create and manage teenager accounts
     */
    public function testParentCanCreateTeenagerAccount(): void
    {
        $parent = new ParentAccount("Marie Durand");

        $account = $parent->createAccountFor("Alice");

        $this->assertInstanceOf(TeenagerAccount::class, $account);
        $this->assertSame("Alice", $account->getTeenagerName());
    }

    /**
     * A parent cannot create an account with an empty teenager name
     * Validation: each account must clearly identify the teenager
     */
    public function testCannotCreateAccountWithEmptyTeenagerName(): void
    {
        $parent = new ParentAccount("Pierre Martin");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Teenager name cannot be empty");

        $parent->createAccountFor("");
    }

    /**
     *A parent can create and manage multiple teenager accounts
     * A parent can have several children and must manage all their accounts
     */
    public function testParentCanManageMultipleAccounts(): void
    {
        $parent = new ParentAccount("Sophie Bernard");

        $alice = $parent->createAccountFor("Alice");
        $bob = $parent->createAccountFor("Bob");
        $charlie = $parent->createAccountFor("Charlie");

        $accounts = $parent->getAccounts();

        $this->assertCount(3, $accounts);
        $this->assertContains($alice, $accounts);
        $this->assertContains($bob, $accounts);
        $this->assertContains($charlie, $accounts);
    }
}
