<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

interface TemplateEngine
{
    /**
     * Check if this engine supports the given type or configuration payload.
     */
    public function supports(string $engine): bool;

    /**
     * Render a template into a string.
     *
     * The template may be a view name, file path, or raw template,
     * depending on the engine implementation.
     *
     * @param array<string,mixed> $data
     */
   /**
     * Compile a template string or view file path with array data into raw HTML string data.
     *
     * @param string $template A view name (e.g., 'transcripts.student') or raw HTML content.
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data): string;
}