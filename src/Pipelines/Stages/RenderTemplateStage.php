<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Services\DocumentRenderer;

final class RenderTemplateStage implements PipelineStage
{
    public function __construct(
        private readonly DocumentRenderer $renderer
    ) {}


    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            if ($context->isBatchExecution()) {
                return $next($context);
            }

            if ($context->getPlan()->getView() === null) {
                return $next($context);
            }


            $content = $this->renderer->render(
                $context
            );


            $context->setRenderedContent(
                $content
            );
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to render document template',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'RenderTemplateStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
