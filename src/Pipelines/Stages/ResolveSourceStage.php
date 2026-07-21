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
 * Class ResolveSourceStage
 *
 * Extracts the raw collection/dataset from the configured execution source 
 * strategy and pushes it safely into the context's records buffer slot.
 */
final class ResolveSourceStage implements PipelineStage
{
    public function handle(PipelineContext $context, Closure $next): mixed
    {
        try {

            $plan = $context->getPlan();
            $source = $plan->getSource();

            if ($source !== null) {

                if ($source->isEmpty()) {

                    AppLogger::channel('document-builder')->warning(
                        'Source data is empty',
                        new AppContext($plan->getContext()),
                        [
                            'stage' => 'ResolveSourceStage',
                            'document_type' => $plan->getType(),
                        ]
                    );
                }

                $context->setRecords($source->resolve());
                $context->setTotalRecords($source->count());
            }

        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to resolve source data',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'ResolveSourceStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
