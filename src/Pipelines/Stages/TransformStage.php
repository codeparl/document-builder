<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Services\DocumentTransformer;

/**
 * Class TransformStage
 *
 * Converts the current execution dataset into document-friendly arrays.
 *
 * The dataset may represent:
 *
 * - the complete source (normal execution)
 * - a single chunk (chunked execution)
 *
 * This stage is intentionally unaware of how the dataset was obtained.
 */
final class TransformStage implements PipelineStage
{
    public function __construct(
        private readonly DocumentTransformer $transformer
    ) {}



    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

       if ($context->isBatchExecution()) {
        return $next($context);
    }
        $records = $context->getRecords();

        if (!is_iterable($records)) {
            return $next($context);
        }


        $transformed =
            $this->transformer->apply(
                $records,
                $context->getPlan()->getTransformers(),
                $context
            );


        $context->setRecords(
            $transformed
        );

        return $next($context);
    }
}