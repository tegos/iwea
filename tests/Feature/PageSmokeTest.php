<?php

namespace Iwea\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Smoke tests that require a running server (Docker Compose).
 * Skipped automatically when the server is unreachable.
 * Run locally: docker compose up -d && vendor/bin/phpunit --group feature
 */
class PageSmokeTest extends TestCase
{
    private static Client $http;
    private static string $base;

    public static function setUpBeforeClass(): void
    {
        self::$base = (string) (getenv('APP_URL') ?: 'http://localhost:8080');
        self::$http = new Client([
            'base_uri'    => self::$base,
            'timeout'     => 5,
            'http_errors' => false,
            'headers'     => ['Accept' => 'text/html'],
        ]);
    }

    private function get(string $path): ResponseInterface
    {
        try {
            return self::$http->get($path);
        } catch (ConnectException $e) {
            self::markTestSkipped('Server not reachable — start Docker first: docker compose up -d');
        }
    }

    #[Group('feature')]
    public function testHomePageReturns200(): void
    {
        $res = $this->get('/');
        $this->assertSame(200, $res->getStatusCode());
    }

    #[Group('feature')]
    public function testHomePageContainsForecastWidget(): void
    {
        $body = (string) $this->get('/')->getBody();
        $this->assertStringContainsString('forecast-container', $body);
        $this->assertStringContainsString('iWEA', $body);
    }

    #[Group('feature')]
    public function testComparePageReturns200(): void
    {
        $res = $this->get('/compare');
        $this->assertSame(200, $res->getStatusCode());
        $body = (string) $res->getBody();
        $this->assertStringContainsString('container-chart-min', $body);
        $this->assertStringContainsString('container-chart-max', $body);
    }

    #[Group('feature')]
    public function testDiffPageReturns200(): void
    {
        $res = $this->get('/diff');
        $this->assertSame(200, $res->getStatusCode());
        $this->assertStringContainsString('source-list-sites', (string) $res->getBody());
    }

    #[Group('feature')]
    public function testAnalyticsPageReturns200(): void
    {
        $res = $this->get('/analytics');
        $this->assertSame(200, $res->getStatusCode());
        $this->assertStringContainsString('table-result-distance', (string) $res->getBody());
    }

    #[Group('feature')]
    public function testSearchPageReturns200(): void
    {
        $res = $this->get('/search?search=Одеса');
        $this->assertSame(200, $res->getStatusCode());
    }

    #[Group('feature')]
    public function test404PageReturnsNotFound(): void
    {
        $res = $this->get('/this-does-not-exist');
        $this->assertSame(404, $res->getStatusCode());
    }

    #[Group('feature')]
    public function testLoginPageReturns200(): void
    {
        $res = $this->get('/login');
        $this->assertSame(200, $res->getStatusCode());
        $this->assertStringContainsString('email', (string) $res->getBody());
    }
}
