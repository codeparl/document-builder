<?php

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

/**
 * Interface DocumentEngine
 *
 * Defines the contract for compile-time rendering engines (e.g., mPDF, PhpSpreadsheet, PHPWord).
 * This acts as the bridge layer converting structural content (HTML strings, datasets) into
 * a binary file result wrapped within a standardized DocumentResult value object.
 *
 * @package UnnovateBrains\DocumentBuilder\Contracts
 */
interface DocumentEngine
{
    /**
     * Get the unique developer-facing name of the engine driver (e.g., 'mpdf', 'chrome', 'phpspreadsheet').
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the explicit document type format this engine generates (e.g., 'pdf', 'word', 'excel', 'csv').
     *
     * @return string
     */
    public function type(): string;

    /**
     * Compile and generate the target document using the raw text content or dataset layout.
     *
     * @param ExecutionPlan $plan The immutable execution instructions configuring this current pass.
     * @param string $content The pre-rendered layout content (e.g., HTML, XML, or raw data markers).
     * @return DocumentContent Standardized value object containing raw binaries, paths, and metadata.
     */
    public function render(
        ExecutionPlan $plan,
        string $content,
        PipelineContext $context
    ): DocumentContent;

    /**
     * Check if this exact implementation handles or matches a specifically requested engine moniker.
     *
     * @param string $engine The name of the engine requested by the developer (e.g., 'mpdf').
     * @return bool
     */
    public function supports(
        string $engine
    ): bool;
}
