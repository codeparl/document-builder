<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Chunking;

use Closure;
use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\ChunkExecutor;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class ChunkExecutorManager
{
    /**
     * @var array<string,ChunkExecutor>
     */
    private array $executors = [];


    public function register(
        string $name,
        ChunkExecutor $executor
    ): void {

        $this->executors[$name] = $executor;
    }


    public function execute(
        string $name,
        PipelineContext $context,
        array $chunks,
        Closure $next
    ): mixed {

        if (!isset($this->executors[$name])) {
            throw new RuntimeException(
                "Chunk executor [{$name}] is not registered."
            );
        }

        return $this->executors[$name]
            ->execute(
                $context,
                $chunks,
                $next
            );
    }
}
