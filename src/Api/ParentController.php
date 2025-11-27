<?php

declare(strict_types=1);

namespace MyWeeklyAllowance\Api;

use MyWeeklyAllowance\ParentAccount;
use MyWeeklyAllowance\Repository\ParentRepository;
use MyWeeklyAllowance\Repository\TeenagerRepository;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Parent', description: 'Parent account management endpoints')]
final class ParentController
{
    private ParentRepository $parentRepo;
    private TeenagerRepository $teenagerRepo;

    public function __construct()
    {
        $this->parentRepo = new ParentRepository();
        $this->teenagerRepo = new TeenagerRepository();
    }

    #[OA\Post(
        path: '/api/parent',
        summary: 'Create a parent account',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Luc Petit'),
                ]
            )
        ),
        tags: ['Parent'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Parent account created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string'),
                        new OA\Property(property: 'name', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
        ]
    )]
    public function createParent(array $data): array
    {
        $parent = new ParentAccount($data['name']);
        $this->parentRepo->save($parent);

        return [
            'id' => $parent->getId(),
            'name' => $parent->getName(),
        ];
    }

    #[OA\Post(
        path: '/api/parent/{parentId}/teenager',
        summary: 'Create a teenager account',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['teenagerName'],
                properties: [
                    new OA\Property(property: 'teenagerName', type: 'string', example: 'Emma'),
                ]
            )
        ),
        tags: ['Parent'],
        parameters: [
            new OA\Parameter(
                name: 'parentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Teenager account created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'teenagerName', type: 'string'),
                        new OA\Property(property: 'balance', type: 'number', format: 'float'),
                        new OA\Property(property: 'parentId', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 404, description: 'Parent not found'),
        ]
    )]
    public function createTeenagerAccount(string $parentId, array $data): array
    {
        $parent = $this->parentRepo->findById($parentId);

        if ($parent === null) {
            throw new \InvalidArgumentException('Parent not found');
        }

        $teenager = $parent->createAccountFor($data['teenagerName']);
        $this->teenagerRepo->save($teenager);

        // Re-save parent (contains updated $accounts array)
        $this->parentRepo->save($parent);

        return [
            'teenagerName' => $teenager->getTeenagerName(),
            'balance' => $teenager->getBalance(),
            'parentId' => $teenager->getParentId(),
        ];
    }

    #[OA\Get(
        path: '/api/parent/{parentId}/accounts',
        summary: 'Get all managed teenager accounts',
        tags: ['Parent'],
        parameters: [
            new OA\Parameter(
                name: 'parentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of teenager accounts',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'teenagerName', type: 'string'),
                            new OA\Property(property: 'balance', type: 'number', format: 'float'),
                            new OA\Property(property: 'weeklyAllowance', type: 'number', format: 'float', nullable: true),
                        ]
                    )
                )
            ),
            new OA\Response(response: 404, description: 'Parent not found'),
        ]
    )]
    public function getAccounts(string $parentId): array
    {
        $parent = $this->parentRepo->findById($parentId);

        if ($parent === null) {
            throw new \InvalidArgumentException('Parent not found');
        }

        $accounts = $parent->getAccounts();
        $result = [];

        foreach ($accounts as $account) {
            $result[] = [
                'teenagerName' => $account->getTeenagerName(),
                'balance' => $account->getBalance(),
                'weeklyAllowance' => $account->getWeeklyAllowance(),
            ];
        }

        return $result;
    }

    #[OA\Post(
        path: '/api/parent/{parentId}/deposit',
        summary: 'Deposit money into teenager account',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['teenagerName', 'amount'],
                properties: [
                    new OA\Property(property: 'teenagerName', type: 'string', example: 'Emma'),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 50.0),
                ]
            )
        ),
        tags: ['Parent'],
        parameters: [
            new OA\Parameter(
                name: 'parentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Deposit successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'newBalance', type: 'number', format: 'float'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 404, description: 'Parent or teenager not found'),
        ]
    )]
    public function deposit(string $parentId, array $data): array
    {
        $parent = $this->parentRepo->findById($parentId);

        if ($parent === null) {
            throw new \InvalidArgumentException('Parent not found');
        }

        $teenager = $this->teenagerRepo->findByParentAndName($parentId, $data['teenagerName']);

        if ($teenager === null) {
            throw new \InvalidArgumentException('Teenager not found');
        }

        $parent->depositMoney($teenager, $data['amount']);

        // Re-save teenager (balance modified)
        $this->teenagerRepo->save($teenager);

        return [
            'newBalance' => $teenager->getBalance(),
        ];
    }

    #[OA\Post(
        path: '/api/parent/{parentId}/expense',
        summary: 'Record an expense on teenager account',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['teenagerName', 'amount', 'description'],
                properties: [
                    new OA\Property(property: 'teenagerName', type: 'string', example: 'Emma'),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 15.0),
                    new OA\Property(property: 'description', type: 'string', example: 'Cinema ticket'),
                ]
            )
        ),
        tags: ['Parent'],
        parameters: [
            new OA\Parameter(
                name: 'parentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Expense recorded',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'newBalance', type: 'number', format: 'float'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input or insufficient balance'),
            new OA\Response(response: 404, description: 'Parent or teenager not found'),
        ]
    )]
    public function recordExpense(string $parentId, array $data): array
    {
        $parent = $this->parentRepo->findById($parentId);

        if ($parent === null) {
            throw new \InvalidArgumentException('Parent not found');
        }

        $teenager = $this->teenagerRepo->findByParentAndName($parentId, $data['teenagerName']);

        if ($teenager === null) {
            throw new \InvalidArgumentException('Teenager not found');
        }

        $parent->recordExpense($teenager, $data['amount'], $data['description']);

        // Re-save teenager (balance and history modified)
        $this->teenagerRepo->save($teenager);

        return [
            'newBalance' => $teenager->getBalance(),
        ];
    }

    #[OA\Post(
        path: '/api/parent/{parentId}/allowance',
        summary: 'Set weekly allowance for teenager account',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['teenagerName', 'amount'],
                properties: [
                    new OA\Property(property: 'teenagerName', type: 'string', example: 'Emma'),
                    new OA\Property(property: 'amount', type: 'number', format: 'float', example: 20.0),
                ]
            )
        ),
        tags: ['Parent'],
        parameters: [
            new OA\Parameter(
                name: 'parentId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Weekly allowance set',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'weeklyAllowance', type: 'number', format: 'float'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 404, description: 'Parent or teenager not found'),
        ]
    )]
    public function setWeeklyAllowance(string $parentId, array $data): array
    {
        $parent = $this->parentRepo->findById($parentId);

        if ($parent === null) {
            throw new \InvalidArgumentException('Parent not found');
        }

        $teenager = $this->teenagerRepo->findByParentAndName($parentId, $data['teenagerName']);

        if ($teenager === null) {
            throw new \InvalidArgumentException('Teenager not found');
        }

        $parent->setWeeklyAllowance($teenager, $data['amount']);

        // Re-save teenager (allowance modified)
        $this->teenagerRepo->save($teenager);

        return [
            'weeklyAllowance' => $teenager->getWeeklyAllowance(),
        ];
    }
}
