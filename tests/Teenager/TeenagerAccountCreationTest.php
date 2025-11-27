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
#[Group("creation")]
final class TeenagerAccountCreationTest extends TestCase
{
    /**
     * A teenager account can be created with a name
     * Each account must clearly identify the teenager owner
     */
    public function testCanCreateAccountWithTeenagerName(): void
    {
        $account = new TeenagerAccount("Alice");

        $this->assertSame("Alice", $account->getTeenagerName());
    }

    /**
     * A new account has an initial balance of 0.00€
     * By default, a new account contains no money
     */
    public function testNewAccountHasZeroBalance(): void
    {
        $account = new TeenagerAccount("Bob");

        $this->assertSame(0.0, $account->getBalance());
    }

    /**
     * An account cannot be created with an empty name
     * Validation: name is mandatory to identify the teenager owner
     */
    public function testCannotCreateAccountWithEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Teenager name cannot be empty");

        new TeenagerAccount("");
    }
}
