<?php

namespace Iwea\Weather;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
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

            $crawler = new Crawler($html);

            $blockDays = $crawler->filter('#blockDays');
            if ($blockDays->count() === 0) {
                error_log('SinoptikUa: #blockDays not found');
                return;
            }

            $dayNodes = $blockDays->filter('.tabs .main');
            if ($dayNodes->count() === 0) {
                error_log('SinoptikUa: no .tabs .main day nodes found');
                return;
            }

            $beginDate = new \DateTime();
            $i = 0;

            $dayNodes->each(function (Crawler $day) use (&$beginDate, &$i): void {
                if ($i > $this->days) {
                    return;
                }

                $maxNode = $day->filter('.max span');
                $minNode = $day->filter('.min span');

                if ($maxNode->count() === 0 || $minNode->count() === 0) {
                    $i++;
                    return;
                }

                if ($i === 0) {
                    $dd = clone $beginDate;
                } else {
                    $beginDate->modify('+1 day');
                    $dd = clone $beginDate;
                }

                $max = trim($maxNode->text());
                $min = trim($minNode->text());

                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $dd->format('Y-m-d'),
                    'min_temp' => (int) trim($min, '°C'),
                    'max_temp' => (int) trim($max, '°C'),
                ]);

                $i++;
            });
        } catch (\Throwable $e) {
            error_log('SinoptikUa: ' . $e->getMessage());
        }
    }
}
