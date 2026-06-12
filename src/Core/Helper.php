<?php

namespace Iwea\Core;

class Helper
{
    public function getDayUkr(int $w): string
    {
        $days = ['Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П`ятниця', 'Субота'];
        return $days[$w];
    }

    public function getMonthUkr(string $month, bool $low = false): string
    {
        $trans = [
            'Jan' => 'Січень', 'Feb' => 'Лютий',  'Mar' => 'Березень',
            'Apr' => 'Квітень', 'May' => 'Травень', 'Jun' => 'Червень',
            'Jul' => 'Липень',  'Aug' => 'Серпень', 'Sep' => 'Вересень',
            'Oct' => 'Жовтень', 'Nov' => 'Листопад','Dec' => 'Грудень',
        ];
        $result = strtr($month, $trans);
        return $low ? mb_strtolower($result) : $result;
    }

    public function group_assoc(array $array, string $key): array
    {
        $return = [];
        foreach ($array as $v) {
            $return[$v[$key]][] = $v;
        }
        return $return;
    }

    public function base64_url_encode(string $input): string
    {
        return strtr(base64_encode($input), '+/=', '-_~');
    }

    public function base64_url_decode(string $input): string
    {
        return base64_decode(strtr($input, '-_~', '+/='));
    }

    public function dateTimesToDays(\DateTime $start, \DateTime $end): int
    {
        return (int) $end->diff($start)->format('%a');
    }

    public function getTitlePage(\DateTime|int $date = 0): string
    {
        if ($date === 0 || !($date instanceof \DateTime)) {
            return 'Погода сьогодні';
        }
        $month = $this->getMonthUkr($date->format('M'));
        $day   = $date->format('d');
        $year  = $date->format('Y');
        return "Погода на {$month} {$day}, {$year}";
    }

    public function getToday(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone(Config::get('APP_TIMEZONE') ?? 'Europe/Kyiv'));
    }

    public function getPolishDays(bool $small = false): array
    {
        if ($small) {
            return ['Nd', 'Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'Sb'];
        }
        return ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];
    }

    public function getIndexOfPolishDay(string $day): int
    {
        foreach ($this->getPolishDays() as $i => $d) {
            if (strcasecmp($d, $day) === 0) {
                return $i;
            }
        }
        return -1;
    }

    public function getNextSite(array $sites): string
    {
        $f    = dirname(__DIR__, 2) . '/data/sync.log';
        $site = trim((string) @file_get_contents($f));
        return in_array($site, $sites) ? $site : $sites[0];
    }

    public function setNextSite(string $site): void
    {
        @file_put_contents(dirname(__DIR__, 2) . '/data/sync.log', $site);
    }

    public function getStateRun(): string
    {
        return trim((string) @file_get_contents(dirname(__DIR__, 2) . '/data/state'));
    }

    public function setStateRun(string $state): void
    {
        @file_put_contents(dirname(__DIR__, 2) . '/data/state', $state);
    }
}
