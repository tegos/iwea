<?php

namespace Iwea\Cache;

class FileCache
{
    private int $ttl;
    private string $dir;

    public function __construct(int $ttl = 10800)
    {
        $this->ttl = $ttl;
        $this->dir = dirname(__DIR__, 2) . '/data/cache/';
        $this->purgeExpired();
    }

    public function get(string $key): mixed
    {
        $files = glob($this->dir . 'cache.' . $this->sanitizeKey($key) . '.*');
        if (!$files) {
            return false;
        }
        $handle = fopen($files[0], 'r');
        flock($handle, LOCK_SH);
        $data = fread($handle, filesize($files[0]));
        flock($handle, LOCK_UN);
        fclose($handle);
        return unserialize($data);
    }

    public function set(string $key, mixed $value): void
    {
        $this->delete($key);
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
        $file = $this->dir . 'cache.' . $this->sanitizeKey($key) . '.' . (time() + $this->ttl);
        $handle = fopen($file, 'w');
        flock($handle, LOCK_EX);
        fwrite($handle, serialize($value));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    public function delete(string $key): void
    {
        $files = glob($this->dir . 'cache.' . $this->sanitizeKey($key) . '.*');
        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^A-Z0-9._-]/i', '', $key);
    }

    private function purgeExpired(): void
    {
        $files = glob($this->dir . 'cache.*');
        if (!$files) {
            return;
        }
        foreach ($files as $file) {
            $time = (int) substr(strrchr($file, '.'), 1);
            if ($time < time() && file_exists($file)) {
                unlink($file);
            }
        }
    }
}
