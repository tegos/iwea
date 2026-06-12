<?php

use Dotenv\Dotenv;
use Iwea\Core\Controller;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Kyiv');
ini_set('memory_limit', '256M');

if ($_SERVER['QUERY_STRING'] !== '' && $_SERVER['QUERY_STRING'] !== '/' && !isset($_GET['action'])) {
    header('Location: /', true, 302);
    exit;
}

new Controller();
