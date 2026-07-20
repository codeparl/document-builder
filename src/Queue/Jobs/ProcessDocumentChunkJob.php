<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Queue\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use UnnovateBrains\DocumentBuilder\Contracts\ChunkProcessor;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Execution\BatchExecution;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\CompileDriverStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlanFactory;

final class ProcessDocumentChunkJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use Batchable;


    public function __construct(
        private readonly string $documentBatchId,
        private readonly int $chunk
    ) {}



    public function handle(
        DocumentStorage $storage,
        ChunkProcessor $processor
    ): void {

        if ($this->batch()?->cancelled()) {
            return;
        }



        /*
        |--------------------------------------------------------------------------
        | Resolve workspace
        |--------------------------------------------------------------------------
        */

        $workspace =
            $storage->batchWorkspace(
                $this->documentBatchId
            );



        /*
        |--------------------------------------------------------------------------
        | Restore immutable execution plan
        |--------------------------------------------------------------------------
        */

        $plan =
            ExecutionPlanFactory::fromArray(
                $workspace->plan()
            );



        $context =
            new DocumentPipelineContext(
                $plan
            );


        $context->setExecution(
            new BatchExecution(
                $this->documentBatchId,
                $this->chunk,
                $workspace
            )
        );



        /*
|--------------------------------------------------------------------------
| Restore runtime driver
|--------------------------------------------------------------------------
*/

        app(CompileDriverStage::class)
            ->handle(
                $context,
                fn($ctx) => $ctx
            );



        $chunkData =
            $workspace->chunk(
                $this->chunk
            );


        $processor->process(
            $context,
            $chunkData['records'],
            $this->chunk
        );
        /*
        |--------------------------------------------------------------------------
        | Save metadata
        |--------------------------------------------------------------------------
        |
        | DefaultChunkProcessor already writes:
        |
        | rendered/{chunk}.pdf
        | chunks/{chunk}.json
        |
        */
    }
}
