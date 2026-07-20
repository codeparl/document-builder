<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

/**
 * Class MetadataStage
 *
 * Enriches the completed document result with execution telemetry.
 *
 * This stage runs after document generation and merging because only then
 * is a final DocumentResult available.
 *
 * Responsibilities:
 *
 * - Add completion timestamp.
 * - Record execution metrics.
 * - Attach runtime information.
 *
 * This stage does not:
 *
 * - Generate documents.
 * - Modify document content.
 * - Persist files.
 */
final class MetadataStage implements PipelineStage
{
    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        $result = $context->getResult();


        if ($result !== null) {

            $result->getMetadata()->merge([
                'completed_at' => now()->toDateTimeString(),
                'memory_peak'  => memory_get_peak_usage(true),
            ]);
        }


        return $next($context);
    }
}
