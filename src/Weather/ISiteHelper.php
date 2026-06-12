<?php

namespace Iwea\Weather;

interface ISiteHelper
{
    public function buildQuery(array $city): void;
    public function setSiteId(int $id): void;
    public function setCityId(int $id): void;
    public function addWeatherData(): void;
}
