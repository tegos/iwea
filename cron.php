<?php

use Dotenv\Dotenv;
use Iwea\Core\{Model, Helper};
use Iwea\Logger\Logger;
use Iwea\Weather\{
    OpenWeatherMap,
    AerisWeather,
    WorldWeatherOnline,
    OpenMeteo,
    SinoptikUa,
    Meteoprog,
    Interia,
};

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Europe/Kyiv');

$start  = microtime(true);
$logger = new Logger();
$tag    = 'CRON';

$logger->i($tag, '---------------------');
$logger->i($tag, 'Cron start');

$model  = new Model();
$helper = new Helper();

$sourceMap = [
    'OpenWeatherMap'   => OpenWeatherMap::class,
    'AerisWeather'     => AerisWeather::class,
    'WorldWeatherOnline' => WorldWeatherOnline::class,
    'OpenMeteo'        => OpenMeteo::class,
    'SinoptikUa'       => SinoptikUa::class,
    'Meteoprog'        => Meteoprog::class,
    'Interia'          => Interia::class,
];

$helper->setStateRun('run');
try {
    $cities = $model->getCities();
    $sites  = $model->getSites();

    foreach ($sites as $site) {
        $siteName = $site['name'];
        if (!isset($sourceMap[$siteName])) {
            $logger->i($tag, "Skip unknown site: {$siteName}");
            continue;
        }

        $logger->i($tag, "Site: {$siteName}");
        $siteClass = new $sourceMap[$siteName]();

        foreach ($cities as $city) {
            $siteClass->buildQuery($city);
            $siteClass->setSiteId((int)$site['id']);
            $siteClass->setCityId((int)$city['id']);
            $siteClass->addWeatherData();
        }
    }
} catch (\Throwable $e) {
    $logger->e($tag, $e->getMessage());
} finally {
    $helper->setStateRun('');
}

$logger->i($tag, 'Час виконання: ' . round(microtime(true) - $start, 4) . ' с.');
