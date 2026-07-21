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

final class EnterContextStage implements PipelineStage
{
    public function __construct(
        private readonly DocumentContextHandlerRegistry $registry
    ) {}


    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            foreach ($this->registry->all() as $resolver) {

                $resolver->enter(
                    $context
                );
            }

            $context->setState(
                'contexts_entered',
                true
            );
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to enter document context',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'EnterContextStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
