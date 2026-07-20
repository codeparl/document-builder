<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

interface DocumentMerger
{
    public function supports(string $type): bool;


    /**
     * @param array<int,string> $chunks
     */
    public function merge(
        array $chunks,
        PipelineContext $context
    ): string;
}