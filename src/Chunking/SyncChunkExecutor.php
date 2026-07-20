<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Chunking;

use Closure;
use Illuminate\Support\Str;
use UnnovateBrains\DocumentBuilder\Contracts\ChunkExecutor;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\ChunkProcessor;
use UnnovateBrains\DocumentBuilder\Execution\BatchExecution;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class SyncChunkExecutor implements ChunkExecutor
{
    public function __construct(
        private readonly ChunkProcessor $processor,
        private readonly DocumentStorage $storage
    ) {}



    public function execute(
        PipelineContext $context,
        array $chunks,
        Closure $next
    ): mixed {


        $batchUuid =
            Str::uuid()->toString();



        /*
        |--------------------------------------------------------------------------
        | Create execution workspace
        |--------------------------------------------------------------------------
        |
        | Sync execution follows exactly the same lifecycle as queued
        | execution. This makes testing and production behaviour identical.
        |
        */
        $workspace =
            $this->storage->batchWorkspace(
                $batchUuid
            );



        /*
        |--------------------------------------------------------------------------
        | Store batch metadata
        |--------------------------------------------------------------------------
        */
        $workspace->putManifest([
            'batch_id' => $batchUuid,
            'type' => $context->getPlan()->getType(),
            'created_at' => now()->toIso8601String(),
        ]);



        /*
        |--------------------------------------------------------------------------
        | Store immutable execution plan
        |--------------------------------------------------------------------------
        */
        $workspace->putPlan(
            $context
                ->getPlan()
                ->toArray()
        );



        /*
        |--------------------------------------------------------------------------
        | Initialize processing status
        |--------------------------------------------------------------------------
        */
        $workspace->putStatus([
            'status' => 'processing',
            'total_chunks' => count($chunks),
            'completed' => 0,
            'failed' => 0,
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
        ]);



        /*
        |--------------------------------------------------------------------------
        | Attach execution workspace
        |--------------------------------------------------------------------------
        */
        $context->setExecution(
            new BatchExecution(
                $batchUuid,
                0,
                $workspace
            )
        );



        /*
        |--------------------------------------------------------------------------
        | Process chunks
        |--------------------------------------------------------------------------
        */
        foreach ($chunks as $index => $chunk) {

            $chunkNumber =
                $index + 1;



            /*
            | Persist original chunk data
            */
            $workspace->putChunk(
                $chunkNumber,
                $chunk
            );



            $this->processor->process(
                $context,
                $chunk,
                $chunkNumber
            );



            $workspace->updateStatus([
                'completed' =>
                $index + 1,
            ]);
        }



        return $next(
            $context
        );
    }
}
