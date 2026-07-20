<?php

namespace UnnovateBrains\DocumentBuilder\Contracts;

use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

/**
 * Interface DocumentQueue
 *
 * Defines the contract for background and asynchronous task execution within the framework.
 * This allows the execution pipeline to defer heavy document generation tasks (like chunking,
 * engine compilation, or merging) to a queue system without locking the main thread, while
 * keeping the package decoupled from Laravel's native Queue facade or external message brokers.
 *
 * @package UnnovateBrains\DocumentBuilder\Contracts
 */
interface DocumentQueue
{
    /**
     * Dispatch a document processing job to the standard asynchronous background queue.
     *
     * @param object $job The job instance managing the execution plan payload.
     * @return mixed Typically returns a job identifier, queue reference, or transaction receipt.
     */
    public function dispatch(
        ExecutionPlan  $job
    ): mixed;

    /**
     * Dispatch a document processing job to be executed immediately in the synchronous foreground.
     *
     * Useful for testing environments, small files, or immediate user-blocking downloads.
     *
     * @param object $job The job instance managing the execution plan payload.
     * @return mixed The direct execution result from the job.
     */
    public function dispatchSync(
        ExecutionPlan  $job
    ): mixed;

    /**
     * Dispatch a document processing job to run right after the HTTP response is sent to the client.
     *
     * Provides a fast, pseudo-asynchronous experience for single-tenant or web environments
     * without requiring a full dedicated background queue worker daemon running.
     *
     * @param object $job The job instance managing the execution plan payload.
     * @return mixed
     */
    public function dispatchAfterResponse(
        ExecutionPlan  $job
    ): mixed;


     /**
     * Dispatch a complete document execution plan.
     *
     * Queue drivers decide how the plan is persisted
     * and which jobs should execute.
     */
    public function dispatchPlan(
        ExecutionPlan $plan
    ): mixed;
}