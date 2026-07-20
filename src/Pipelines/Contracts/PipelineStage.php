<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Contracts;

use Closure;

/**
 * Interface PipelineStage
 *
 * Defines a single, discrete action block in the document execution pipeline.
 */
interface PipelineStage
{
    /**
     * Process the current stage and pass the context along.
     */
    public function handle(PipelineContext $context, Closure $next): mixed;
}