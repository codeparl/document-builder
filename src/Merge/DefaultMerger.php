<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Merge;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentMerger;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class DefaultMerger implements DocumentMerger
{

    public function supports(
        string $type
    ): bool {
        return true;
    }



    /**
     * Default merger behavior.
     *
     * Non mergeable document types (Excel, Word, CSV)
     * can still pass through the merge pipeline when
     * chunking is enabled.
     *
     * Expected input:
     *
     * [
     *     "/workspace/rendered/1.xlsx"
     * ]
     *
     * The single artifact becomes the final artifact.
     */
    public function merge(
        array $chunks,
        PipelineContext $context
    ): string {


        if (count($chunks) !== 1) {

            throw new RuntimeException(
                'Document type does not support merging and multiple chunks were generated.'
            );
        }


        $file = $chunks[0];


        if (!file_exists($file)) {

            throw new RuntimeException(
                "Chunk does not exist: {$file}"
            );
        }


        return file_get_contents($file);
    }
}
