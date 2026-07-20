<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

/**
 * Class ResolveSourceStage
 *
 * Extracts the raw collection/dataset from the configured execution source 
 * strategy and pushes it safely into the context's records buffer slot.
 */
final class ResolveSourceStage implements PipelineStage
{
    public function handle(PipelineContext $context, Closure $next): mixed
    {
        $plan = $context->getPlan();
        $source = $plan->getSource();
        if ($source !== null) {
            // Now we have confidence in count() and isEmpty()
            if ($source->isEmpty()) {
                // Log a warning or handle empty data gracefully
            }

            $context->setRecords($source->resolve());
            $context->setTotalRecords($source->count());
        }

        return $next($context);
    }
}
