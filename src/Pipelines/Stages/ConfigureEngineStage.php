<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class ConfigureEngineStage implements PipelineStage
{
    public function handle(PipelineContext $context, Closure $next): mixed
    {
        $driver = $context->getDriver();
        if ($driver !== null) {
            // Safely extracts the contract-compliant DocumentEngine object
            $context->setEngine($driver->getEngineInstance());
        }

        return $next($context);
    }
}