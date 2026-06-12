<?php

namespace Iwea\Core;

class Api
{
    public function __construct()
    {
        $method = $_REQUEST['method'] ?? '';
        if ($method === '') {
            return;
        }
        $args = array_filter($_POST, fn($v) => isset($v));
        new Controller($method, $args, true);
    }
}
