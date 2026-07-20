<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

use UnnovateBrains\DocumentBuilder\Contracts\ChunkExecutor;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\DocumentMetadata;

final class MockChunkExecutor implements ChunkExecutor
{
    public array $chunks = [];


    public function execute(
        PipelineContext $context,
        array $chunks
    ): mixed {

        $this->chunks = $chunks;


        /**
         * Simulate chunk processing.
         *
         * Real implementation can:
         * - queue jobs
         * - process immediately
         * - use workers
         */


        $content = json_encode([
            'chunks' => count($chunks),
            'records' => array_merge(...$chunks),
        ]);


        $context->setResult(
            new DocumentResult(
                content: $content,
                path: 'documents/chunked.pdf',
                filename: 'chunked.pdf',
                type: 'pdf',
                metadata: new DocumentMetadata([
                    'chunks' => count($chunks)
                ])
            )
        );


        return $context;
    }
}