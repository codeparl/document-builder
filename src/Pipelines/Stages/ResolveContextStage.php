<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;


final class ResolveContextStage implements PipelineStage
{
    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

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

            AppLogger::channel('document-builder')->info(
                'Document generation started',
                new AppContext($context->getPlan()->getContext()),
                [
                    'document_type' => $context->getPlan()->getType(),
                ]
            );
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to resolve document context',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'ResolveContextStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
