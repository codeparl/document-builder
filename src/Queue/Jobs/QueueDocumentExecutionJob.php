<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Queue\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\DocumentPipeline;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlanFactory;
use UnnovateBrains\DocumentBuilder\Execution\BatchExecution;

final class QueueDocumentExecutionJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use Batchable;


    public function __construct(
        private readonly string $documentBatchId
    ) {}



    public function handle(
        DocumentStorage $storage,
        DocumentPipeline $pipeline
    ): void {


        dump('doc queue job');
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
        | Attach workspace execution
        |--------------------------------------------------------------------------
        */

        $context->setExecution(
            new BatchExecution(
                $this->documentBatchId,
                0,
                $workspace
            )
        );



        /*
        |--------------------------------------------------------------------------
        | Resume pipeline normally
        |--------------------------------------------------------------------------
        */

        $pipeline->executeContext(
            $context
        );
    }
}
