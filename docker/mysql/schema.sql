-- iWea weather aggregator — initial schema
-- ==========================================

CREATE DATABASE IF NOT EXISTS `iwea`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `iwea`;

SET NAMES utf8mb4;

-- --------------------------------------------------
-- city
-- Referenced columns: id, name, name_iso, lat, lon
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS `city` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(255)    NOT NULL,
  `name_iso`     VARCHAR(255)    NOT NULL DEFAULT '',
  `name_tr`      VARCHAR(255)    NOT NULL DEFAULT '',
  `name_sinoptik` VARCHAR(255)   NOT NULL DEFAULT '',
  `name_pl`      VARCHAR(255)    NOT NULL DEFAULT '',
  `cid_pl`       INT UNSIGNED    NOT NULL DEFAULT 0,
  `lat`          DECIMAL(9,6)    NOT NULL DEFAULT 0.000000,
  `lon`          DECIMAL(9,6)    NOT NULL DEFAULT 0.000000,
  PRIMARY KEY (`id`),
  KEY `idx_city_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------
-- site
-- Referenced columns: id, name, status, color, image_url
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS `site` (
  `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(255)    NOT NULL,
  `status`    TINYINT(1)      NOT NULL DEFAULT 1,
  `color`     VARCHAR(32)     NOT NULL DEFAULT '',
  `image_url` VARCHAR(512)    NOT NULL DEFAULT '',
  `url`       VARCHAR(512)    NOT NULL DEFAULT '',
  `country`   VARCHAR(100)    NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_site_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------
-- weather
-- Referenced columns: id, site_id, city_id, date,
--   min_temp, max_temp, date_write (= created_at)
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS `weather` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id`    INT UNSIGNED    NOT NULL,
  `city_id`    INT UNSIGNED    NOT NULL,
  `date`       DATE            NOT NULL,
  `min_temp`   DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
  `max_temp`   DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
  `date_write` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_weather_lookup` (`site_id`, `city_id`, `date`),
  KEY `idx_weather_date_write` (`date_write`),
  CONSTRAINT `fk_weather_site` FOREIGN KEY (`site_id`) REFERENCES `site` (`id`),
  CONSTRAINT `fk_weather_city` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------
-- user
-- Referenced columns: user_id, email, pass, name
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `user_id`    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(255)    NOT NULL,
  `pass`       VARCHAR(255)    NOT NULL,
  `name`       VARCHAR(255)    NOT NULL DEFAULT '',
  `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- Seed data
-- ==========================================

-- Cities (lat/lon accurate to 4 decimal places)
-- name_tr: Meteoprog slug | name_sinoptik: Sinoptik UA slug | name_pl/cid_pl: Interia PL
INSERT INTO `city` (`id`, `name`, `name_iso`, `name_tr`, `name_sinoptik`, `name_pl`, `cid_pl`, `lat`, `lon`) VALUES
  (1, 'Київ',   'Kyiv',    'Kyiv',    'київ',    'kijow',           60664, 50.4501, 30.5234),
  (2, 'Львів',  'Lviv',    'Lviv',    'львів',   'lwow',            60701, 49.8397, 24.0297),
  (3, 'Одеса',  'Odesa',   'Odessa',  'одеса',   'odessa',          63090, 46.4825, 30.7233),
  (4, 'Харків', 'Kharkiv', 'Kharkiv', 'харків',  'charkow',         55837, 49.9935, 36.2304),
  (5, 'Дніпро', 'Dnipro',  'Dnepropetrovsk', 'дніпро', 'dniepropietrowsk', 51882, 48.4647, 35.0462)
ON DUPLICATE KEY UPDATE
  `name_tr`      = VALUES(`name_tr`),
  `name_sinoptik`= VALUES(`name_sinoptik`),
  `name_pl`      = VALUES(`name_pl`),
  `cid_pl`       = VALUES(`cid_pl`);

-- Weather sources (7 sources used in the codebase)
INSERT INTO `site` (`id`, `name`, `status`, `color`, `image_url`, `url`, `country`) VALUES
  (1, 'OpenWeatherMap',    1, 'rgb(240, 87,  46)',  '', 'https://openweathermap.org/',        'США'),
  (2, 'AerisWeather',      1, 'rgb(46,  87,  240)', '', 'https://www.aerisweather.com/',      'США'),
  (3, 'WorldWeatherOnline',1, 'rgb(240, 200, 46)',  '', 'https://www.worldweatheronline.com/','Велика Британія'),
  (4, 'OpenMeteo',         1, 'rgb(46,  200, 100)', '', 'https://open-meteo.com/',            'Австрія'),
  (5, 'SinoptikUa',        1, 'rgb(200, 46,  200)', '', 'https://ua.sinoptik.ua/',            'Україна'),
  (6, 'Meteoprog',         1, 'rgb(46,  200, 200)', '', 'https://www.meteoprog.ua/',          'Україна'),
  (7, 'Interia',           1, 'rgb(200, 100, 46)',  '', 'https://pogoda.interia.pl/',         'Польща')
ON DUPLICATE KEY UPDATE
  `url`     = VALUES(`url`),
  `country` = VALUES(`country`);
