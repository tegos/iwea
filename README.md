# iWea

iWea — web weather aggregator that compares forecasts from multiple sources on interactive Highcharts graphs.

> Compares temperature forecasts from 5 active sources side by side, displayed on a single interactive chart.

---

## Weather Sources

| Source | Type | Status |
|---|---|---|
| OpenWeatherMap | API | ✅ Active |
| Open-Meteo | API | ✅ Active (free, no key needed) |
| Sinoptik UA | Scraper | ✅ Active |
| Meteoprog | Scraper | ✅ Active |
| Interia | Scraper | ✅ Active |
| AerisWeather | API | ⏸ Disabled (no free tier) |
| WorldWeatherOnline | API | ⏸ Disabled (paid only) |
| Dark Sky | API | ❌ Removed (shut down 2019) |
| Yahoo Weather | API | ❌ Removed (shut down 2017) |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2 |
| Database | MySQL 8 |
| HTTP client | Guzzle 7 |
| HTML parsing | Symfony DomCrawler + CssSelector |
| Charts | Highcharts |
| Infrastructure | Docker Compose (nginx + php-fpm + mysql) |

---

## Architecture

Custom MVC: **Controller → Action → Model → Template**

- PSR-4 autoloading under the `Iwea\` namespace (`src/`)
- File-based query cache with a 3-hour TTL
- PDO prepared statements for all database queries
- `public/` is the web root; everything else is outside the document root

---

## Quick Start (Docker)

```bash
cp .env.example .env
# edit .env — fill in API keys and DB credentials
docker compose up -d
# app available at http://localhost:8080
```

The `db` service auto-imports `docker/mysql/schema.sql` on first run.

---

## Quick Start (Manual)

```bash
composer install
cp .env.example .env
# edit .env — fill in DB credentials and API keys
# import docker/mysql/schema.sql into your MySQL instance
# configure nginx or Apache to serve public/ as the web root
```

---

## Environment Variables

| Variable | Description |
|---|---|
| `DB_HOST` | MySQL host (e.g. `localhost`) |
| `DB_NAME` | Database name (default: `iwea`) |
| `DB_USER` | Database user |
| `DB_PASS` | Database password |
| `OWM_API_KEY` | OpenWeatherMap API key |
| `APP_TIMEZONE` | PHP timezone (e.g. `Europe/Kyiv`) |
| `APP_DEFAULT_CITY` | DB id of the default city (`city` table) |
| `APP_DEFAULT_SITE` | DB id of the default weather source (`site` table) |
| `APP_DOMAIN` | Public URL, including scheme (e.g. `https://yourdomain.com`) |
| `APP_START_DATE` | Earliest date with stored weather data |

---

## Cron

```bash
php cron.php
```

Fetches fresh forecasts from all active sources and stores them in the database. Schedule to run daily:

```cron
0 6 * * * /usr/bin/php /var/www/html/cron.php >> /var/log/iwea-cron.log 2>&1
```

---

## Project History

Originally built as a university diploma project in 2016. Modernized in 2026: PSR-4 namespaces, PDO prepared statements, Guzzle HTTP client, Docker Compose, bcrypt passwords, PHP sessions.

---

## License

MIT
