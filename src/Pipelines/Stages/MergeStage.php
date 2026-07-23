<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use RuntimeException;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Merge\DocumentMergerManager;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Support\DocumentExecutionMetadata;
use UnnovateBrains\DocumentBuilder\Support\DocumentMetadata;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\FileContent;

final class MergeStage implements PipelineStage
{
    public function __construct(
        private readonly DocumentMergerManager $manager,
        private readonly DocumentStorage $storage
    ) {}



    /**
     * Merge generated document chunks into the final document artifact.
     *
     * Chunked generation stores every generated fragment inside the execution
     * workspace. This stage combines those fragments into one final artifact.
     *
     * Flow:
     *
     * rendered/1.pdf
     * rendered/2.pdf
     * rendered/3.pdf
     *
     *        |
     *        v
     *
     * merger
     *
     *        |
     *        v
     *
     * final/document.pdf
     *
     *        |
     *        v
     *
     * OutputStage
     *
     *        |
     *        v
     *
     * permanent storage path
     *
     */
    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            $plan = $context->getPlan();



            /*
            |--------------------------------------------------------------------------
            | Skip merge
            |--------------------------------------------------------------------------
            |
            | Normal documents already have a result from GenerateDocumentStage.
            |
            */
            if (!$plan->shouldMerge()) {
                return $next($context);
            }



            /*
            |--------------------------------------------------------------------------
            | Resolve workspace
            |--------------------------------------------------------------------------
            */

            $execution = $context->execution();


            if ($execution === null) {

                throw new RuntimeException(
                    'Cannot merge without execution workspace.'
                );
            }


            $workspace = $execution->workspace();



            /*
            |--------------------------------------------------------------------------
            | Resolve generated chunk files
            |--------------------------------------------------------------------------
            */

            $chunks =
                $workspace->renderedChunkPaths(
                    $plan->getType()
                );


            if (empty($chunks)) {

                throw new RuntimeException(
                    'No generated chunks available for merging.'
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Convert workspace paths into physical paths
            |--------------------------------------------------------------------------
            |
            | Libraries like FPDI require real filesystem paths.
            |
            */
            $physicalChunks = [];


            foreach ($chunks as $chunk) {

                $physicalChunks[] =
                    $workspace->physicalPath(
                        $chunk
                    );
            }



            /*
            |--------------------------------------------------------------------------
            | Resolve merger
            |--------------------------------------------------------------------------
            */

            $merger =
                $this->manager->merger(
                    $plan->getType()
                );



            /*
            |--------------------------------------------------------------------------
            | Merge chunks
            |--------------------------------------------------------------------------
            */

            $merged =
                $merger->merge(
                    $physicalChunks,
                    $context
                );



            /*
            |--------------------------------------------------------------------------
            | Store merged document inside workspace
            |--------------------------------------------------------------------------
            |
            | We do not keep the merged binary in PipelineContext.
            | The workspace becomes the temporary artifact store.
            |
            */
            if ($merged instanceof DocumentContent) {

                $workspace->putFinal(
                    $merged->value(),
                    $plan->getType()
                );
            } else {

                $workspace->putFinal(
                    $merged,
                    $plan->getType()
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Resolve filename
            |--------------------------------------------------------------------------
            */

            $filename =
                $plan->getOutputFilename()
                ?? 'document';



            if (
                !str_ends_with(
                    strtolower($filename),
                    '.' . strtolower($plan->getType())
                )
            ) {

                $filename .=
                    '.' . $plan->getType();
            }



            /*
            |--------------------------------------------------------------------------
            | Resolve permanent output path
            |--------------------------------------------------------------------------
            |
            | This is NOT the workspace path.
            | OutputStage will copy the workspace artifact here.
            |
            */
            $outputPath =
                $this->storage->resolvePath(
                    $plan->getOutputPath()
                        ?? 'documents/' . $filename
                );



            /*
            |--------------------------------------------------------------------------
            | Create FileContent pointing to workspace artifact
            |--------------------------------------------------------------------------
            */

            $content =
                new FileContent(
                    path: $workspace->physicalPath(
                        'final/document.' . $plan->getType()
                    ),
                    type: $plan->getType(),
                    filename: $filename,
                    metadata: $plan->getMetadata()?->toArray() ?? []
                );



            /*
            |--------------------------------------------------------------------------
            | Create final document result
            |--------------------------------------------------------------------------
            */

            $result =
                new DocumentResult(
                    content: $content,
                    path: $outputPath,
                    filename: $filename,
                    metadata: $plan->getMetadata() ?? new DocumentMetadata([]),
                    type: $plan->getType()
                );



            DocumentExecutionMetadata::append(
                $result,
                $plan,
                $context,
                $filename
            );



            /*
            |--------------------------------------------------------------------------
            | Replace execution result
            |--------------------------------------------------------------------------
            */

            $context->setResult(
                $result
            );
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')->error(
                'Failed to merge document chunks',
                new AppContext($context->getPlan()->getContext()),
                [
                    'stage' => 'MergeStage',
                    'document_type' => $context->getPlan()->getType(),
                    'should_merge' => $context->getPlan()->shouldMerge(),
                    'error' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return $next($context);
    }
}
