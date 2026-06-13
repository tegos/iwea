<?php

namespace Iwea\Tests;

use Iwea\Core\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    public function testDayReturnsCorrectUkrainianDay(): void
    {
        $this->assertSame('Понеділок', Locale::day(1));
        $this->assertSame('Неділя',   Locale::day(0));
        $this->assertSame('Субота',   Locale::day(6));
    }

    public function testMonthReturnsCorrectUkrainianMonth(): void
    {
        $this->assertSame('Червень', Locale::month('Jun'));
        $this->assertSame('Грудень', Locale::month('Dec'));
        $this->assertSame('червень', Locale::month('Jun', true));
    }

    public function testDaysDiffReturnsCorrectCount(): void
    {
        $start = new \DateTime('2026-06-01');
        $end   = new \DateTime('2026-06-08');
        $this->assertSame(7, Locale::daysDiff($start, $end));
    }

    public function testPageTitleReturnsTodayStringForZero(): void
    {
        $this->assertSame('Погода сьогодні', Locale::pageTitle(0));
    }

    public function testIndexOfPolishDayReturnsCorrectIndex(): void
    {
        $this->assertSame(1, Locale::indexOfPolishDay('Poniedziałek'));
        $this->assertSame(0, Locale::indexOfPolishDay('Niedziela'));
        $this->assertSame(-1, Locale::indexOfPolishDay('Blah'));
    }
}
