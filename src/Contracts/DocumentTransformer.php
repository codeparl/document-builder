<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

interface DocumentTransformer
{
    public function transform(
        mixed $data,
        PipelineContext $context
    ): mixed;
}