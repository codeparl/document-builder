<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder;

use Illuminate\Support\Str;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentQueue;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Contracts\StorageWorkspaceInterface;
use UnnovateBrains\DocumentBuilder\Drivers\DriverManager;
use UnnovateBrains\DocumentBuilder\Execution\BatchExecution;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\DocumentPipeline;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\MergeStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Stages\OutputStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentPipelineContext;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlanSerializer;
use UnnovateBrains\DocumentBuilder\Support\QueuedDocumentResult;

final class DocumentManager
{
    public function __construct(
        protected DocumentPipeline $pipeline,
        protected DriverManager $driverManager,
        protected DocumentStorage $storage,
        protected DocumentQueue $queue
    ) {}

    /**
     * Centralized factory: orchestrates whether execution targets run synchronously 
     * through the pipeline or route down to the background queue driver.
     */
    public function generate(ExecutionPlan $plan): DocumentResult|QueuedDocumentResult|string
    {
        if ($this->shouldRouteToQueue($plan)) {
            return $this->queue->dispatchPlan($plan);
        }

        return $this->pipeline->execute($plan);
    }

    /**
     * Directly force-dispatches any execution plan straight to the queue.
     */
    public function dispatch(ExecutionPlan $plan): mixed
    {
        return $this->queue->dispatch($plan);
    }

    /**
     * Spawn an isolated, immutable DocumentBuilder instance injected with this manager.
     */
    public function builder(string $type): DocumentBuilder
    {
        return new DocumentBuilder($type, $this);
    }

    /**
     * Alias entrypoint mapping directly to the generate process workflow.
     */
    public function execute(ExecutionPlan $plan): DocumentResult|QueuedDocumentResult|string
    {
        return $this->generate($plan);
    }

    /**
     * Creates an isolated workspace specifically designed for asynchronous batch splitting.
     */
    public function createBatch(ExecutionPlan $plan): string
    {
        $batchId = (string) Str::uuid();

        $workspace = $this->storage
            ->forContext(
                $plan->getContext()['tenant_id'] ?? null,
                $plan->getContext()['school_id'] ?? null
            )
            ->batchWorkspace($batchId);

        $workspace->putPlan(
            ExecutionPlanSerializer::serialize($plan)
        );

        $workspace->putStatus([
            'status' => 'created',
            'batch_id' => $batchId,
            'created_at' => now()->toDateTimeString(),
        ]);

        $this->dispatch($plan);

        return $batchId;
    }
    /**
     * Resolve the storage workspace associated with a document batch.
     *
     * The workspace contains all temporary execution artifacts:
     *
     * - execution plan
     * - manifest
     * - status information
     * - chunk data
     * - rendered chunk files
     * - merged/final artifacts
     *
     * Queue workers, API controllers, and monitoring services can use this
     * method to access batch execution storage without knowing the underlying
     * filesystem implementation.
     *
     * @param string $batchId The batch execution identifier.
     *
     * @return \UnnovateBrains\DocumentBuilder\Contracts\StorageWorkspaceInterface
     */
    public function batchWorkspace(
        string $batchId
    ): StorageWorkspaceInterface {
        return $this->storage
            ->batchWorkspace($batchId);
    }



    /**
     * Retrieve the current execution status of a document batch.
     *
     * The status is maintained inside the batch workspace and represents
     * the current lifecycle state of the execution:
     *
     * Example:
     *
     * [
     *     'status' => 'processing',
     *     'total_chunks' => 10,
     *     'completed' => 5
     * ]
     *
     * This method is intended for:
     *
     * - API progress endpoints
     * - dashboards
     * - queue monitoring
     * - client polling
     *
     * @param string $batchId The batch execution identifier.
     *
     * @return array<string,mixed>
     */
    public function status(
        string $batchId
    ): array {

        return $this->batchWorkspace($batchId)
            ->status();
    }



    /**
     * Update execution status information for an active batch.
     *
     * Existing status values are preserved and merged with the supplied
     * attributes.
     *
     * @param string $batchId The batch execution identifier.
     * @param array<string,mixed> $status New status information.
     *
     * @return void
     */
    public function updateStatus(
        string $batchId,
        array $status
    ): void {

        $this->batchWorkspace($batchId)
            ->updateStatus($status);
    }



    /**
     * Determine whether a batch workspace exists.
     *
     * @param string $batchId The batch execution identifier.
     *
     * @return bool
     */
    public function exists(
        string $batchId
    ): bool {

        return $this->storage
            ->exists(
                "batches/{$batchId}"
            );
    }



    /**
     * Retrieve the final generated document artifact.
     *
     * This represents the final artifact produced after the pipeline
     * completes. For chunked documents this is the merged output.
     *
     * @param string $batchId The batch execution identifier.
     * @param string $type Document type extension.
     *
     * @return string Binary document content.
     */
    public function final(
        string $batchId,
        string $type = 'pdf'
    ): string {

        return $this->batchWorkspace($batchId)
            ->final($type);
    }



