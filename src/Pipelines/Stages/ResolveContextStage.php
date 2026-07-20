<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;


final class ResolveContextStage implements PipelineStage
{
    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        $documentContext = $context
            ->getPlan()
            ->getContext();
        /*
        |--------------------------------------------------------------------------
        | Framework context
        |--------------------------------------------------------------------------
        */

        if (!empty($documentContext['locale'])) {

            app()->setLocale(
                $documentContext['locale']
            );
        }


        if (!empty($documentContext['timezone'])) {

            date_default_timezone_set(
                $documentContext['timezone']
            );
        }


        $context->setState(
            'document_context',
            $documentContext
        );


        return $next($context);
    }
}
