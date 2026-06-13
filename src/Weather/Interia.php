<?php

namespace Iwea\Weather;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Iwea\Core\{Model, Locale};

class Interia implements ISiteHelper
{
    private Client $client;
    private Model $model;
    private string $url = '';
    private int $siteId = 0;
    private int $cityId = 0;

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
        $cityName = $city['name_pl'];
        $cidPl    = $city['cid_pl'];
        $this->url = 'https://pogoda.interia.pl/'
            . "prognoza-dlugoterminowa-{$cityName},cId,{$cidPl}";
    }

    public function addWeatherData(): void
    {
        try {
            $html = $this->client->get($this->url)->getBody()->getContents();

            $crawler = new Crawler($html);

            $list = $crawler->filter('.weather-forecast-longterm-list');
            if ($list->count() === 0) {
                error_log('Interia: .weather-forecast-longterm-list not found');
                return;
            }

            $list->filter('.weather-forecast-longterm-list-entry')->each(function (Crawler $li): void {
                $dateNode = $li->filter('.weather-forecast-longterm-list-entry-hour .date');
                $dayNode  = $li->filter('.weather-forecast-longterm-list-entry-hour .day');

                if ($dateNode->count() === 0 || $dayNode->count() === 0) {
                    return;
                }

                $date = trim($dateNode->text());   // e.g. "14.06"
                $day  = trim($dayNode->text());    // e.g. "Wtorek"

                $dateW = Locale::indexOfPolishDay($day);

                $date .= '.' . date('Y');

                $dateTime   = \DateTime::createFromFormat('d.m.Y', $date);
                if ($dateTime === false) {
                    return;
                }

                $curDateW = (int) $dateTime->format('w');

                // Resolve year-boundary ambiguity: if the day-of-week from the
                // Polish name doesn't match what the parsed date gives, shift +1 year.
                if ($dateW !== -1 && $dateW !== $curDateW) {
                    $dateTime->modify('+1 year');
                }

                $maxNode = $li->filter('.weather-forecast-longterm-list-entry-forecast-temp');
                $minNode = $li->filter('.weather-forecast-longterm-list-entry-forecast-lowtemp');

                if ($maxNode->count() === 0 || $minNode->count() === 0) {
                    return;
                }

                $max = trim($maxNode->text());
                $min = trim($minNode->text());

                $this->model->addWeatherRecord([
                    'site_id'  => $this->siteId,
                    'city_id'  => $this->cityId,
                    'date'     => $dateTime->format('Y-m-d'),
                    'min_temp' => (int) trim($min, '°C'),
                    'max_temp' => (int) trim($max, '°C'),
                ]);
            });
        } catch (\Throwable $e) {
            error_log('Interia: ' . $e->getMessage());
        }
    }

}
