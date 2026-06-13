<?php

namespace Iwea\Tests;

use Iwea\Core\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    private Helper $h;

    protected function setUp(): void
    {
        $this->h = new Helper();
    }

    public function testGetDayUkrReturnsCorrectDay(): void
    {
        $this->assertSame('Понеділок', $this->h->getDayUkr(1));
        $this->assertSame('Неділя',   $this->h->getDayUkr(0));
        $this->assertSame('Субота',   $this->h->getDayUkr(6));
    }

    public function testGetMonthUkrReturnsCorrectMonth(): void
    {
        $this->assertSame('Червень',  $this->h->getMonthUkr('Jun'));
        $this->assertSame('Грудень',  $this->h->getMonthUkr('Dec'));
        $this->assertSame('червень',  $this->h->getMonthUkr('Jun', true));
    }

    public function testGroupAssocGroupsByKey(): void
    {
        $input = [
            ['type' => 'a', 'v' => 1],
            ['type' => 'b', 'v' => 2],
            ['type' => 'a', 'v' => 3],
        ];
        $result = $this->h->group_assoc($input, 'type');
        $this->assertCount(2, $result['a']);
        $this->assertCount(1, $result['b']);
    }

    public function testBase64UrlRoundtrip(): void
    {
        $original = 'hello/world+test=';
        $encoded  = $this->h->base64_url_encode($original);
        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
        $this->assertSame($original, $this->h->base64_url_decode($encoded));
    }

    public function testDateTimesToDaysReturnsCorrectDiff(): void
    {
        $start = new \DateTime('2026-06-01');
        $end   = new \DateTime('2026-06-08');
        $this->assertSame(7, $this->h->dateTimesToDays($start, $end));
    }
}
