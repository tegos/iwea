<?php

namespace Iwea\Core;

class Action extends Helper
{
    private Model $model;
    private mixed $arg;

    public function __construct(Model $model, mixed $arg = [])
    {
        $this->model = $model;
        $this->arg   = $arg;
    }

    public function header(Template &$view): void
    {
        $weather  = $this->model->getWeather();
        $cityName = $weather['city_name'];
        $keywords = [
            'iwea', 'огляд погоди', 'порівняння погоди', 'погода', 'прогноз погоди',
            'аналіз погоди', 'погода з різних сайтів', 'погода на 10 днів',
            'графіки температур', 'OpenWeatherMap', 'SinoptikUa', 'Meteoprog',
            'AerisWeather', 'WorldWeatherOnline', 'Interia', 'OpenMeteo',
            $cityName,
        ];
        $view->keywords       = implode(',', array_map('mb_strtolower', $keywords));
        $view->description    = "iWea — порівнюй, аналізуй погоду і отримуй достовірний результат. Погода у {$cityName} на 7 днів.";
        $view->user = $this->isUser();
    }

    public function home(Template &$view): void
    {
        $weather      = $this->model->getWeather();
        $dateNow      = $this->getToday();
        $view->is_home     = true;
        $view->categories  = json_encode($weather['categories']);
        $view->series      = json_encode($weather['series']);
        $view->city_name   = $weather['city_name'];
        $view->title       = 'iWEA — Порівняння прогнозу погоди';
        $view->day_now     = $this->getDayUkr((int)$dateNow->format('w'));
        $view->forecasts   = $weather['forecasts'];
        $view->now_month   = $this->getMonthUkr($dateNow->format('M'));
        $view->now_month_d = $dateNow->format('d');
        $view->site_id     = $this->model->getCookieSiteId();
        $view->sites       = $this->model->getSites();
        $view->canonical   = Config::get('APP_DOMAIN') ?? '/';
        $view->chart       = $view->render('chart');
    }

    public function search_page(Template &$view): void
    {
        $search          = $_GET['search'] ?? '';
        $view->title     = 'iWEA — Пошук';
        $view->results   = $this->model->getCities($search);
        $view->canonical = Config::get('APP_DOMAIN') ?? '/';
    }

    public function not_found(Template &$view): void
    {
        header('HTTP/1.0 404 Not Found', true, 404);
        $view->title     = 'iWEA — 404';
        $view->canonical = (Config::get('APP_DOMAIN') ?? '') . '/not_found';
    }

    public function info(Template &$view): void
    {
        $view->title     = 'Список джерел';
        $view->sites     = $this->model->getAllSites();
        $view->canonical = (Config::get('APP_DOMAIN') ?? '') . '/sources';
    }

    public function sitemap(): void
    {
        $domain = Config::get('APP_DOMAIN') ?? '';
        $pages  = ['', 'sources', 'compare', 'analytics', 'accuracy', 'diff', 'search'];
        $start  = new \DateTime(Config::get('APP_START_DATE') ?? '2016-05-12');
        $end    = new \DateTime();
        $days   = $this->dateTimesToDays($start, $end);

        $output  = '<?xml version="1.0" encoding="UTF-8"?>';
        $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($pages as $page) {
            $output .= "<url><loc>{$domain}/{$page}</loc><changefreq>daily</changefreq><priority>0.8</priority></url>";
        }
        $dateStart = (new \DateTime())->modify("-{$days} days");
        for ($i = 0; $i < $days; $i++) {
            $param   = $this->base64_url_encode(http_build_query(['d' => $dateStart->format('d-m-Y')]));
            $output .= "<url><loc>{$domain}/compare/{$param}</loc><changefreq>daily</changefreq><priority>0.7</priority></url>";
            $dateStart->modify('+1 day');
        }
        $output .= '</urlset>';

        header('Content-Type: application/xml');
        echo $output;
        exit;
    }

    public function auth_reg(Template &$view): void
    {
        $view->title     = 'iWEA — Авторизація';
        $view->canonical = (Config::get('APP_DOMAIN') ?? '') . '/auth_reg';
        $view->result    = $this->model->getMessage('reg_success') ? 'Вітаємо! Ви успішно зареєструвались.' : false;
    }

    public function reg(Template &$view): void
    {
        $view->title     = 'iWEA — реєстрація';
        $view->canonical = (Config::get('APP_DOMAIN') ?? '') . '/reg';
        if ($this->model->getMessage('error')) {
            $view->error = unserialize($this->model->getMessage('error'));
            $this->model->unsetMessage('error');
        } else {
            $view->error = false;
        }
    }

