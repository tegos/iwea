<?php

namespace Iwea\Weather;

use GuzzleHttp\Client;
use Iwea\Core\{Model, Helper, Config};

class WorldWeatherOnline implements ISiteHelper
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
        $key = Config::get('WWO_API_KEY');

        $this->url = 'https://api.worldweatheronline.com/premium/v1/weather.ashx'
            . "?key={$key}&q={$lat},{$lon}&format=json&num_of_days=7&tp=24";
    }

    public function addWeatherData(): void
    {
        try {
            $response = $this->client->get($this->url);
            $data     = json_decode($response->getBody()->getContents(), true);

            if (empty($data['data']['weather'])) {
                return;
            }

            $periods = $data['data']['weather'];

            foreach ($periods as $period) {
                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $period['date'],
                    'min_temp' => $period['mintempC'],
                    'max_temp' => $period['maxtempC'],
                ]);
            }
        } catch (\Throwable $e) {
            error_log('WorldWeatherOnline error: ' . $e->getMessage());
        }
    }
}
