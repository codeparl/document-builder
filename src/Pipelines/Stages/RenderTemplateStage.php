<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Services\DocumentRenderer;

final class RenderTemplateStage implements PipelineStage
{
    public function __construct(
        private readonly DocumentRenderer $renderer
    ) {
    }


    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {


         if ($context->isBatchExecution()) 
        return $next($context);
    

        if ($context->getPlan()->getView() === null) {
            return $next($context);
        }


        $content = $this->renderer->render(
            $context
        );


        $context->setRenderedContent(
            $content
        );


        return $next($context);
    }
}