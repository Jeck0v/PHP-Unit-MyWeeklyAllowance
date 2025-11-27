<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\User;

use MyWeeklyAllowance\Repository\UserRepository;
use MyWeeklyAllowance\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
#[Group("user")]
#[Group("registration")]
final class UserRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        UserRepository::clear();
    }

    /**
     * A user can be registered with valid credentials
     * User registration: firstName, lastName, email, and password
     */
    public function testCanRegisterUserWithValidCredentials(): void
    {
        $user = new User(
            firstName: "John",
            lastName: "Doe",
            email: "john.doe@example.com",
            password: "SecureP@ssw0rd123",
        );

        $this->assertSame("John", $user->getFirstName());
        $this->assertSame("Doe", $user->getLastName());
        $this->assertSame("john.doe@example.com", $user->getEmail());
    }

    /**
     * A registered user has a unique ID
     * Each user must have a unique identifier for authentication
     */
    public function testRegisteredUserHasUniqueId(): void
    {
        $user1 = new User("Alice", "Smith", "alice@example.com", "Pass123!");
        $user2 = new User("Bob", "Jones", "bob@example.com", "Pass456!");

        $this->assertNotNull($user1->getId());
        $this->assertNotNull($user2->getId());
        $this->assertNotSame($user1->getId(), $user2->getId());
    }

    /**
     * Password is not stored in plain text
     * Password must be hashed and encrypted, never stored as plain text
     */
    public function testPasswordIsNotStoredInPlainText(): void
    {
        $plainPassword = "MySecretPassword123!";
        $user = new User("Jane", "Doe", "jane@example.com", $plainPassword);

        $this->assertNotSame($plainPassword, $user->getEncryptedPassword());
    }
}
