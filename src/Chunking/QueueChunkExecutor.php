<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Chunking;

use Closure;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use UnnovateBrains\DocumentBuilder\Contracts\ChunkExecutor;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\ProcessDocumentChunkJob;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\FinalizeDocumentJob;
use UnnovateBrains\DocumentBuilder\Support\QueuedDocumentResult;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class QueueChunkExecutor implements ChunkExecutor
{
    public function __construct(
        private readonly DocumentStorage $storage
    ) {}



    public function execute(
        PipelineContext $context,
        array $chunks,
        Closure $next
    ): mixed {


        /*
        |--------------------------------------------------------------------------
        | Use existing execution workspace
        |--------------------------------------------------------------------------
        |
        | This was created by DocumentQueueManager.
        |
        */
        $executionId =
            $context
            ->execution()
            ->batchId();



        $workspace =
            $context
            ->execution()
            ->workspace();

        /*
|--------------------------------------------------------------------------
| Persist runtime execution snapshot
|--------------------------------------------------------------------------
|
| Workers run in another process. They cannot access the current
| PipelineContext, compiled driver, or resolved runtime objects.
|
*/

        $workspace->putManifest([

            'execution_id' => $executionId,

            'type' =>
            $context->getPlan()->getType(),

            'engine' =>
            $context->getPlan()->getEngine(),

            'driver_config' =>
            $context->getPlan()->getDriverConfig(),

            'template_engine' =>
            $context->getPlan()->getTemplateEngine(),

            'view' =>
            $context->getPlan()->getView(),

            'created_at' =>
            now()->toISOString(),

        ]);


        $workspace->putStatus([

            'status' => 'queued',

            'execution_id' => $executionId,

            'total_chunks' => count($chunks),

            'created_at' => now()->toISOString(),

        ]);



        $jobs = [];



        foreach ($chunks as $index => $chunk) {


            $number = $index + 1;



            $workspace->putChunk(
                $number,
                [
                    'number' => $number,
                    'records' => $chunk,
                ]
            );



            $jobs[] =
                new ProcessDocumentChunkJob(
                    $executionId,
                    $number
                );
        }



        $batch =
            Bus::batch($jobs)
            ->then(function (Batch $batch) use ($executionId) {


                FinalizeDocumentJob::dispatch(
                    $executionId
                );
            })
            ->catch(function (
                Batch $batch,
                \Throwable $e
            ) use ($executionId) {


                $workspace =
                    app(DocumentStorage::class)
                    ->batchWorkspace(
                        $executionId
                    );


                $workspace->updateStatus([
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ]);
            })
            ->dispatch();



        $workspace->updateStatus([

            'status' => 'processing',

            'bus_batch_id' => $batch->id,

        ]);



        /*
        |--------------------------------------------------------------------------
        | Queue executor has completed its responsibility.
        |--------------------------------------------------------------------------
        |
        | Do not create another QueuedDocumentResult.
        | The execution already has one.
        |
        */

        return $context;
    }
}
