<?php

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;

/**
 * Interface DocumentDriver
 *
 * Defines the contract that all format and engine drivers must implement.
 * Drivers are responsible for configuring the low-level vendor engine libraries,
 * coordinating layout compilation, and wrapping the output binaries.
 *
 * @package UnnovateBrains\DocumentBuilder\Contracts
 */
interface DocumentDriver
{
    /**
     * Compile and generate the target document using the structured plan and pre-rendered string.
     *
     * @param ExecutionPlan $plan The immutable execution configuration.
     * @param string $content The pre-rendered layout template content (e.g., HTML, CSV syntax, raw text).
     * @return DocumentContent Standardized value object containing raw binaries, path details, and tracking metadata.
     * * @throws \UnnovateBrains\DocumentBuilder\Exceptions\PipelineException If vendor engine compilation fails.
     */
    public function handle(
        ExecutionPlan $plan,
        string $content
    ): DocumentContent;

    /**
     * Get the explicit document format type this driver manages (e.g., 'pdf', 'word', 'excel', 'csv').
     *
     * @return string
     */
    public function type(): string;

    /**
     * Get the developer-facing name of the internal processing engine (e.g., 'mpdf', 'chrome', 'phpword').
     *
     * @return string
     */
    public function engine(): string;

    /**
     * Verify whether this driver matches a given combination of format type and processing engine.
     *
     * Used by the driver registry during the pipeline resolution phase.
     *
     * @param string $type The format type (e.g., 'pdf').
     * @param string|null $engine The requested engine moniker (e.g., 'mpdf'). Null defaults to the driver's preferred choice.
     * @return bool
     */
    public function supports(
        string $type,
        ?string $engine = null
    ): bool;


    /**
     * Retrieve the low-level rendering engine instance encapsulated by this driver.
     *
     * @return \UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine
     */
    public function getEngineInstance(): DocumentEngine;
}
