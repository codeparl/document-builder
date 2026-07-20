<?php

namespace UnnovateBrains\DocumentBuilder\Contracts;

use Closure;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

/**
 * Interface PipelineStep
 *
 * Defines the contract for an individual middleware unit within the execution pipeline.
 * Each step receives the execution context payload, executes its specialized operation, 
 * and hands off processing to the next sequential milestone handler.
 *
 * @package UnnovateBrains\DocumentBuilder\Contracts
 */
interface PipelineStep
{
    /**
     * Process the current execution task item or modify the operational state.
     *
     * @param array{plan: ExecutionPlan, content: string, result: ?object} $passable array container holding pipeline state.
     * @param Closure(array): array $next The call-forward reference pointer to the next processing step block.
     * @return array{plan: ExecutionPlan, content: string, result: ?object}
     */
    public function handle(
        array $passable,
        Closure $next
    ): array;
}