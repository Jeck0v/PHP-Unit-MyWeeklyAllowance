<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\User;

use MyWeeklyAllowance\Security\JWTManager;
use MyWeeklyAllowance\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(JWTManager::class)]
#[CoversClass(User::class)]
#[Group("security")]
#[Group("jwt")]
final class JWTGenerationTest extends TestCase
{
    /**
     * A JWT token can be generated for a user
     * Generate a valid JWT token containing user information
     */
    public function testJWTTokenCanBeGeneratedForUser(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        $token = $jwtManager->generate($user);

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    /**
     * JWT token has three parts (header.payload.signature)
     * Valid JWT structure: mmmmm.yyyyy.zzzzz
     */
    public function testJWTTokenHasThreeParts(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        $token = $jwtManager->generate($user);
        $parts = explode(".", $token);

        $this->assertCount(3, $parts);
    }

    /**
     * JWT payload contains user ID
     * Payload must include the user's unique identifier
     */
    public function testJWTPayloadContainsUserId(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        $token = $jwtManager->generate($user);
        $payload = $jwtManager->decode($token);

        $this->assertArrayHasKey("user_id", $payload);
        $this->assertSame($user->getId(), $payload["user_id"]);
    }

    /**
     * JWT payload contains user email
     * Payload must include the user's email address
     */
    public function testJWTPayloadContainsEmail(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        $token = $jwtManager->generate($user);
        $payload = $jwtManager->decode($token);

        $this->assertArrayHasKey("email", $payload);
        $this->assertSame("alice@example.com", $payload["email"]);
    }

    /**
     *  JWT payload contains expiration timestamp
     * Token must have an expiration time (exp claim)
     */
    public function testJWTPayloadContainsExpiration(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        $token = $jwtManager->generate($user);
        $payload = $jwtManager->decode($token);

        $this->assertArrayHasKey("exp", $payload);
        $this->assertIsInt($payload["exp"]);
        $this->assertGreaterThan(time(), $payload["exp"]);
    }

    /**
     * JWT token can be verified as valid
     * A valid token should pass signature verification
     */
    public function testJWTTokenCanBeVerifiedAsValid(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        $token = $jwtManager->generate($user);
        $isValid = $jwtManager->verify($token);

        $this->assertTrue($isValid);
    }

    /**
     * Tampered JWT token is rejected
     * Modified tokens should fail signature verification
     */
    public function testTamperedJWTTokenIsRejected(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        $token = $jwtManager->generate($user);

        // Tamper with the token
        $parts = explode(".", $token);
        $parts[1] = base64_encode(
            '{"user_id":"999","email":"hacker@example.com"}',
        );
        $tamperedToken = implode(".", $parts);

        $isValid = $jwtManager->verify($tamperedToken);

        $this->assertFalse($isValid);
    }

    /**
     * Expired JWT token is rejected
     * Tokens past their expiration time should be invalid
     */
    public function testExpiredJWTTokenIsRejected(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        // Generate token that expires immediately
        $token = $jwtManager->generate($user, expiresInSeconds: -1);

        $isValid = $jwtManager->verify($token);

        $this->assertFalse($isValid);
    }

    /**
     * JWT token default expiration is 1 hour
     * Tokens should expire after 1 hour (3600 seconds) by default
     */
    public function testJWTTokenDefaultExpirationIsOneHour(): void
    {
        $user = new User(
            "Bob",
            "Jones",
            "bob@example.com",
            "yeZ7_SN(ri7s73A7,*",
        );
        $jwtManager = new JWTManager();

        $token = $jwtManager->generate($user);
        $payload = $jwtManager->decode($token);

        $expectedExpiration = time() + 3600;
        $actualExpiration = $payload["exp"];

        // Allow 5 seconds margin for test execution time
        $this->assertEqualsWithDelta($expectedExpiration, $actualExpiration, 5);
    }
}
