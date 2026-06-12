<?php

namespace Iwea\Weather;

use GuzzleHttp\Client;
use Iwea\Core\{Model, Helper, Config};

class AerisWeather implements ISiteHelper
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
        $lat      = (float) $city['lat'];
        $lon      = (float) $city['lon'];
        $clientId = Config::get('AERIS_CLIENT_ID');
        $secret   = Config::get('AERIS_CLIENT_SECRET');

        $this->url = "https://api.aerisapi.com/forecasts/{$lat},{$lon}"
            . "?client_id={$clientId}&client_secret={$secret}&filter=day&limit=7";
    }

    public function addWeatherData(): void
    {
        try {
            $response = $this->client->get($this->url);
            $data     = json_decode($response->getBody()->getContents(), true);

            if (empty($data['response'][0]['periods'])) {
                return;
            }

            $periods = $data['response'][0]['periods'];

            foreach ($periods as $period) {
                // dateTimeISO is like "2024-06-12T07:00:00+03:00"
                $date = date('Y-m-d', strtotime($period['dateTimeISO']));

                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $date,
                    'min_temp' => $period['minTempC'],
                    'max_temp' => $period['maxTempC'],
                ]);
            }
        } catch (\Throwable $e) {
            error_log('AerisWeather error: ' . $e->getMessage());
        }
    }
}
