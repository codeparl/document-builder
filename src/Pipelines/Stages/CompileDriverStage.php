<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
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

        $plan = $context->getPlan();

        $driver = $this->driverManager->driver(
            $plan->getType(),
            $plan->getEngine()
        );

        $context->setDriver($driver);
        $context->setEngine($driver->getEngineInstance());

        return $next($context);
    }
}
