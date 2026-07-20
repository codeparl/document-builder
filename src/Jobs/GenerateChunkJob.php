<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Job;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Execution\BatchExecution;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\DocumentPipeline;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

final class GenerateChunkJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    /**
     * Create a new chunk generation job.
     *
     * The job only carries lightweight identifiers.
     * The actual chunk data and execution plan are restored
     * from the batch workspace.
     */
    public function __construct(
        public readonly string $batchUuid,
        public readonly int $chunkNumber
    ) {}



    /**
     * Execute one document chunk generation.
     *
     * Lifecycle:
     *
     * 1. Restore batch workspace.
     * 2. Restore immutable execution plan.
     * 3. Create pipeline context.
     * 4. Execute document pipeline.
     * 5. Persist generated chunk artifact.
     *
     * Batch progress is intentionally not handled here.
     * Laravel Bus Batch manages completion and failures.
     */
    public function handle(
        DocumentStorage $storage,
        DocumentPipeline $pipeline
    ): void {


        /*
        |--------------------------------------------------------------------------
        | Restore workspace
        |--------------------------------------------------------------------------
        */
        $workspace = $storage->batchWorkspace(
            $this->batchUuid
        );



        /*
        |--------------------------------------------------------------------------
        | Restore execution plan
        |--------------------------------------------------------------------------
        */
        $plan = ExecutionPlan::fromArray(
            $workspace->plan()
        );



        /*
        |--------------------------------------------------------------------------
        | Create execution context
        |--------------------------------------------------------------------------
        */
        $context = new DocumentPipelineContext(
            $plan
        );


        $context->setExecution(
            new BatchExecution(
                $this->batchUuid,
                $this->chunkNumber,
                $workspace
            )
        );



        /*
        |--------------------------------------------------------------------------
        | Execute document pipeline
        |--------------------------------------------------------------------------
        */
        $pipeline->executeContext(
            $context
        );



        /*
        |--------------------------------------------------------------------------
        | Persist generated chunk
        |--------------------------------------------------------------------------
        */
        $result = $context->getResult();


        $workspace->putRendered(
            $this->chunkNumber,
            $result,
            $context->getPlan()->getType()
        );
    }
}
