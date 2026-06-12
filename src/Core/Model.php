<?php

namespace Iwea\Core;

use Iwea\Cache\FileCache;
use Iwea\Database\Database;

class Model extends Helper
{
    private Database $db;
    private FileCache $cache;

    public function __construct()
    {
        $this->db = new Database(
            Config::get('DB_HOST'),
            Config::get('DB_NAME'),
            Config::get('DB_USER'),
            Config::get('DB_PASS')
        );
        $this->cache = new FileCache(3600 * 3);
    }

    private function query(string $sql, array $params = []): array
    {
        $key    = md5($sql . serialize($params));
        $cached = $this->cache->get($key);
        if ($cached !== false) {
            return $cached;
        }
        $data = $this->db->query($sql, $params);
        $this->cache->set($key, $data);
        return $data;
    }

    public function addWeatherRecord(array $data): void
    {
        $this->db->execute(
            "INSERT INTO weather (site_id, city_id, date, min_temp, max_temp) VALUES (?, ?, ?, ?, ?)",
            [(int)$data['site_id'], (int)$data['city_id'], $data['date'], $data['min_temp'], $data['max_temp']]
        );
    }

    public function getCityName(int $cityId): string
    {
        $rows = $this->query("SELECT name FROM city WHERE id = ?", [$cityId]);
        return $rows[0]['name'] ?? '';
    }

    public function getSite(int $siteId): array
    {
        $rows = $this->query("SELECT * FROM site WHERE id = ?", [$siteId]);
        return $rows[0] ?? [];
    }

    public function getSites(string|false $search = false): array
    {
        if ($search !== false) {
            return $this->query("SELECT * FROM site WHERE name LIKE ? AND status = 1", ["%{$search}%"]);
        }
        return $this->query("SELECT * FROM site WHERE status = 1");
    }

    public function getCities(string|false $search = false): array
    {
        if ($search !== false) {
            return $this->query(
                "SELECT * FROM city WHERE name LIKE ? OR name_iso LIKE ?",
                ["%{$search}%", "%{$search}%"]
            );
        }
        return $this->query("SELECT * FROM city");
    }

    public function getCookieSiteId(): int
    {
        $default = (int) (Config::get('APP_DEFAULT_SITE') ?? 2);
        $id = (int) ($_COOKIE['site_id'] ?? $default);
        return $id > 0 ? $id : $default;
    }

    public function getCookieCityId(): int
    {
        $default = (int) (Config::get('APP_DEFAULT_CITY') ?? 3);
        $id = (int) ($_COOKIE['city_id'] ?? $default);
        return $id > 0 ? $id : $default;
    }

    public function userExists(string|int $identifier): bool
    {
        if (is_int($identifier)) {
            $rows = $this->db->query("SELECT EXISTS(SELECT 1 FROM user WHERE user_id = ?) AS yes", [$identifier]);
        } else {
            $rows = $this->db->query("SELECT EXISTS(SELECT 1 FROM user WHERE email = ?) AS yes", [$identifier]);
        }
        return (bool) ($rows[0]['yes'] ?? false);
    }

    public function addUser(array $data): void
    {
        $hash = password_hash($data['pass'], PASSWORD_BCRYPT);
        $this->db->execute(
            "INSERT INTO user (pass, email, name) VALUES (?, ?, ?)",
            [$hash, $data['email'], $data['name']]
        );
    }

    public function getUserByEmail(string $email): array
    {
        $rows = $this->db->query("SELECT * FROM user WHERE email = ?", [$email]);
        return $rows[0] ?? [];
    }

    public function getUserById(int $userId): array
    {
        $rows = $this->db->query("SELECT * FROM user WHERE user_id = ?", [$userId]);
        return $rows[0] ?? [];
    }

    public function setMessage(string $key, mixed $message): void
    {
        $_SESSION[$key] = $message;
    }

    public function getMessage(string $key): mixed
    {
        return $_SESSION[$key] ?? false;
    }

