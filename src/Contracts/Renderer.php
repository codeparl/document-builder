<?php

namespace UnnovateBrains\DocumentBuilder\Contracts;

/**
 * Interface Renderer
 *
 * Defines the contract for turning structured layouts and view designs (e.g., Blade templates, 
 * raw HTML, Markdown, or custom templates) into raw template string components. 
 * This isolates the presentation parsing stage from the underlying document compiling engine.
 *
 * @package UnnovateBrains\DocumentBuilder\Contracts
 */
interface Renderer
{
    /**
     * Render a structural view or layout path with contextual template parameters.
     *
     * @param string $view The view path identifier (e.g., 'reports.students' or 'invoice').
     * @param array<string, mixed> $data The data payload injected directly into the layout template views.
     * @return string The rendered template output content string (typically valid stringified HTML/XML markup).
     */
    public function render(
        string $view,
        array $data = []
    ): string;

    /**
     * Verify whether this rendering implementation supports a given view type or extension format.
     *
     * Allows the system to seamlessly interchange standard HTML/Blade engines with specialized 
     * template syntax engines depending on configuration needs.
     *
     * @param string $type The rendering layout format type (e.g., 'blade', 'twig', 'html').
     * @return bool
     */
    public function supports(
        string $type
    ): bool;
}