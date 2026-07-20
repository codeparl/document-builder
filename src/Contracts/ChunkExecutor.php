<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

use Closure;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

interface ChunkExecutor
{
    /**
     * Execute generated chunks.
     *
     * Implementations decide whether chunks are:
     *
     * - rendered immediately
     * - persisted and dispatched to workers
     * - streamed
     *
     * @return mixed
     */
 public function execute(
        PipelineContext $context,
        array $chunks,
        Closure $next
    ): mixed;
}