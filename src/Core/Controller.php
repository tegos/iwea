<?php

namespace Iwea\Core;

class Controller extends Helper
{
    private string $method;
    private mixed $arg;
    private Model $model;

    public function __construct(string $path = '', mixed $arg = [], bool $return = false)
    {
        $this->method = $path;
        $this->arg    = count((array)$arg) < 1 ? ($_GET['arg'] ?? []) : $arg;
        $this->model  = new Model();

        if (strlen($path) > 0) {
            $this->route();
            return;
        }

        $route     = $this->parseRoute();
        $this->arg = $route['arg'] ?? $this->arg;
        $this->routePage($route['action']);
    }

    private function parseRoute(): array
    {
        $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $uri = trim($uri, '/');

        if ($uri === '' || $uri === 'index.php') {
            return ['action' => 'home', 'arg' => null];
        }

        if ($uri === 'sitemap.xml') {
            return ['action' => 'sitemap', 'arg' => null];
        }

        if (str_starts_with($uri, 'all/')) {
            return ['action' => 'all', 'arg' => substr($uri, 4)];
        }

        if ($uri === 'login') {
            return [
                'action' => $_SERVER['REQUEST_METHOD'] === 'POST' ? 'auth' : 'auth_reg',
                'arg'    => null,
            ];
        }

        if ($uri === 'register') {
            return [
                'action' => $_SERVER['REQUEST_METHOD'] === 'POST' ? 'registration' : 'reg',
                'arg'    => null,
            ];
        }

        $slugMap = [
            'set-city' => 'set_city_id',
            'set-site' => 'set_site_id',
        ];

        return ['action' => $slugMap[$uri] ?? $uri, 'arg' => null];
    }

    private function route(): void
    {
        $rez = '';
        if (method_exists($this->model, $this->method)) {
            $method = $this->method;
            $rez    = json_encode($this->model->$method());
        }
        echo $rez;
    }

    private function routePage(string $action): void
    {
        $action = strtolower($action);
        session_start();

        $view = new Template();
        $act  = new Action($this->model, $this->arg);
        $act->header($view);

        $renderPage = null;

        switch ($action) {
            case 'home':
                $renderPage = $action;
                $act->home($view);
                break;
            case 'search':
                $renderPage = $action;
                $act->search_page($view);
                break;
            case 'info':
            case 'all':
            case 'analytics':
            case 'auth_reg':
            case 'reg':
            case 'registration':
            case 'auth':
            case 'account':
                $renderPage = $action;
                $act->$action($view);
                break;
            case 'sitemap':
                $act->sitemap();
                return;
            case 'set_city_id':
                setcookie('city_id', (int)($_GET['city_id'] ?? 0));
                header('Location: /');
                exit;
            case 'set_site_id':
                setcookie('site_id', (int)($_GET['site_id'] ?? 0));
                header('Location: /');
                exit;
            default:
                $renderPage = 'not_found';
                $act->not_found($view);
                break;
        }

        $view->header = $view->render('header');
        $view->footer = $view->render('footer');

        if ($renderPage !== null) {
            echo $view->render($renderPage);
        }
    }
}
