<?php

namespace Iwea\Logger;

class Logger
{
    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = rtrim($dir, '/');
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
    }

    public function i(string $tag, string $message): void
    {
        $this->write('INFO', $tag, $message);
    }

    public function e(string $tag, string $message): void
    {
        $this->write('ERROR', $tag, $message);
    }

    private function write(string $level, string $tag, string $message): void
    {
        $line = date('Y-m-d H:i:s') . " [{$level}] [{$tag}] {$message}" . PHP_EOL;
        file_put_contents($this->dir . '/' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}
