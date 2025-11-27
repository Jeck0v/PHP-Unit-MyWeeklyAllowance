<?php

declare(strict_types=1);

namespace MyWeeklyAllowance;

use InvalidArgumentException;
use MyWeeklyAllowance\Repository\UserRepository;
use MyWeeklyAllowance\Security\PasswordEncryptor;
use MyWeeklyAllowance\Security\PasswordHasher;

final class User
{
    private string $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $encryptedPassword;
    private PasswordHasher $hasher;
    private PasswordEncryptor $encryptor;

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $password
    ) {
        $this->hasher = new PasswordHasher();
        $this->encryptor = new PasswordEncryptor();

        $this->validateFirstName($firstName);
        $this->validateLastName($lastName);
        $this->validateEmail($email);
        $this->validatePassword($password);

        $this->id = $this->generateUniqueId();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->encryptedPassword = $this->securePassword($password);

        // Save to repository
        $repository = new UserRepository();
        $repository->save($this);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getEncryptedPassword(): string
    {
        return $this->encryptedPassword;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        $decryptedHash = $this->encryptor->decrypt($this->encryptedPassword);
        return $this->hasher->verify($plainPassword, $decryptedHash);
    }

    private function validateFirstName(string $firstName): void
    {
        if (trim($firstName) === '') {
            throw new InvalidArgumentException('First name cannot be empty');
        }
    }

    private function validateLastName(string $lastName): void
    {
        if (trim($lastName) === '') {
            throw new InvalidArgumentException('Last name cannot be empty');
        }
    }

    private function validateEmail(string $email): void
    {
        // RFC format validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        // Additional validation: reject emails with special characters in local part (except dot, underscore, hyphen)
        $localPart = explode('@', $email)[0] ?? '';
        if (preg_match('/[^a-zA-Z0-9._-]/', $localPart)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        // Check uniqueness
        $repository = new UserRepository();
        if ($repository->emailExists($email)) {
            throw new InvalidArgumentException('Email already exists');
        }
    }

    private function validatePassword(string $password): void
    {
        // Minimum length: 8 characters
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters');
        }

        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one number');
        }

        // At least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one special character');
        }
    }

    private function securePassword(string $password): string
    {
        // Hash with Argon2id
        $hashed = $this->hasher->hash($password);

        // Encrypt with AES-256
        return $this->encryptor->encrypt($hashed);
    }

    private function generateUniqueId(): string
    {
        return bin2hex(random_bytes(16)); // 32 characters hex
    }
}
