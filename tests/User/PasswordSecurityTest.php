<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Tests\User;

use MyWeeklyAllowance\Security\PasswordHasher;
use MyWeeklyAllowance\Security\PasswordEncryptor;
use MyWeeklyAllowance\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(PasswordHasher::class)]
#[CoversClass(PasswordEncryptor::class)]
#[CoversClass(User::class)]
#[Group("security")]
#[Group("password")]
final class PasswordSecurityTest extends TestCase
{
    /**
     * Password i s hashed with Argon2id
     * (PASSWORD_ARGON2ID)
     */
    public function testPasswordIsHashedWithArgon2id(): void
    {
        $plainPassword = "yeZ7_SN(ri7s73A7,*";
        $hasher = new PasswordHasher();

        $hashedPassword = $hasher->hash($plainPassword);

        // Argon2id hash starting with $argon2id$
        $this->assertStringStartsWith('$argon2id$', $hashedPassword);
    }

    /**
     * Hashed password can be verified correctly
     * Verify that the hashed password matches the original plain password
     */
    public function testHashedPasswordCanBeVerified(): void
    {
        $plainPassword = "MySecretPassword456!";
        $hasher = new PasswordHasher();

        $hashedPassword = $hasher->hash($plainPassword);
        $isValid = $hasher->verify($plainPassword, $hashedPassword);

        $this->assertTrue($isValid);
    }

    /**
     * Wrong password verification fails
     * Verify that an incorrect password does not match the hash
     */
    public function testWrongPasswordVerificationFails(): void
    {
        $correctPassword = "CorrectPassword789!";
        $wrongPassword = "WrongPassword0541!";
        $hasher = new PasswordHasher();

        $hashedPassword = $hasher->hash($correctPassword);
        $isValid = $hasher->verify($wrongPassword, $hashedPassword);

        $this->assertFalse($isValid);
    }

    /**
     * Hashed password is encrypted with AES-256
     * The hashed password into encrypted with AES-256-CBC
     */
    public function testHashedPasswordIsEncryptedWithAES256(): void
    {
        $plainPassword = "TestPassword123!";
        $hasher = new PasswordHasher();
        $encryptor = new PasswordEncryptor();

        $hashedPassword = $hasher->hash($plainPassword);
        $encryptedHash = $encryptor->encrypt($hashedPassword);

        // Encrypted data should be different from hash
        $this->assertNotSame($hashedPassword, $encryptedHash);
        // Encrypted data should be base64 encoded
        $this->assertMatchesRegularExpression(
            '/^[A-Za-z0-9+\/=]+$/',
            $encryptedHash,
        );
    }

    /**
     *  Encrypted hash can be decrypted back to original hash
     * Verify that decryption returns the original hashed password
     */
    public function testEncryptedHashCanBeDecrypted(): void
    {
        $plainPassword = "yeZ7_SN(ri7s73A7,*";
        $hasher = new PasswordHasher();
        $encryptor = new PasswordEncryptor();

        $hashedPassword = $hasher->hash($plainPassword);
        $encryptedHash = $encryptor->encrypt($hashedPassword);
        $decryptedHash = $encryptor->decrypt($encryptedHash);

        $this->assertSame($hashedPassword, $decryptedHash);
    }

    /**
     * User password goes through complete security pipeline
     * Hash with argon2bid =>Encrypt with AES-256 => Store encrypted hash
     */
    public function testUserPasswordGoesThoughCompleteSecurityPipeline(): void
    {
        $plainPassword = "CompleteTest789!";
        $user = new User(
            "Security",
            "Tester",
            "security@example.com",
            $plainPassword,
        );

        $encryptedPassword = $user->getEncryptedPassword();
        $this->assertNotSame($plainPassword, $encryptedPassword);

        // Should be able to verify against the original password
        $this->assertTrue($user->verifyPassword($plainPassword));
        $this->assertFalse($user->verifyPassword("WrongPassword123!"));
    }

    /**
     * Each password hash is unique (salted)
     * Even same passwords should produce different hashes like due to the salt factor
     */
    public function testEachPasswordHashIsUnique(): void
    {
        $password = "SamePassword123!";
        $user1 = new User("User", "One", "user1@example.com", $password);
        $user2 = new User("User", "Two", "user2@example.com", $password);

        $this->assertNotSame(
            $user1->getEncryptedPassword(),
            $user2->getEncryptedPassword(),
        );
    }
}
