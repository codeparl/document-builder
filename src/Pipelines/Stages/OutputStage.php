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
     * Persist completed document artifact.
     *
     * Responsibilities:
     *
     * - take DocumentContent from previous stage
     * - store artifact permanently
     * - replace result with storage-backed content
     */
    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            $result = $context->getResult();


            if ($result === null) {
                return $next($context);
            }


            if (! $result->isComplete()) {
                return $next($context);
            }



            $plan = $context->getPlan();


            $filename =
                $result->getFilename();


            $type =
                $result->getType();


            $metadata =
                $result->getMetadata();



            /*
            |--------------------------------------------------------------------------
            | Get generated content
            |--------------------------------------------------------------------------
            */

            $content =
                $result->getContent();


            if ($content === null) {

                throw new \RuntimeException(
                    'Document result does not contain content.'
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Resolve final storage path
            |--------------------------------------------------------------------------
            */

            $storedPath =
                $this->storage->putContent(
                    $result->getPath(),
                    $content
                );



            /*
            |--------------------------------------------------------------------------
            | Create permanent storage content
            |--------------------------------------------------------------------------
            */

            $storedContent =
                new FileContent(
                    path: $storedPath,
                    type: $type,
                    filename: $filename,
                    extension: $plan->extension(),
                    metadata: $metadata->toArray(),
                    physical: false
                );



            /*
            |--------------------------------------------------------------------------
            | Replace result
            |--------------------------------------------------------------------------
            */

            $finalResult =
                new DocumentResult(
                    content: $storedContent,
                    path: $storedPath,
                    filename: $filename,
                    type: $type,
                    metadata: $metadata
                );


            $context->setResult(
                $finalResult
            );



            AppLogger::channel('document-builder')
                ->info(
                    'Document generated successfully',
                    new AppContext(
                        $plan->getContext()
                    ),
                    [
                        'path' => $storedPath,
                        'filename' => $filename,
                        'type' => $type,
                    ]
                );
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')
                ->error(
                    'Failed to persist document output',
                    new AppContext(
                        $context->getPlan()->getContext()
                    ),
                    [
                        'stage' => 'OutputStage',
                        'error' => $e->getMessage(),
                    ]
                );

            throw $e;
        }


        return $next($context);
    }
}
