<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

interface ImageDriver extends DocumentDriver
{

    public function handle(
        ExecutionPlan $plan,
        string $content,
        PipelineContext $context
    ): DocumentContent;


    public function supportsFormat(
        string $format
    ): bool;
}
