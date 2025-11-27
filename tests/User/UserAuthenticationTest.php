<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\User;

use InvalidArgumentException;
use MyWeeklyAllowance\Security\AuthenticationService;
use MyWeeklyAllowance\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthenticationService::class)]
#[CoversClass(User::class)]
#[Group("authentication")]
#[Group("login")]
final class UserAuthenticationTest extends TestCase
{
    /**
     * User can authenticate with correct email and password
     * Successful login returns a JWT token
     */
    public function testUserCanAuthenticateWithCorrectCredentials(): void
    {
        // Register user first
        $user = new User("John", "Doe", "john@example.com", "SecurePass123!");

        $authService = new AuthenticationService();
        $result = $authService->authenticate(
            "john@example.com",
            "SecurePass123!",
        );

        $this->assertTrue($result->isSuccessful());
        $this->assertNotNull($result->getToken());
        $this->assertSame($user->getId(), $result->getUserId());
    }

    /**
     * Authentication fails with incorrect email
     * Login with non-existent email should be rejected
     */
    public function testAuthenticationFailsWithIncorrectEmail(): void
    {
        $user = new User("Jane", "Smith", "jane@example.com", "Pass123!");

        $authService = new AuthenticationService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid credentials");

        $authService->authenticate("wrong@example.com", "Pass123!");
    }

    /**
     * Authentication fails with incorrect password
     * Login with wrong password should be rejected
     */
    public function testAuthenticationFailsWithIncorrectPassword(): void
    {
        $user = new User("Bob", "Jones", "bob@example.com", "CorrectPass123!");

        $authService = new AuthenticationService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid credentials");

        $authService->authenticate("bob@example.com", "WrongPass456!");
    }

    /**
     * Authentication verifies password hash correctly
     * The stored hash must match the provided password
     */
    public function testAuthenticationVerifiesPasswordHashCorrectly(): void
    {
        $plainPassword = "MyPassword789!";
        $user = new User("Alice", "Brown", "alice@example.com", $plainPassword);

        $authService = new AuthenticationService();

        // Correct password should work
        $resultSuccess = $authService->authenticate(
            "alice@example.com",
            $plainPassword,
        );
        $this->assertTrue($resultSuccess->isSuccessful());

        // Wrong password should fail
        $this->expectException(InvalidArgumentException::class);
        $authService->authenticate("alice@example.com", "WrongPassword123!");
    }

    /**
     * Authentication is case-sensitive for email
     * Email matching should be case-insensitive
     */
    public function testAuthenticationIsCaseInsensitiveForEmail(): void
    {
        $user = new User("Charlie", "Davis", "charlie@example.com", "Pass123!");

        $authService = new AuthenticationService();

        // Should work with different case
        $result = $authService->authenticate("Charlie@Example.COM", "Pass123!");

        $this->assertTrue($result->isSuccessful());
    }

    /**
     * authentication returns user information on success
     * Successful auth should return user ID and email
     */
    public function testAuthenticationReturnsUserInformationOnSuccess(): void
    {
        $user = new User("Eve", "Wilson", "eve@example.com", "Secure456!");

        $authService = new AuthenticationService();
        $result = $authService->authenticate("eve@example.com", "Secure456!");

        $this->assertTrue($result->isSuccessful());
        $this->assertSame($user->getId(), $result->getUserId());
        $this->assertSame("eve@example.com", $result->getEmail());
    }

    /**
     * Multiple failed authentication attempts are tracked
     * Track failed login attempts for security monitoring
     */
    public function testMultipleFailedAuthenticationAttemptsAreTracked(): void
    {
        $user = new User("Frank", "Moore", "frank@example.com", "Password123!");
        $authService = new AuthenticationService();

        // Attempt 3 failed logins
        for ($i = 0; $i < 3; $i++) {
            try {
                $authService->authenticate("frank@example.com", "WrongPass!");
            } catch (InvalidArgumentException $e) {
                // Expected
            }
        }

        $failedAttempts = $authService->getFailedAttempts("frank@example.com");
        $this->assertSame(3, $failedAttempts);
    }

    /**
     * account is locked after too many failed attempts
     * After 5 failed attempts, account should be temporarily locked
     */
    public function testAccountIsLockedAfterTooManyFailedAttempts(): void
    {
        $user = new User(
            "Grace",
            "Taylor",
            "grace@example.com",
            "Password789!",
        );
        $authService = new AuthenticationService();

        // Attempt 5 failed logins
        for ($i = 0; $i < 5; $i++) {
            try {
                $authService->authenticate("grace@example.com", "WrongPass!");
            } catch (InvalidArgumentException $e) {
                // Expected
            }
        }

        // 6th attempt should mention account is locked
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Account temporarily locked");

        $authService->authenticate("grace@example.com", "Password789!");
    }

    /**
     * Successful login resets failed attempt counter
     * Counter should reset to 0 after successful authentication
     */
    public function testSuccessfulLoginResetsFailedAttemptCounter(): void
    {
        $user = new User("Henry", "Anderson", "henry@example.com", "Test123!");
        $authService = new AuthenticationService();

        // Fail 2 times
        for ($i = 0; $i < 2; $i++) {
            try {
                $authService->authenticate("henry@example.com", "Wrong!");
            } catch (InvalidArgumentException $e) {
                // Expected
            }
        }

        $this->assertSame(
            2,
            $authService->getFailedAttempts("henry@example.com"),
        );

        // Successful login
        $authService->authenticate("henry@example.com", "Test123!");

        // Counter reset
        $this->assertSame(
            0,
            $authService->getFailedAttempts("henry@example.com"),
        );
    }
}
