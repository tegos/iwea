<?php

namespace Iwea\Core;

final class Locale
{
    private static array $days = [
        'Неділя', 'Понеділок', 'Вівторок', 'Середа', 'Четвер', 'П`ятниця', 'Субота',
    ];

    private static array $months = [
        'Jan' => 'Січень',  'Feb' => 'Лютий',    'Mar' => 'Березень',
        'Apr' => 'Квітень', 'May' => 'Травень',   'Jun' => 'Червень',
        'Jul' => 'Липень',  'Aug' => 'Серпень',   'Sep' => 'Вересень',
        'Oct' => 'Жовтень', 'Nov' => 'Листопад',  'Dec' => 'Грудень',
    ];

    private static array $polishDays = [
        'Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota',
    ];

    private static array $polishDaysShort = ['Nd', 'Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'Sb'];

    public static function day(int $w): string
    {
        return self::$days[$w];
    }

    public static function month(string $month, bool $low = false): string
    {
        $result = strtr($month, self::$months);
        return $low ? mb_strtolower($result) : $result;
    }

    public static function today(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone(Config::get('APP_TIMEZONE') ?? 'Europe/Kyiv'));
    }

    public static function daysDiff(\DateTime $start, \DateTime $end): int
    {
        return (int) $end->diff($start)->format('%a');
    }

    public static function pageTitle(\DateTime|int $date = 0): string
    {
        if ($date === 0 || !($date instanceof \DateTime)) {
            return 'Погода сьогодні';
        }
        return sprintf('Погода на %s %s, %s', self::month($date->format('M')), $date->format('d'), $date->format('Y'));
    }

    public static function polishDays(bool $small = false): array
    {
        return $small ? self::$polishDaysShort : self::$polishDays;
    }

    public static function indexOfPolishDay(string $day): int
    {
        foreach (self::$polishDays as $i => $d) {
            if (strcasecmp($d, $day) === 0) {
                return $i;
            }
        }
        return -1;
    }
}
