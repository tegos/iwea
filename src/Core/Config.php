<?php

namespace Iwea\Core;

class Config
{
    public static function get(string $key): mixed
    {
        return $_ENV[$key] ?? null;
    }
}
