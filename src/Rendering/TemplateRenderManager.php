<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Rendering;

use InvalidArgumentException;
use UnnovateBrains\DocumentBuilder\Contracts\TemplateRenderer;
use UnnovateBrains\DocumentBuilder\Contracts\TemplateEngine;

final class TemplateRenderManager implements TemplateRenderer
{
    /** @var array<string, TemplateEngine> */
    private array $drivers = [];

    public function registerEngine(string $name, TemplateEngine $driver): void
    {
        $this->drivers[$name] = $driver;
    }

    public function render(string $engine, string $view, array $data = []): string
    {
        if (!isset($this->drivers[$engine])) {
            throw new InvalidArgumentException("Template engine [{$engine}] is not registered or supported.");
        }

        return $this->drivers[$engine]->render($view, $data);
    }
}