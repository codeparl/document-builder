<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

interface ImageEngine  extends DocumentEngine
{

    public function name(): string;


    public function type(): string;


    public function render(
        ExecutionPlan $plan,
        string $content,
        PipelineContext $context
    ): DocumentContent;
}
