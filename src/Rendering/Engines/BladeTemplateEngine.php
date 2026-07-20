<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Rendering\Engines;

use Illuminate\Contracts\View\Factory as ViewFactory;
use UnnovateBrains\DocumentBuilder\Contracts\TemplateEngine;

final class BladeTemplateEngine implements TemplateEngine
{
    public function __construct(
        private readonly ViewFactory $viewFactory
    ) {}

    public function supports(string $engine): bool
    {
        return in_array(strtolower($engine), ['blade', 'view'], true);
    }

    public function render(string $template, array $data): string
    {
        if (! $this->viewFactory->exists($template)) {
            throw new \InvalidArgumentException("The requested Blade view target [{$template}] could not be found.");
        }

        return $this->viewFactory->make($template, $data)->render();
    }
}