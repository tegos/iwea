<?php

use Dotenv\Dotenv;
use Iwea\Core\Api;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Kyiv');

header('Content-Type: application/json');

set_exception_handler(function (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
});

new Api();
