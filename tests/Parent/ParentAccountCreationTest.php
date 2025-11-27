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
#[Group("creation")]
final class ParentAccountCreationTest extends TestCase
{
    /**
     * A parent can be created with a name
     * Each parent must have an identity to manage their teenagers accounts
     */
    public function testCanCreateParentWithName(): void
    {
        $parent = new ParentAccount("Jean Dupont");

        $this->assertSame("Jean Dupont", $parent->getName());
    }

    /**
     * A parent cannot be created with an empty name
     * Validation rule: name is mandatory to identify the parent
     */
    public function testCannotCreateParentWithEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Parent name cannot be empty");

        new ParentAccount("");
    }
}
