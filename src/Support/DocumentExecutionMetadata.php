<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;

final class DocumentExecutionMetadata
{
    public static function append(
        DocumentResult $result,
        ExecutionPlan $plan,
        PipelineContext $context,
        string $filename
    ): void {

        $result->getMetadata()->merge([
            'generation' => [

                'completed_at' => now()->toDateTimeString(),

                'memory_peak' => memory_get_peak_usage(true),

                'driver' => $plan->getEngine(),

                'type' => $plan->getType(),

                'filename' => $filename,

                'generated_by' => 'document-builder',

                'php_version' => PHP_VERSION,

                'environment' => app()->environment(),

                'execution_mode' => $context->isBatchExecution()
                    ? 'batch'
                    : 'sync',
            ],
        ]);
    }
}
