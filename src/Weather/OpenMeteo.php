<?php

namespace Iwea\Weather;

use GuzzleHttp\Client;
use Iwea\Core\{Model, Helper, Config};

class OpenMeteo implements ISiteHelper
{
    private Client $client;
    private Model $model;
    private string $url = '';
    private int $siteId = 0;
    private int $cityId = 0;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 15]);
        $this->model  = new Model();
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
        $lat = (float) $city['lat'];
        $lon = (float) $city['lon'];

        $this->url = 'https://api.open-meteo.com/v1/forecast'
            . "?latitude={$lat}&longitude={$lon}"
            . '&daily=temperature_2m_max,temperature_2m_min'
            . '&timezone=Europe%2FKyiv'
            . '&forecast_days=7';
    }

    public function addWeatherData(): void
    {
        try {
            $response = $this->client->get($this->url);
            $data     = json_decode($response->getBody()->getContents(), true);

            if (empty($data['daily']['time'])) {
                return;
            }

            $times   = $data['daily']['time'];
            $maxTemps = $data['daily']['temperature_2m_max'];
            $minTemps = $data['daily']['temperature_2m_min'];

            foreach ($times as $i => $date) {
                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $date,
                    'min_temp' => $minTemps[$i],
                    'max_temp' => $maxTemps[$i],
                ]);
            }
        } catch (\Throwable $e) {
            error_log('OpenMeteo error: ' . $e->getMessage());
        }
    }
}
