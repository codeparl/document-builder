<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Drivers\DriverManager;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;

/**
 * Class CompileDriverStage
 *
 * Resolves the appropriate format driver dynamically and compiles the template
 * or source into a finalized document result.
 */
final class CompileDriverStage implements PipelineStage
{
    public function __construct(
        private readonly DriverManager $driverManager,
    ) {}

    public function handle( 
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            $plan = $context->getPlan();

            $driver = $this->driverManager->driver(
                $plan->getType(),
                $plan->getEngine()
            );

            $context->setDriver($driver);
            $context->setEngine($driver->getEngineInstance());

        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to compile document driver',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'CompileDriverStage',
                    'document_type' => $context->getPlan()->getType(),
                    'engine' => $context->getPlan()->getEngine(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
