<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Api;

use MyWeeklyAllowance\Security\AuthenticationService;
use MyWeeklyAllowance\User;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'MyWeeklyAllowance API',
    description: 'REST API for managing teenagers pocket money accounts'
)]
#[OA\Server(
    url: 'http://localhost:9462',
    description: 'Development server'
)]
#[OA\Tag(name: 'Authentication', description: 'User authentication endpoints')]
final class AuthController
{
    private AuthenticationService $authService;

    public function __construct()
    {
        $this->authService = new AuthenticationService();
    }

    #[OA\Post(
        path: '/api/register',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['firstName', 'lastName', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SecureP@ssw0rd123'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User successfully registered',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string'),
                        new OA\Property(property: 'firstName', type: 'string'),
                        new OA\Property(property: 'lastName', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    public function register(array $data): array
    {
        $user = new User(
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            email: $data['email'],
            password: $data['password']
        );

        return [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
        ];
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Authenticate user and get JWT token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john.doe@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'SecureP@ssw0rd123'),
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string'),
                        new OA\Property(property: 'userId', type: 'string'),
                        new OA\Property(property: 'email', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 423, description: 'Account locked'),
        ]
    )]
    public function login(array $data): array
    {
        $result = $this->authService->authenticate(
            $data['email'],
            $data['password']
        );

        return [
            'token' => $result->getToken(),
            'userId' => $result->getUserId(),
            'email' => $result->getEmail(),
        ];
    }
}
