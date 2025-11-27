<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\User;

use InvalidArgumentException;
use MyWeeklyAllowance\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
#[Group("user")]
#[Group("validation")]
final class UserRegistrationValidationTest extends TestCase
{
    /**
     * First name cannot be empty
     * Validation: first name is mandatory
     */
    public function testFirstNameCannotBeEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("First name cannot be empty");

        new User("", "Doe", "john@example.com", "Pass123!");
    }

    /**
     * Last name cannot be empty
     * Validation: last name is mandatory
     */
    public function testLastNameCannotBeEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Last name cannot be empty");

        new User("John", "", "john@example.com", "Pass123!");
    }

    /**
     * Email must have a valid format (RFC)
     * Email must follow RFC standard format
     */
    #[DataProvider("invalidEmailProvider")]
    public function testEmailMustHaveValidFormat(string $invalidEmail): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid email format");

        new User("John", "Doe", $invalidEmail, "Pass123!");
    }

    public static function invalidEmailProvider(): array
    {
        return [
            "missing @" => ["johndoe.com"],
            "missing domain" => ["john@"],
            "missing local part" => ["@example.com"],
            "spaces" => ["john doe@example.com"],
            "double @" => ["john@@example.com"],
            "missing TLD" => ["john@example"],
            "invalid characters" => ["john#doe@example.com"],
        ];
    }

    /**
     * Email must be unique (no duplicates)
     * Each email can only be registered once in the system
     */
    public function testEmailMustBeUnique(): void
    {
        $email = "duplicate@example.com";
        $user1 = new User("John", "Doe", $email, "Pass123!");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Email already exists");

        $user2 = new User("Jane", "Smith", $email, "Pass456!");
    }

    /**
     * Password must meet minimum length requirement
     * Password must be at least 8 characters long
     */
    public function testPasswordMustMeetMinimumLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Password must be at least 8 characters");

        new User("John", "Doe", "john@example.com", "Pass1!");
    }

    /**
     * Password must contain at least one uppercase letter
     * Strong password requirement: at least one uppercase letter
     */
    public function testPasswordMustContainUppercase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Password must contain at least one uppercase letter",
        );

        new User("John", "Doe", "john@example.com", "password123!");
    }

    /**
     * Password must contain at least one lowercase letter
     * Strong password requirement: at least one lowercase letter
     */
    public function testPasswordMustContainLowercase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Password must contain at least one lowercase letter",
        );

        new User("John", "Doe", "john@example.com", "PASSWORD123!");
    }

    /**
     * Password must contain at least one number
     * Strong password requirement: at least one digit
     */
    public function testPasswordMustContainNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Password must contain at least one number",
        );

        new User("John", "Doe", "john@example.com", "Password!");
    }

    /**
     * Password must contain at least one special character
     * Strong password requirement: at least one special character
     */
    public function testPasswordMustContainSpecialCharacter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Password must contain at least one special character",
        );

        new User("John", "Doe", "john@example.com", "Password123");
    }
}
