<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
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

        try {

            $result = $context->getResult();


            if ($result !== null) {

                $result->getMetadata()->merge([
                    'completed_at' => now()->toDateTimeString(),
                    'memory_peak'  => memory_get_peak_usage(true),
                ]);
            }

        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to enrich document metadata',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'MetadataStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