    public function unsetMessage(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function getDataAnalyze(): array
    {
        $cityId = $this->getCookieCityId();
        $days   = isset($_GET['days']) ? (int)$_GET['days'] - 1 : 6;

        $startDate     = new \DateTime();
        $startDatePlus = (new \DateTime())->modify('+1 day');
        $endDate       = (new \DateTime())->modify("-{$days} day");

        $sql = "SELECT site_id, site.name, city_id, date, DATE(date_write) AS datew,
                    AVG(min_temp) AS min, AVG(max_temp) AS max
                FROM weather
                JOIN site ON weather.site_id = site.id
                WHERE city_id = ?
                AND DATE(date) = ?
                AND date_write BETWEEN ? AND ?
                AND status = 1
                GROUP BY DATE(date_write), site_id
                ORDER BY site_id, date_write";

        $data = $this->query($sql, [
            $cityId,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $startDatePlus->format('Y-m-d'),
        ]);

        $rez = [];
        foreach ($data as $value) {
            $date  = \DateTime::createFromFormat('Y-m-d', $value['date']);
            $datew = \DateTime::createFromFormat('Y-m-d', $value['datew']);
            $rez[] = [
                'datew' => ($startDate->format('Y-m-d') !== $datew->format('Y-m-d'))
                    ? $datew->format('d.m.Y') : 'Сьогодні',
                'date'  => $date->format('d.m.Y'),
                'name'  => $value['name'],
                'min'   => round((float)$value['min'], 2),
                'max'   => round((float)$value['max'], 2),
            ];
        }
        return $this->group_assoc($rez, 'name');
    }

    public function getWeatherAll(string|int $date): array
    {
        $cityId    = $this->getCookieCityId();
        $startDate = $date ? new \DateTime((string)$date) : $this->getToday();
        $endDate   = (clone $startDate)->modify('+6 day');

        $sql = "SELECT site_id, site.name, city_id, date,
                    AVG(min_temp) AS min, AVG(max_temp) AS max
                FROM weather
                JOIN site ON weather.site_id = site.id
                WHERE city_id = ?
                AND DATE(date_write) = ?
                AND date BETWEEN ? AND ?
                AND status = 1
                GROUP BY site_id, date
                ORDER BY site_id, date";

        $data      = $this->query($sql, [
            $cityId,
            $startDate->format('Y-m-d'),
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        ]);

        $cityName   = $this->getCityName($cityId);
        $sites      = $this->getSites();
        $categories = [];
        $dataTemps  = [];

        foreach ($data as $value) {
            $d = \DateTime::createFromFormat('Y-m-d', $value['date']);
            $categories[] = $d->format('d.m.Y');
            $dataTemps[]  = [
                'date'    => $d->format('d.m'),
                'min'     => $value['min'],
                'max'     => $value['max'],
                'site_id' => $value['site_id'],
            ];
        }
        $categories = array_unique($categories);

        $forecasts = [];
        foreach ($categories as $category) {
            $d   = \DateTime::createFromFormat('d.m.Y', $category);
            $min = 0; $max = 0; $k = 0;
            $dm  = $d->format('d.m');
            foreach ($dataTemps as $v) {
                if ($v['date'] === $dm) {
                    $min += $v['min'];
                    $max += $v['max'];
                    $k++;
                }
            }
            $forecasts[] = [
                'day'      => $this->getDayUkr((int)$d->format('w')),
                'day_date' => $dm,
                'min'      => $k ? round($min / $k) : 0,
                'max'      => $k ? round($max / $k) : 0,
            ];
        }

        $series = []; $seriesMax = [];
        foreach ($sites as $site) {
            $t  = ['name' => $site['name'], 'color' => $site['color'], 'marker' => ['symbol' => 'square'], 'data' => []];
            $tm = $t;
            foreach ($data as $v) {
                if ($v['site_id'] == $site['id']) {
                    $t['data'][]  = round($v['min']);
                    $tm['data'][] = round($v['max']);
                }
            }
            $series[]    = $t;
            $seriesMax[] = $tm;
        }

        $avg    = ['name' => 'Середнє', 'color' => 'rgb(85, 191, 59)', 'marker' => ['symbol' => 'diamond'], 'data' => []];
        $avgMax = $avg;
        foreach ($forecasts as $f) {
            $avg['data'][]    = round($f['min']);
            $avgMax['data'][] = round($f['max']);
        }
        $series[]    = $avg;
        $seriesMax[] = $avgMax;

        return [
            'categories' => $categories,
            'city_name'  => $cityName,
            'series'     => $series,
            'series_max' => $seriesMax,
            'forecasts'  => $forecasts,
        ];
    }

    public function getWeather(): array
    {
        $cityId    = $this->getCookieCityId();
        $siteId    = $this->getCookieSiteId();
        $startDate = $this->getToday();
        $endDate   = (clone $startDate)->modify('+6 day');

        $sql = "SELECT site_id, site.name, city_id, date,
                    AVG(min_temp) AS min, AVG(max_temp) AS max
                FROM weather
                JOIN site ON weather.site_id = site.id
                WHERE city_id = ?
                AND DATE(date_write) = CURDATE()
                AND site_id = ?
                AND date BETWEEN ? AND ?
                GROUP BY site_id, date
                ORDER BY site_id, date";

        $data     = $this->query($sql, [$cityId, $siteId, $startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        $cityName = $this->getCityName($cityId);
        $sites    = $this->getSites();

        $categories = [];
        foreach ($data as $value) {
            $d = \DateTime::createFromFormat('Y-m-d', $value['date']);
            $categories[] = $d->format('d.m.Y');
        }
        $categories = array_unique($categories);

        $series = [];
        foreach ($sites as $site) {
            if ($site['id'] != $siteId) {
                continue;
            }
            $minS = ['name' => $site['name'] . ' - min', 'color' => $site['color'], 'marker' => ['symbol' => 'square'], 'data' => []];
            $maxS = ['name' => $site['name'] . ' - max', 'color' => $site['color'], 'marker' => ['symbol' => 'square'], 'data' => []];
            foreach ($data as $v) {
                if ($v['site_id'] == $site['id']) {
                    $minS['data'][] = round($v['min']);
                    $maxS['data'][] = round($v['max']);
                }
            }
            $series[] = $minS;
            $series[] = $maxS;
        }

        $forecasts = [];
        foreach ($categories as $i => $category) {
            $d = \DateTime::createFromFormat('d.m.Y', $category);
            $forecasts[] = [
                'day'      => $this->getDayUkr((int)$d->format('w')),
                'day_date' => $d->format('d.m'),
                'min'      => $series[0]['data'][$i] ?? 0,
                'max'      => $series[1]['data'][$i] ?? 0,
            ];
        }

        return [
            'categories' => $categories,
            'city_name'  => $cityName,
            'series'     => $series,
            'forecasts'  => $forecasts,
        ];
    }

    public function getSCities(): array
    {
        $r = [];
        foreach ($this->query("SELECT * FROM city") as $d) {
            $r[] = $d['name'];
            $r[] = $d['name_iso'];
        }
        return $r;
    }

    public function getSitesForSelect(): array
    {
        $siteId = $this->getCookieSiteId();
        return array_map(fn($d) => [
            'value'    => $d['id'],
            'selected' => $siteId == $d['id'],
            'text'     => $d['name'],
            'imageSrc' => $d['image_url'],
        ], $this->getSites());
    }
}
