<?php

namespace Iwea\Weather;

use GuzzleHttp\Client;
use Iwea\Core\Model;

class Meteoprog implements ISiteHelper
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
        $cityName = $city['name_tr'];
        $this->url = 'https://www.meteoprog.ua/ua/review/'
            . $cityName . '/?_pjax=div%23reviewforecast_pjax_container';
    }

    public function addWeatherData(): void
    {
        try {
            $content = $this->client->get($this->url)->getBody()->getContents();

            // The page embeds forecast as a JS variable `var data = [...]`.
            // Regex extraction is intentional — DomCrawler cannot parse JS variables.
            if (!preg_match('/var data\s*=\s*(\[.*?\]);/s', $content, $m)) {
                error_log('Meteoprog: could not extract var data');
                return;
            }

            $entries = json_decode($m[1], true);
            if (!is_array($entries)) {
                error_log('Meteoprog: failed to JSON-decode var data');
                return;
            }

            $count = 0;

            foreach ($entries as $entry) {
                if ($count >= 7) {
                    break;
                }

                // Skip hidden/past entries
                if (isset($entry['hide'])) {
                    continue;
                }

                if (!isset($entry['date'], $entry['max'], $entry['min'])) {
                    continue;
                }

                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $entry['date'],
                    'min_temp' => (int) $entry['min'],
                    'max_temp' => (int) $entry['max'],
                ]);

                $count++;
            }
        } catch (\Throwable $e) {
            error_log('Meteoprog: ' . $e->getMessage());
        }
    }
}
