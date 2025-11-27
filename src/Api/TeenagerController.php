<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Api;

use MyWeeklyAllowance\TeenagerAccount;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Teenager', description: 'Teenager account operations')]
final class TeenagerController
{
    /** @var array<string, TeenagerAccount> */
    private static array $teenagers = [];

    /**
     * Register a teenager account for API access
     */
    public static function registerTeenager(string $key, TeenagerAccount $account): void
    {
        self::$teenagers[$key] = $account;
    }

    #[OA\Get(
        path: '/api/teenager/{parentId}/{teenagerName}/balance',
        summary: 'Get teenager account balance',
        tags: ['Teenager'],
        parameters: [
            new OA\Parameter(
                name: 'parentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'teenagerName',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Account balance retrieved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'teenagerName', type: 'string'),
                        new OA\Property(property: 'balance', type: 'number', format: 'float'),
                        new OA\Property(property: 'weeklyAllowance', type: 'number', format: 'float', nullable: true),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Teenager not found'),
        ]
    )]
    public function getBalance(string $parentId, string $teenagerName): array
    {
        $teenager = self::$teenagers[$parentId . '_' . $teenagerName] ?? null;

        if ($teenager === null) {
            throw new \InvalidArgumentException('Teenager not found');
        }

        return [
            'teenagerName' => $teenager->getTeenagerName(),
            'balance' => $teenager->getBalance(),
            'weeklyAllowance' => $teenager->getWeeklyAllowance(),
        ];
    }

    #[OA\Get(
        path: '/api/teenager/{parentId}/{teenagerName}/history',
        summary: 'Get teenager transaction history',
        tags: ['Teenager'],
        parameters: [
            new OA\Parameter(
                name: 'parentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'teenagerName',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Transaction history retrieved',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'type', type: 'string', enum: ['DEPOSIT', 'EXPENSE', 'WEEKLY_ALLOWANCE']),
                            new OA\Property(property: 'amount', type: 'number', format: 'float'),
                            new OA\Property(property: 'description', type: 'string', nullable: true),
                        ]
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Teenager not found'),
        ]
    )]
    public function getHistory(string $parentId, string $teenagerName): array
    {
        $teenager = self::$teenagers[$parentId . '_' . $teenagerName] ?? null;

        if ($teenager === null) {
            throw new \InvalidArgumentException('Teenager not found');
        }

        return $teenager->getTransactionHistory();
    }

    #[OA\Post(
        path: '/api/teenager/{parentId}/{teenagerName}/allowance/apply',
        summary: 'Apply weekly allowance to teenager account',
        tags: ['Teenager'],
        parameters: [
            new OA\Parameter(
                name: 'parentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'teenagerName',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Weekly allowance applied',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'newBalance', type: 'number', format: 'float'),
                        new OA\Property(property: 'appliedAmount', type: 'number', format: 'float'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Weekly allowance not configured'),
            new OA\Response(response: 404, description: 'Teenager not found'),
        ]
    )]
    public function applyWeeklyAllowance(string $parentId, string $teenagerName): array
    {
        $teenager = self::$teenagers[$parentId . '_' . $teenagerName] ?? null;

        if ($teenager === null) {
            throw new \InvalidArgumentException('Teenager not found');
        }

        $allowanceAmount = $teenager->getWeeklyAllowance();
        $teenager->applyWeeklyAllowance();

        return [
            'newBalance' => $teenager->getBalance(),
            'appliedAmount' => $allowanceAmount,
        ];
    }
}
