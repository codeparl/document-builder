<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
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


        foreach ($this->registry->all() as $resolver) {

            $resolver->enter(
                $context
            );
        }


        $context->setState(
            'contexts_entered',
            true
        );


        return $next($context);
    }
}