    public function all(Template &$view): void
    {
        error_reporting(0);
        $startDate = new \DateTime(Config::get('APP_START_DATE') ?? '2016-05-12');
        $dateNow   = $this->getToday();
        $today     = true;
        $paramD    = '';

        if (!empty($this->arg)) {
            try {
                $paramD = '/' . $this->arg;
                $getStr = $this->base64_url_decode($this->arg);
                parse_str($getStr, $getArray);
                if (!empty($getArray['d'])) {
                    $dateNow = new \DateTime($getArray['d']);
                    $today   = false;
                }
            } catch (\Exception) {
            }
        }

        $dateFormat = $dateNow->format('Y-m-d');
        $weather    = $this->model->getWeatherAll($dateFormat);
        $canonical  = (Config::get('APP_DOMAIN') ?? '') . "/compare{$paramD}";

        $view->canonical   = $canonical;
        $view->categories  = json_encode($weather['categories']);
        $view->series      = json_encode($weather['series']);
        $view->series_max  = json_encode($weather['series_max']);
        $view->city_name   = $weather['city_name'];
        $view->title       = $today ? 'Погода сьогодні' : $this->getTitlePage($dateNow);
        $view->day_now     = $this->getDayUkr((int)$dateNow->format('w'));
        $view->forecasts   = $weather['forecasts'];
        $view->now_month   = $this->getMonthUkr($dateNow->format('M'));
        $view->now_month_d = $dateNow->format('d');
        $view->page_title  = $view->title;
        $view->is_today    = $today;
        $view->site_id     = $this->model->getCookieSiteId();

        $todayStr = $this->getToday()->format('Y-m-d');
        if ($dateFormat !== $todayStr) {
            $prev = (clone $dateNow)->modify('-1 day');
            $next = (clone $dateNow)->modify('+1 day');
            if ($startDate < $prev) {
                $view->url_prev   = '/compare/' . $this->base64_url_encode(http_build_query(['d' => $prev->format('d-m-Y')]));
                $view->title_prev = $this->getTitlePage($prev);
            }
            $view->url_next   = '/compare/' . $this->base64_url_encode(http_build_query(['d' => $next->format('d-m-Y')]));
            $view->title_next = $this->getTitlePage($next);
        } else {
            $prev = (clone $dateNow)->modify('-1 day');
            $view->url_prev   = '/compare/' . $this->base64_url_encode(http_build_query(['d' => $prev->format('d-m-Y')]));
            $view->title_prev = $this->getTitlePage($prev);
        }

        $view->chart = $view->render('chart-all');
    }

    public function analytics(Template &$view): void
    {
        $weather   = $this->model->getWeatherAll(0);
        $seriesMax = $weather['series_max'];
        array_pop($seriesMax);

        $view->categories = json_encode($weather['categories']);
        $view->series_max = json_encode($seriesMax);
        $view->city_name  = $weather['city_name'];
        $view->title      = 'Класифікація джерел';
        $view->canonical  = (Config::get('APP_DOMAIN') ?? '') . '/analytics';
    }

    public function accuracy(Template &$view): void
    {
        $view->title     = 'Точність прогнозу';
        $view->canonical = (Config::get('APP_DOMAIN') ?? '') . '/accuracy';
    }

    public function diff(Template &$view): void
    {
        $weather   = $this->model->getWeatherAll(0);
        $series    = $weather['series'];
        $seriesMax = $weather['series_max'];
        array_pop($series);
        array_pop($seriesMax);

        $view->categories = json_encode($weather['categories']);
        $view->series     = json_encode($series);
        $view->series_max = json_encode($seriesMax);
        $view->city_name  = $weather['city_name'];
        $view->title      = 'Різниця джерел';
        $view->canonical  = (Config::get('APP_DOMAIN') ?? '') . '/diff';
        $view->sites      = $this->model->getSites();
        $view->site_id    = $this->model->getCookieSiteId();
        $view->chart_diff = $view->render('chart-diff');
    }

    public function auth(Template &$view): void
    {
        $email = $_POST['email'] ?? '';
        $pass  = $_POST['pass'] ?? '';

        if (strlen($email) > 5 && strlen($pass) > 3) {
            if ($this->model->userExists($email)) {
                $user = $this->model->getUserByEmail($email);
                if (password_verify($pass, $user['pass'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    header('Location: /');
                    exit;
                }
                $this->model->setMessage('error', serialize(['status' => true, 'message' => 'Неправильний пароль.']));
            } else {
                $this->model->setMessage('error', serialize(['status' => true, 'message' => 'Нема такого користувача.']));
            }
        } else {
            $this->model->setMessage('error', serialize(['status' => true, 'message' => 'Перевірте введені дані.']));
        }

        header('Location: /login');
        exit;
    }

    public function registration(Template &$view): void
    {
        $email = $_POST['email'] ?? '';
        $pass  = $_POST['pass'] ?? '';
        $name  = $_POST['name'] ?? '';

        if (strlen($email) > 5 && strlen($pass) > 3 && strlen($name) > 2) {
            if (!$this->model->userExists($email)) {
                $this->model->addUser(['email' => $email, 'pass' => $pass, 'name' => $name]);
                $this->model->setMessage('reg_success', true);
                header('Location: /login');
                exit;
            }
            $this->model->setMessage('error', serialize(['status' => true, 'message' => 'Такий користувач вже існує.']));
        } else {
            $this->model->setMessage('error', serialize(['status' => true, 'message' => 'Перевірте введені дані.']));
        }

        header('Location: /register');
        exit;
    }

    public function isUser(): array|false
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return false;
        }
        return $this->model->getUserById((int)$userId) ?: false;
    }
}