    /**
     * Retrieve the merged document artifact from a batch workspace.
     *
     * This is mainly used for chunked executions where multiple rendered
     * fragments are combined into one document.
     *
     * Example:
     *
     * rendered/1.pdf
     * rendered/2.pdf
     * rendered/3.pdf
     *
     * becomes:
     *
     * merged/final.pdf
     *
     * @param string $batchId The batch execution identifier.
     * @param string $type Document type extension.
     *
     * @return string Binary merged document content.
     */
    public function merged(
        string $batchId,
        string $type = 'pdf'
    ): string {

        return $this->batchWorkspace($batchId)
            ->final($type);
    }



    /**
     * Retrieve all rendered chunk artifact paths for a batch.
     *
     * This allows clients to inspect individual generated parts before
     * or after the merge operation.
     *
     * Example:
     *
     * [
     *     rendered/1.pdf,
     *     rendered/2.pdf,
     *     rendered/3.pdf
     * ]
     *
     * @param string $batchId The batch execution identifier.
     * @param string $type Document type extension.
     *
     * @return array<int,string>
     */
    public function chunks(
        string $batchId,
        string $type = 'pdf'
    ): array {

        return $this->batchWorkspace($batchId)
            ->renderedChunkPaths($type);
    }



    /**
     * Retrieve the immutable execution plan used for a batch.
     *
     * Queue workers rely on this information to reconstruct execution
     * without the original builder instance.
     *
     * @param string $batchId The batch execution identifier.
     *
     * @return array<string,mixed>
     */
    public function plan(
        string $batchId
    ): array {

        return $this->batchWorkspace($batchId)
            ->plan();
    }



    public function merge(
        string $batchId,
        string $type = 'pdf'
    ): DocumentResult {

        $plan = new ExecutionPlan(
            type: $type,
            engine: null,
            source: null,
            view: null,
            viewData: [],
            templateEngine: null,
            chunkSize: null,
            shouldMerge: true,
            outputFilename: "batch_{$batchId}",
            outputPath: null,
            disk: null,
            shouldQueue: false,
            metadata: null,
        );


        $context = new DocumentPipelineContext(
            $plan
        );


        $workspace =
            $this->storage
            ->batchWorkspace($batchId);



        $context->setExecution(
            new BatchExecution(
                $batchId,
                0,
                $workspace
            )
        );


        /*
    |--------------------------------------------------------------------------
    | Run merge only
    |--------------------------------------------------------------------------
    */

        app(MergeStage::class)
            ->handle(
                $context,
                function ($ctx) {
                    return $ctx;
                }
            );


        /*
    |--------------------------------------------------------------------------
    | Persist final artifact
    |--------------------------------------------------------------------------
    */

        app(OutputStage::class)
            ->handle(
                $context,
                function ($ctx) {
                    return $ctx;
                }
            );


        return $context->getResult();
    }



    /**
     * Remove temporary workspace data belonging to a completed batch.
     *
     * This deletes:
     *
     * - chunk data
     * - rendered fragments
     * - merged artifacts
     * - execution metadata
     *
     * This should normally be called after successful persistence of the
     * final document.
     *
     * @param string $batchId The batch execution identifier.
     *
     * @return void
     */
    public function cleanup(
        string $batchId
    ): void {

        $this->batchWorkspace($batchId)
            ->cleanup();
    }

    /**
     * Access the internal driver registry container.
     */
    public function drivers(): DriverManager
    {
        return $this->driverManager;
    }

    /**
     * Finalize and concatenate asynchronous data chunks for an active batch identifier.
     */
    public function finalizeBatch(string $batchId, string $type = 'pdf'): void
    {
        $plan = new ExecutionPlan(
            type: $type,
            engine: null,
            source: null,
            view: null,
            viewData: [],
            templateEngine: null,
            chunkSize: null,
            shouldMerge: true,
            outputFilename: "batch_{$batchId}",
            outputPath: null,
            disk: null,
            shouldQueue: false,
            metadata: null
        );

        $context = new DocumentPipelineContext($plan);
        $context->setState('batch_id', $batchId);
        $context->setState('is_chunked', true);

        /** @var MergeStage $mergeStage */
        $mergeStage = app(MergeStage::class);

        $context = $mergeStage->handle($context, function ($ctx) {
            return $ctx;
        });

        $this->cleanupBatch($batchId);
    }

    /**
     * Remove temporary chunk storage files after a successful merge operation.
     */
    protected function cleanupBatch(string $batchId): void
    {
        $targetDirectory = "batches/{$batchId}";

        if ($this->storage->exists($targetDirectory)) {
            $this->storage->deleteDirectory($targetDirectory);
        }
    }

    /**
     * Evaluates execution requirements defensively across structural getters.
     */
    protected function shouldRouteToQueue(ExecutionPlan $plan): bool
    {
        if (method_exists($plan, 'shouldQueue')) {
            return $plan->shouldQueue();
        }

        if (method_exists($plan, 'getShouldQueue')) {
            return $plan->getShouldQueue();
        }

        return false;
    }
}
