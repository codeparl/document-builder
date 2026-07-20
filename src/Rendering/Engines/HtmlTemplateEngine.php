<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Rendering\Engines;

use UnnovateBrains\DocumentBuilder\Contracts\TemplateEngine;

final class HtmlTemplateEngine implements TemplateEngine
{
    public function supports(string $engine): bool
    {
        return in_array(strtolower($engine), ['html', 'raw', 'string'], true);
    }

    public function render(string $template, array $data): string
    {
        // Simple short-code interpolation fallback: replaces {{ key }} with data values
        $compiled = $template;
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $compiled = str_replace('{{ ' . $key . ' }}', (string) $value, $compiled);
                $compiled = str_replace('{{' . $key . '}}', (string) $value, $compiled);
            }
        }

        return $compiled;
    }
}