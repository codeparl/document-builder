<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Services;

use UnnovateBrains\DocumentBuilder\Contracts\TemplateRenderer;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class DocumentRenderer
{
    public function __construct(
        private readonly TemplateRenderer $renderer
    ) {}


    public function render(
        PipelineContext $context
    ): string {


        $plan = $context->getPlan();
        if ($plan->getView() === null) {
            return '';
        }

        $data = $plan->getViewData();


        $data['records'] = $context->getRecords();


    
        return $this->renderer->render(
            $plan->getTemplateEngine() ?? 'blade',
            $plan->getView(),
            $data
        );
    }



}
