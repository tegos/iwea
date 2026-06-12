<?php

namespace Iwea\Weather;

use GuzzleHttp\Client;
use Iwea\Core\{Model, Helper, Config};

class OpenWeatherMap implements ISiteHelper
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
        $key = Config::get('OWM_API_KEY');

        $this->url = 'https://api.openweathermap.org/data/2.5/forecast'
            . "?lat={$lat}&lon={$lon}&appid={$key}&units=metric&cnt=40";
    }

    public function addWeatherData(): void
    {
        try {
            $response = $this->client->get($this->url);
            $data     = json_decode($response->getBody()->getContents(), true);

            if (empty($data['list'])) {
                return;
            }

            // Group list[] entries by date, tracking min of temp_min and max of temp_max
            $days = [];
            foreach ($data['list'] as $entry) {
                $date   = date('Y-m-d', $entry['dt']);
                $minT   = $entry['main']['temp_min'];
                $maxT   = $entry['main']['temp_max'];

                if (!isset($days[$date])) {
                    $days[$date] = ['min' => $minT, 'max' => $maxT];
                } else {
                    if ($minT < $days[$date]['min']) {
                        $days[$date]['min'] = $minT;
                    }
                    if ($maxT > $days[$date]['max']) {
                        $days[$date]['max'] = $maxT;
                    }
                }
            }

            foreach ($days as $date => $temps) {
                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $date,
                    'min_temp' => $temps['min'],
                    'max_temp' => $temps['max'],
                ]);
            }
        } catch (\Throwable $e) {
            error_log('OpenWeatherMap error: ' . $e->getMessage());
        }
    }
}
