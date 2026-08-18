# The 2016 original

Visual record of iWea as it existed in 2016, before the 2026 modernization. The application
ran at `iwea.ml` (domain long expired) and backed the diploma project and the ComInt 2017
conference paper cited in the root README.

Nothing here is reachable any more. These files are the only surviving evidence of the
original architecture and of the research results the paper reports.

## Architecture

| File | What it shows |
|---|---|
| `class-diagram.png` | PhpStorm-generated class diagram of the 2016 codebase |

Worth reading against today's `src/`. The 2016 design had a single static `Helper` grab-bag
(`check`, `convert`, `var_dump`, `strip_tags_content`, `cleanHtml`, `get_web_page`, `getDayUkr`,
`getMonthUkr`, `group_assoc`) that every source class inherited from, plus an `ISiteHelper`
interface implemented by eight source adapters: SinoptikUa, AerisWeather, OpenWeatherMap,
Interia, Meteoprog, TheDarkSkyCompany, WorldWeatherOnline. Persistence went through a
hand-rolled `MMySQLi` wrapper with `escape()` instead of prepared statements, and there was a
custom `AutoLoader` in place of Composer PSR-4.

The 2026 refactor dissolved `Helper` into `Locale` and `SyncState`, replaced `MMySQLi` with PDO
prepared statements, and dropped the sources that shut down (Dark Sky, Yahoo) or went paid-only.

## Live site, 3 July 2016

Full-page captures of `iwea.ml` on its last recorded working day.

| File | Page |
|---|---|
| `site-2016-07-03-home.png` | Home: city search, source picker, 7-day card, single-source chart |
| `site-2016-07-03-sources.png` | Information: the seven sources with URLs and countries |
| `site-2016-07-03-all-sources.png` | All sources: averaged card plus min/max charts with all seven series |
| `site-2016-07-03-analytics.png` | Analytics: distance matrix, clustering, per-source error, pairwise difference |

`home-2016-05-31.png` is an earlier capture of the home page (31 May 2016 data), included
because the interface differs slightly from the July one.

## Research output

| File | What it shows |
|---|---|
| `chart-all-sources-2016-06.png` | Min and max temperature for Lviv, 31.05-06.06.2016, all seven sources plus the mean |
| `mockup-analytics.png` | The error table and per-source error bars: SinoptikUa 1.02, Interia 1.09, WorldWeatherOnline 1.17, AerisWeather 1.31, Meteoprog 1.33, OpenWeatherMap 1.57, TheDarkSkyCompany 1.58 |
| `mockup-clustering.png` | The distance matrix between max-temperature curves and the resulting split into two groups |
| `mockup-home.png` | Presentation composite of the home page and charts |

The clustering shown in `mockup-clustering.png` and in the analytics capture is what the paper
title means by *classification*: sources are grouped by the distance between their forecast
curves, and the grouping is not stable between runs. In the mockup the split is
{Meteoprog, AerisWeather, TheDarkSkyCompany, Interia} against
{OpenWeatherMap, WorldWeatherOnline, SinoptikUa}; five weeks later, on the live site, it is
{Meteoprog, TheDarkSkyCompany, AerisWeather, WorldWeatherOnline} against
{Interia, OpenWeatherMap, SinoptikUa}.

## Interface details

Cropped figures, used as illustrations in the diploma text.

| File | What it shows |
|---|---|
| `ui-cities.png` | The city list: Boryslav, Drohobych, Lviv, Zhovkva, Mykolaiv, Sambir, Stryi, Turka, Chervonohrad, Kyiv |
| `ui-source-picker.png` | The source dropdown with vendor logos |
| `ui-forecast-card.png` | The 7-day forecast card |

## Provenance

These files lived in a personal freelance archive (`D:\work\archive\freelance-2015-2018\iwea`)
until 18 August 2026, when they were moved here and the archive folder was deleted. The
`screencapture-iwea-ml-<epoch-ms>.png` filenames of the four live captures decoded to
2016-07-03 14:58-14:59 UTC; `mockup-clustering.png` was named `Web-Page-PSD-Mockup.png`,
after the stock mockup template rather than its contents.
