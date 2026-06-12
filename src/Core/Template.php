<?php

namespace Iwea\Core;

class Template
{
    private array $vars = [];

    public function __get(string $name): mixed
    {
        return $this->vars[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        if ($name === 'view_template_file') {
            throw new \Exception("Cannot bind variable named 'view_template_file'");
        }
        $this->vars[$name] = $value;
    }

    public function render(string $view_template_file): string
    {
        if (array_key_exists('view_template_file', $this->vars)) {
            throw new \Exception("Cannot bind variable called 'view_template_file'");
        }
        extract($this->vars);
        ob_start();
        include dirname(__DIR__, 2) . '/template/' . $view_template_file . '.tpl';
        return ob_get_clean() ?: '';
    }
}
