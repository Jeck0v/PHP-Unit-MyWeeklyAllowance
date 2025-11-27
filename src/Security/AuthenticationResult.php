<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Security;

final class AuthenticationResult
{
    private bool $successful;
    private ?string $token;
    private ?string $userId;
    private ?string $email;

    public function __construct(
        bool $successful,
        ?string $token = null,
        ?string $userId = null,
        ?string $email = null
    ) {
        $this->successful = $successful;
        $this->token = $token;
        $this->userId = $userId;
        $this->email = $email;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
