<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentMerger;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class MockDocumentMerger implements DocumentMerger
{
    public array $mergedChunks = [];

    public function supports(string $type): bool
    {
        return $type === 'pdf';
    }


    public function merge(
        array $chunks,
        PipelineContext $context
    ): string {

        $this->mergedChunks = $chunks;

        return implode(
            "\n",
            array_map(
                fn ($chunk) => "MERGED:{$chunk}",
                $chunks
            )
        );
    }
}