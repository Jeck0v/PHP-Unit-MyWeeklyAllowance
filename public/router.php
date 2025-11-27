<?php

// Router for PHP built-in server

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API routes
if (str_starts_with($uri, '/api/')) {
    require __DIR__ . '/api.php';
    return true;
}

// Swagger spec
if ($uri === '/swagger.php') {
    require __DIR__ . '/swagger.php';
    return true;
}

// Default: serve static files or index.html
if ($uri === '/' || $uri === '/index.html') {
    require __DIR__ . '/index.html';
    return true;
}

// If file exists, serve it
if (file_exists(__DIR__ . $uri)) {
    return false; // Let PHP server handle it
}

// 404
http_response_code(404);
echo '404 Not Found';
return true;
