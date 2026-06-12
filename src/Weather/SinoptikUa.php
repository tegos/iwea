<?php

namespace Iwea\Weather;

use GuzzleHttp\Client;
use Iwea\Core\Model;

class SinoptikUa implements ISiteHelper
{
    private Client $client;
    private Model $model;
    private string $url = '';
    private int $siteId = 0;
    private int $cityId = 0;
    private int $days = 8;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 15,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (compatible; iWea/2.0)'],
        ]);
        $this->model = new Model();
    }

    public function setSiteId(int $id): void
    {
        $this->siteId = $id;
    }

    public function setCityId(int $id): void
    {
        $this->cityId = $id;
    }

    public function buildQuery(array $city): void
    {
        $cityName = $city['name_sinoptik'];
        $this->url = 'https://ua.sinoptik.ua/' . "погода-{$cityName}/10-днів";
    }

    public function addWeatherData(): void
    {
        try {
            $html = $this->client->get($this->url)->getBody()->getContents();

            if (!preg_match('/<script[^>]*id="preloaded-state"[^>]*>(.*?)<\/script>/s', $html, $m)) {
                error_log('SinoptikUa: preloaded-state script block not found');
                return;
            }

            $data = json_decode($m[1], true);
            if (!isset($data['weather']['data']['forecast']) || !is_array($data['weather']['data']['forecast'])) {
                error_log('SinoptikUa: forecast data not found in preloaded-state JSON');
                return;
            }

            $forecast = $data['weather']['data']['forecast'];
            $count    = 0;

            foreach ($forecast as $day) {
                if ($count >= 7) {
                    break;
                }

                if (!isset($day['date'], $day['temperature']['max'], $day['temperature']['min'])) {
                    continue;
                }

                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $day['date'],
                    'min_temp' => (int) $day['temperature']['min'],
                    'max_temp' => (int) $day['temperature']['max'],
                ]);

                $count++;
            }
        } catch (\Throwable $e) {
            error_log('SinoptikUa: ' . $e->getMessage());
        }
    }
}
