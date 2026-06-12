<?php

namespace Iwea\Core;

class Api
{
    private const ALLOWED = ['getSCities', 'getSites'];

    public function __construct()
    {
        $method = $_REQUEST['method'] ?? '';
        if ($method === '' || !in_array($method, self::ALLOWED, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Unknown method']);
            return;
        }
        new Controller($method, [], true);
    }
}
