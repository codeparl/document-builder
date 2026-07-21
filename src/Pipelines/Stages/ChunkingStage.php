<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Chunking\ChunkExecutorManager;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class ChunkingStage implements PipelineStage
{
    public function __construct(
        private readonly ChunkExecutorManager $executorManager
    ) {}


    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            $plan = $context->getPlan();


            /*
            |--------------------------------------------------------------------------
            | No chunking requested
            |--------------------------------------------------------------------------
            */
            if (!$plan->getChunkSize()) {
                return $next($context);
            }

            /*
            |--------------------------------------------------------------------------
            | Split records into chunks
            |--------------------------------------------------------------------------
            */

            $chunks = $this->chunk(
                $context->getRecords(),
                $plan->getChunkSize()
            );


            /*
            |--------------------------------------------------------------------------
            | Resolve execution strategy
            |--------------------------------------------------------------------------
            |
            | Examples:
            | sync  -> process immediately
            | queue -> dispatch workers
            |
            */

            return $this->executorManager->execute(
                $plan->getChunkExecutor(),
                $context,
                $chunks,
                $next
            );
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to execute chunking stage',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'ChunkingStage',
                    'document_type' => $context->getPlan()->getType(),
                    'chunk_size' => $context->getPlan()->getChunkSize(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }
    }


    /**
     * Split iterable dataset.
     *
     * @return array<int,array<int,mixed>>
     */
    private function chunk(
        iterable $dataset,
        int $size
    ): array {

        $chunks = [];

        $buffer = [];


        foreach ($dataset as $item) {

            $buffer[] = $item;


            if (count($buffer) >= $size) {

                $chunks[] = $buffer;

                $buffer = [];
            }
        }


        if (!empty($buffer)) {

            $chunks[] = $buffer;
        }


        return $chunks;
    }
}
