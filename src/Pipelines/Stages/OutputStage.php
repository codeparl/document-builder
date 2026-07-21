<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\FileContent;

final class OutputStage implements PipelineStage
{
    public function __construct(
        private readonly DocumentStorage $storage
    ) {}


    /**
     * Persist the completed document artifact.
     *
     * Final pipeline boundary responsible for:
     *
     * - storing generated document content
     * - normalizing stored paths
     * - updating final DocumentResult
     * - logging successful generation
     */
    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            $result = $context->getResult();


            /*
            |--------------------------------------------------------------------------
            | No generated artifact
            |--------------------------------------------------------------------------
            */
            if ($result === null) {
                return $next($context);
            }


            /*
            |--------------------------------------------------------------------------
            | Ignore incomplete executions
            |--------------------------------------------------------------------------
            */
            if (! $result->isComplete()) {
                return $next($context);
            }


            /*
            |--------------------------------------------------------------------------
            | Capture final metadata before replacing result
            |--------------------------------------------------------------------------
            */
            $filename = $result->getFilename();

            $type = $result->getType();

            $metadata = $result->getMetadata();



            /*
            |--------------------------------------------------------------------------
            | Persist final artifact
            |--------------------------------------------------------------------------
            */
            $storedPath = $this->storage->putContent(
                $result->getPath(),
                $result->getContent()
            );



            /*
            |--------------------------------------------------------------------------
            | Normalize final result
            |--------------------------------------------------------------------------
            |
            | Storage may resolve a different physical path.
            |
            */
            if ($storedPath !== $result->getPath()) {

                $content = new FileContent(
                    path: $this->storage->resolvePath(
                        $storedPath
                    ),
                    type: $type,
                    filename: $filename,
                    metadata: $metadata->toArray()
                );


                $result = new DocumentResult(
                    content: $content,
                    path: $storedPath,
                    filename: $filename,
                    type: $type,
                    metadata: $metadata
                );


                $context->setResult(
                    $result
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Log successful document generation
            |--------------------------------------------------------------------------
            */
            AppLogger::channel('document-builder')
                ->info(
                    'Document generated successfully',
                    new AppContext($context->getPlan()->getContext()),
                    [
                        'path' => $storedPath,
                        'filename' => $filename,
                        'type' => $type,
                    ]
                );
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to persist document output',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'OutputStage',
                    'document_type' => $context->getPlan()->getType(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
