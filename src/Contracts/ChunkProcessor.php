<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ChunkResult;

interface ChunkProcessor
{
    public function process(
        PipelineContext $context,
        array $chunk,
        int $number
    ): ChunkResult;
}