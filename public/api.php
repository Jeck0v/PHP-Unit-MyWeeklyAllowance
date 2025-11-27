<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MyWeeklyAllowance\Api\AuthController;
use MyWeeklyAllowance\Api\ParentController;
use MyWeeklyAllowance\Api\TeenagerController;

// Set JSON response header
header('Content-Type: application/json');

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Get JSON body for POST requests
$data = [];
if ($method === 'POST') {
    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody, true) ?? [];
}

try {
    // Route matching
    if ($uri === '/api/register' && $method === 'POST') {
        $controller = new AuthController();
        $result = $controller->register($data);
        http_response_code(201);
        echo json_encode($result);

    } elseif ($uri === '/api/login' && $method === 'POST') {
        $controller = new AuthController();
        $result = $controller->login($data);
        http_response_code(200);
        echo json_encode($result);

    } elseif ($uri === '/api/parent' && $method === 'POST') {
        $controller = new ParentController();
        $result = $controller->createParent($data);
        http_response_code(201);
        echo json_encode($result);

    } elseif (preg_match('#^/api/parent/([^/]+)/teenager$#', $uri, $matches) && $method === 'POST') {
        $parentId = $matches[1];
        $controller = new ParentController();
        $result = $controller->createTeenagerAccount($parentId, $data);
        http_response_code(201);
        echo json_encode($result);

    } elseif (preg_match('#^/api/parent/([^/]+)/accounts$#', $uri, $matches) && $method === 'GET') {
        $parentId = $matches[1];
        $controller = new ParentController();
        $result = $controller->getAccounts($parentId);
        http_response_code(200);
        echo json_encode($result);

    } elseif (preg_match('#^/api/parent/([^/]+)/deposit$#', $uri, $matches) && $method === 'POST') {
        $parentId = $matches[1];
        $controller = new ParentController();
        $result = $controller->deposit($parentId, $data);
        http_response_code(200);
        echo json_encode($result);

    } elseif (preg_match('#^/api/parent/([^/]+)/expense$#', $uri, $matches) && $method === 'POST') {
        $parentId = $matches[1];
        $controller = new ParentController();
        $result = $controller->recordExpense($parentId, $data);
        http_response_code(200);
        echo json_encode($result);

    } elseif (preg_match('#^/api/parent/([^/]+)/allowance$#', $uri, $matches) && $method === 'POST') {
        $parentId = $matches[1];
        $controller = new ParentController();
        $result = $controller->setWeeklyAllowance($parentId, $data);
        http_response_code(200);
        echo json_encode($result);

    } elseif (preg_match('#^/api/teenager/([^/]+)/([^/]+)/balance$#', $uri, $matches) && $method === 'GET') {
        $parentId = $matches[1];
        $teenagerName = urldecode($matches[2]);
        $controller = new TeenagerController();
        $result = $controller->getBalance($parentId, $teenagerName);
        http_response_code(200);
        echo json_encode($result);

    } elseif (preg_match('#^/api/teenager/([^/]+)/([^/]+)/history$#', $uri, $matches) && $method === 'GET') {
        $parentId = $matches[1];
        $teenagerName = urldecode($matches[2]);
        $controller = new TeenagerController();
        $result = $controller->getHistory($parentId, $teenagerName);
        http_response_code(200);
        echo json_encode($result);

    } elseif (preg_match('#^/api/teenager/([^/]+)/([^/]+)/allowance/apply$#', $uri, $matches) && $method === 'POST') {
        $parentId = $matches[1];
        $teenagerName = urldecode($matches[2]);
        $controller = new TeenagerController();
        $result = $controller->applyWeeklyAllowance($parentId, $teenagerName);
        http_response_code(200);
        echo json_encode($result);

    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }

} catch (\InvalidArgumentException $e) {
    // Handle validation errors
    $statusCode = match($e->getMessage()) {
        'Account temporarily locked' => 423,
        'Invalid credentials' => 401,
        default => 400
    };

    http_response_code($statusCode);
    echo json_encode(['error' => $e->getMessage()]);

} catch (\Throwable $e) {
    // Handle unexpected errors
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'message' => $e->getMessage()]);
}
