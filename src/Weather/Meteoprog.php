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

            // The page embeds temperature arrays as JS variables `var hot` and `var cold`.
            // Regex extraction is intentional — DomCrawler cannot parse JS variables.

            // Isolate the script block that contains these variables
            $dataTemperature = $content;

            // Strip the jQuery wrapper if present
            $dataTemperature = str_replace('$(function() {', '', $dataTemperature);

            // Cut off everything from `var options` onwards
            $optionsPos = strpos($dataTemperature, 'var options');
            if ($optionsPos !== false) {
                $dataTemperature = substr($dataTemperature, 0, $optionsPos);
            }

            preg_match('/var hot =(.*)\svar cold/s', $dataTemperature, $hots);
            if (empty($hots[1])) {
                error_log('Meteoprog: could not extract var hot');
                return;
            }
            $hot = trim(rtrim(trim($hots[1]), ';'));

            preg_match('/var cold =(.*);/s', $dataTemperature, $colds);
            if (empty($colds[1])) {
                error_log('Meteoprog: could not extract var cold');
                return;
            }
            $cold = trim(rtrim(trim($colds[1]), ';'));

            $hotArray  = json_decode($hot, true);
            $coldArray = json_decode($cold, true);

            if (!is_array($hotArray) || !is_array($coldArray)) {
                error_log('Meteoprog: failed to JSON-decode temperature arrays');
                return;
            }

            $beginDate = new \DateTime();

            for ($i = 0; $i < $this->days; $i++) {
                if (!isset($hotArray[$i], $coldArray[$i])) {
                    break;
                }

                if ($i === 0) {
                    $dd = clone $beginDate;
                } else {
                    $beginDate->modify('+1 day');
                    $dd = clone $beginDate;
                }

                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $dd->format('Y-m-d'),
                    'min_temp' => $coldArray[$i][1],
                    'max_temp' => $hotArray[$i][1],
                ]);
            }
        } catch (\Throwable $e) {
            error_log('Meteoprog: ' . $e->getMessage());
        }
    }
}
