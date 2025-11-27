<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Generator;

// Generate OpenAPI specification from controllers
$openapi = Generator::scan([__DIR__ . '/../src/Api']);

// Output JSON
header('Content-Type: application/json');
echo $openapi->toJson();
