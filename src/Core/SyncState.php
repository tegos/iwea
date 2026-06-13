<?php

namespace Iwea\Core;

final class SyncState
{
    private static string $logFile   = '';
    private static string $stateFile = '';

    private static function logFile(): string
    {
        if (self::$logFile === '') {
            self::$logFile = dirname(__DIR__, 2) . '/data/sync.log';
        }
        return self::$logFile;
    }

    private static function stateFile(): string
    {
        if (self::$stateFile === '') {
            self::$stateFile = dirname(__DIR__, 2) . '/data/state';
        }
        return self::$stateFile;
    }

    public static function getNextSite(array $sites): string
    {
        $site = trim((string) @file_get_contents(self::logFile()));
        return in_array($site, $sites) ? $site : $sites[0];
    }

    public static function setNextSite(string $site): void
    {
        @file_put_contents(self::logFile(), $site);
    }

    public static function getRunState(): string
    {
        return trim((string) @file_get_contents(self::stateFile()));
    }

    public static function setRunState(string $state): void
    {
        @file_put_contents(self::stateFile(), $state);
    }
}
