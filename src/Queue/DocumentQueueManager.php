<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Queue;

use Illuminate\Support\Str;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentQueue;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Queue\Jobs\QueueDocumentExecutionJob;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlanSerializer;
use UnnovateBrains\DocumentBuilder\Support\QueuedDocumentResult;

/**
 * DocumentQueueManager
 *
 * Responsible only for creating asynchronous document executions.
 *
 * Responsibilities:
 *
 * - Create execution workspace
 * - Store immutable execution plan
 * - Create execution status
 * - Dispatch pipeline execution job
 *
 * It does NOT:
 *
 * - Split chunks
 * - Render documents
 * - Merge documents
 *
 * Those responsibilities belong to the pipeline.
 */
final class DocumentQueueManager implements DocumentQueue
{

    public function __construct(
        private readonly DocumentStorage $storage
    ) {}



    /**
     * Dispatch document generation asynchronously.
     *
     * Creates an execution workspace and starts
     * the pipeline inside a queue worker.
     */
    public function dispatch(
        ExecutionPlan $plan
    ): QueuedDocumentResult {

        $batchId = (string) Str::uuid();
        /*
    |--------------------------------------------------------------------------
    | Resolve workspace through storage context
    |--------------------------------------------------------------------------
    |
    | Storage decides:
    | - tenant
    | - school
    | - disk
    | - path isolation
    |
    */

        $workspace =
            $this->storage->batchWorkspace(
                $batchId
            );


        /*
    |--------------------------------------------------------------------------
    | Store execution blueprint
    |--------------------------------------------------------------------------
    */

        $workspace->putPlan(
            ExecutionPlanSerializer::serialize($plan)
        );


        /*
    |--------------------------------------------------------------------------
    | Store source snapshot
    |--------------------------------------------------------------------------
    */

        if ($plan->getSource()) {

            $workspace->putSource(
                $plan->getSource()->serialize()
            );
        }



        $workspace->putStatus([

            'status' => 'queued',

            'execution_id' => $batchId,

            'total_chunks' => 0,

            'processed_chunks' => 0,

            'created_at' => now()->toISOString(),

        ]);


        QueueDocumentExecutionJob::dispatch(
            $batchId
        );

        return new QueuedDocumentResult(
            $batchId
        );
    }




    public function dispatchPlan(
        ExecutionPlan $plan
    ): QueuedDocumentResult {

        return $this->dispatch($plan);
    }




    public function dispatchSync(
        ExecutionPlan $plan
    ): mixed {

        return dispatch_sync($plan);
    }




    public function dispatchAfterResponse(
        ExecutionPlan $plan
    ): mixed {

        return dispatch($plan)
            ->afterResponse();
    }
}
