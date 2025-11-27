<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\User;

use MyWeeklyAllowance\Security\AuthenticationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthenticationResult::class)]
#[Group("authentication")]
final class AuthenticationResultTest extends TestCase
{
    /**
     * Authentication result contains token and user info
     */
    public function testAuthenticationResultContainsTokenAndUserInfo(): void
    {
        $result = new AuthenticationResult(
            successful: true,
            token: "jwt.token.here",
            userId: "jeck0v",
            email: "test@example.com",
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertSame("jwt.token.here", $result->getToken());
        $this->assertSame("jeck0v", $result->getUserId());
        $this->assertSame("test@example.com", $result->getEmail());
    }
}
