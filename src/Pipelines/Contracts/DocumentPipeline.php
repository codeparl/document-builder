<?php

namespace UnnovateBrains\DocumentBuilder\Pipelines\Contracts;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecutionResult;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;

/**
 * Interface DocumentPipeline
 *
 * Directs the sequential flow of execution plan stages. It pipes the context, 
 * renders views, coordinates with target drivers, and deposits assets into storage.
 *
 * @package UnnovateBrains\DocumentBuilder\Contracts
 */
interface DocumentPipeline
{
    /**
     * Coordinate the processing pipeline across all registered structural steps.
     *
     * @param ExecutionPlan $plan The immutable instruction definition.
     * @return DocumentExecutionResult The compiled outcome artifact.
     * @throws \UnnovateBrains\DocumentBuilder\Exceptions\PipelineException
     */
    public function execute(
        ExecutionPlan $plan
    ): DocumentExecutionResult;


    /**
     * Execute an already hydrated pipeline context.
     *
     * Used internally by queues, batch processing,
     * and advanced integrations.
     */
    public function executeContext(
        DocumentPipelineContext $context
    ): DocumentPipelineContext;

    public function resume(
        DocumentPipelineContext $context,
        string $stage
    ): DocumentPipelineContext;
}
