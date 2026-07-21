<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Contracts\Renderer;

/**
 * Class RenderViewStage
 *
 * Passes the view templates and the source stream to the view engine to generate raw text layout syntax.
 */
final class RenderViewStage implements PipelineStage
{
    public function __construct(
        private readonly Renderer $renderer
    ) {}

    public function handle(PipelineContext $context, Closure $next): mixed
    {
        try {

            $plan = $context->getPlan();
            $view = $plan->getView();

            // 💡 Fixed: Early return must pass control to the NEXT closure block to keep the pipeline alive!
            if ($view === null) {
                return $next($context);
            }

            // 💡 Merges view variables while streaming resolved source data down into the layout engine
            $renderedOutput = $this->renderer->render(
                $view,
                array_merge($plan->getViewData(), [
                    'records' => $plan->getSource()?->resolve() ?? []
                ])
            );

            $context->setRenderedContent($renderedOutput);
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to render document view',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'RenderViewStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
