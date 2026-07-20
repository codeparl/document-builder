<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

interface TemplateRenderer
{
    /**
     * Compile a layout string or file view with dynamic data payloads.
     */
    public function render(string $engine, string $template, array $data): string;
}