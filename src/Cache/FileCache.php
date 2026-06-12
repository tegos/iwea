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

        $file   = $files[0];
        $expiry = (int) substr(strrchr($file, '.'), 1);
        if ($expiry < time()) {
            unlink($file);
            return false;
        }

        $handle = fopen($file, 'r');
        if ($handle === false) {
            return false;
        }
        flock($handle, LOCK_SH);
        $stat = fstat($handle);
        $data = $stat['size'] > 0 ? fread($handle, $stat['size']) : '';
        flock($handle, LOCK_UN);
        fclose($handle);

        return $data !== '' ? json_decode($data, true) : false;
    }

    public function set(string $key, mixed $value): void
    {
        $this->delete($key);
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }

        $file   = $this->dir . 'cache.' . $this->sanitizeKey($key) . '.' . (time() + $this->ttl);
        $handle = fopen($file, 'w');
        if ($handle === false) {
            throw new \RuntimeException("FileCache: cannot open {$file} for writing");
        }
        flock($handle, LOCK_EX);
        fwrite($handle, (string) json_encode($value));
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
        return preg_replace('/[^A-Z0-9_-]/i', '', $key);
    }

    private function purgeExpired(): void
    {
        $files = glob($this->dir . 'cache.*');
        if (!$files) {
            return;
        }
        foreach ($files as $file) {
            $expiry = (int) substr(strrchr($file, '.'), 1);
            if ($expiry < time() && file_exists($file)) {
                unlink($file);
            }
        }
    }
}
