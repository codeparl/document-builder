<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class ConfigureEngineStage implements PipelineStage
{
    public function handle(PipelineContext $context, Closure $next): mixed
    {
        try {

            $driver = $context->getDriver();
            if ($driver !== null) {
                // Safely extracts the contract-compliant DocumentEngine object
                $context->setEngine($driver->getEngineInstance());
            }
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to configure document engine',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'ConfigureEngineStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
