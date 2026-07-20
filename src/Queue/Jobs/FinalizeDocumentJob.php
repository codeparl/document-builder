<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Queue\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\DocumentPipeline;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlanFactory;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Execution\BatchExecution;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\CompileDriverStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\MergeStage;


/**
 * FinalizeDocumentJob
 *
 * Continues pipeline execution after all document chunks
 * have been generated.
 *
 * Pipeline continuation:
 *
 * MergeStage
 *     |
 * OutputStage
 *     |
 * LeaveContextStage
 */
final class FinalizeDocumentJob implements ShouldQueue
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


        /*
        |--------------------------------------------------------------------------
        | Restore workspace
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
        | Rebuild pipeline context
        |--------------------------------------------------------------------------
        */

        $context =
            new DocumentPipelineContext(
                $plan
            );



        /*
        |--------------------------------------------------------------------------
        | Restore execution workspace
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
| Restore runtime driver
|--------------------------------------------------------------------------
*/

        app(CompileDriverStage::class)
            ->handle(
                $context,
                fn($ctx) => $ctx
            );

        /*
        |--------------------------------------------------------------------------
        | Continue from MergeStage
        |--------------------------------------------------------------------------
        */

        $pipeline->resume(
            $context,
            MergeStage::class
        );
    }
}
