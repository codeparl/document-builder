<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextHandlerRegistry;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;

final class LeaveContextStage implements PipelineStage
{
    public function __construct(
        private readonly DocumentContextHandlerRegistry $registry
    ) {}



    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            /*
            |--------------------------------------------------------------------------
            | Leave contexts in reverse order
            |--------------------------------------------------------------------------
            */

            foreach (
                array_reverse(
                    $this->registry->all()
                )
                as $resolver
            ) {

                $resolver->leave(
                    $context
                );
            }


            $context->setState(
                'contexts_entered',
                false
            );



            /*
            |--------------------------------------------------------------------------
            | Cleanup temporary workspace
            |--------------------------------------------------------------------------
            |
            | Cleanup only happens when:
            |
            | 1. Workspace exists
            | 2. Execution produced a final artifact
            | 3. Chunk artifacts are not intentionally preserved
            |
            |
            | Example:
            |
            | chunk(100)
            | merge()
            |
            | produces final.pdf
            | temporary chunks can be deleted.
            |
            |
            | Example:
            |
            | chunk(100)
            | merge(false)
            |
            | keeps:
            |
            | rendered/1.pdf
            | rendered/2.pdf
            | rendered/3.pdf
            |
            | for pagination / preview APIs.
            |
            */

            $execution =
                $context->execution();


            /*
    |--------------------------------------------------------------------------
    | Cleanup temporary workspace
    |--------------------------------------------------------------------------
    |
    | Only remove workspace when:
    |
    | 1. Document execution is complete
    | 2. No deferred chunk storage is required
    |
    */
            $execution = $context->execution();


            if (
                $execution !== null &&
                $execution->workspace() !== null
            ) {

                $plan = $context->getPlan();


                /*
        |--------------------------------------------------------------------------
        | Keep chunks for deferred merge
        |--------------------------------------------------------------------------
        */
                if (
                    $plan->isChunked() &&
                    !$plan->shouldMerge()
                ) {
                    return $context;
                }


                $execution
                    ->workspace()
                    ->cleanup();
            }
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to leave document context',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'LeaveContextStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        /*
        |--------------------------------------------------------------------------
        | Terminal stage
        |--------------------------------------------------------------------------
        */

        return $context;
    }
}
