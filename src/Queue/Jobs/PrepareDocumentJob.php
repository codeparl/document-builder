<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Queue\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\ResolveContextStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\ResolveSourceStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlanFactory;

final class PrepareDocumentJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use Batchable;



    public function __construct(
        private readonly string $documentBatchId
    ) {}



    public function handle(
        DocumentStorage $storage,
        Dispatcher $dispatcher
    ): void {


        /*
        |--------------------------------------------------------------------------
        | Resolve batch workspace
        |--------------------------------------------------------------------------
        */

        $workspace =
            $storage->batchWorkspace(
                $this->documentBatchId
            );



        /*
        |--------------------------------------------------------------------------
        | Restore execution plan
        |--------------------------------------------------------------------------
        */

        $plan =
            ExecutionPlanFactory::fromArray(
                $workspace->plan()
            );



        /*
        |--------------------------------------------------------------------------
        | Create pipeline context
        |--------------------------------------------------------------------------
        */

        $context =
            new DocumentPipelineContext(
                $plan
            );



        /*
        |--------------------------------------------------------------------------
        | Resolve context
        |--------------------------------------------------------------------------
        */

        app(ResolveContextStage::class)
            ->handle(
                $context,
                fn($ctx) => $ctx
            );



        /*
        |--------------------------------------------------------------------------
        | Resolve source
        |--------------------------------------------------------------------------
        */

        app(ResolveSourceStage::class)
            ->handle(
                $context,
                fn($ctx) => $ctx
            );



        $records =
            $context->getRecords();



        /*
        |--------------------------------------------------------------------------
        | Split records
        |--------------------------------------------------------------------------
        */

        $chunkSize =
            $plan->getChunkSize()
            ?? count($records);



        $chunks =
            array_chunk(
                $records,
                $chunkSize
            );



        /*
        |--------------------------------------------------------------------------
        | Store chunks
        |--------------------------------------------------------------------------
        */

        foreach ($chunks as $index => $chunk) {


            $chunkNumber =
                $index + 1;



            $workspace->putChunk(
                $chunkNumber,
                $chunk
            );



            $dispatcher->dispatch(
                new ProcessDocumentChunkJob(
                    $this->documentBatchId,
                    $chunkNumber
                )
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Update workspace status
        |--------------------------------------------------------------------------
        */

        $workspace->updateStatus([
            'status' => 'processing',
            'total_chunks' => count($chunks),
            'processed_chunks' => 0,
            'started_at' => now()->toISOString(),
        ]);
    }
}
