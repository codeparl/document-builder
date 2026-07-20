<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
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
     * This stage represents the final persistence boundary of the pipeline.
     *
     * At this point:
     *
     * - Standard execution has generated a final DocumentResult.
     * - Chunked execution has been merged into a final DocumentResult.
     * - Queue execution has completed its document generation.
     *
     * Responsibilities:
     *
     * - Retrieve the final execution result.
     * - Ignore incomplete or missing results.
     * - Delegate document persistence to DocumentStorage.
     *
     * This stage does not:
     *
     * - Generate documents.
     * - Render templates.
     * - Merge document fragments.
     * - Inspect document content types.
     * - Handle filesystem implementation details.
     *
     * DocumentContent decides whether the artifact is:
     *
     * - An in-memory binary string.
     * - A stream.
     * - A generated file.
     */
    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {


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
        |
        | Queue based executions may return a pending result.
        | Permanent storage only happens after completion.
        |
        */
        if (!$result->isComplete()) {
            return $next($context);
        }



        /*
|--------------------------------------------------------------------------
| Persist final artifact
|--------------------------------------------------------------------------
|
| Storage receives the content abstraction and decides how
| to write it to the configured filesystem.
|
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
| Storage may resolve the path differently:
|
| documents/report.pdf
|
| becomes:
|
| tenants/1/schools/5/documents/report.pdf
|
*/
        if ($storedPath !== $result->getPath()) {


            $content =
                new FileContent(
                    path: $this->storage->resolvePath(
                        $storedPath
                    ),
                    type: $result->getType(),
                    filename: $result->getFilename(),
                    metadata: $result->getMetadata()->toArray()
                );



            $result =
                new DocumentResult(
                    content: $content,
                    path: $storedPath,
                    filename: $result->getFilename(),
                    type: $result->getType(),
                    metadata: $result->getMetadata()
                );


            $context->setResult(
                $result
            );
        }


        return $next($context);
    }
}
