<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use RuntimeException;
use SchoolPalm\AppLogger\Context\AppContext;
use SchoolPalm\AppLogger\Facades\AppLogger;
use Throwable;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
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
        private readonly DocumentMergerManager $manager
    ) {}



    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        try {

            $plan = $context->getPlan();


            if (! $plan->shouldMerge()) {
                return $next($context);
            }



            $execution = $context->execution();


            if ($execution === null) {

                throw new RuntimeException(
                    'Cannot merge without execution workspace.'
                );
            }


            $workspace = $execution->workspace();



            $chunks =
                $workspace->renderedChunkPaths(
                    $plan->getType()
                );


            if (empty($chunks)) {

                throw new RuntimeException(
                    'No generated chunks available for merging.'
                );
            }



            $physicalChunks = [];


            foreach ($chunks as $chunk) {

                $physicalChunks[] =
                    $workspace->physicalPath(
                        $chunk
                    );
            }



            $merger =
                $this->manager->merger(
                    $plan->getType()
                );



            $merged =
                $merger->merge(
                    $physicalChunks,
                    $context
                );



            /*
            |--------------------------------------------------------------------------
            | Store merged artifact in workspace
            |--------------------------------------------------------------------------
            */

            $workspace->putFinal(
                $merged instanceof DocumentContent
                    ? $merged->value()
                    : $merged,
                $plan->getType()
            );



            $filename =
                $plan->getOutputFilename()
                ?? 'document';


            if (
                ! str_ends_with(
                    strtolower($filename),
                    '.' . strtolower($plan->getType())
                )
            ) {

                $filename .= '.' . $plan->getType();
            }



            /*
            |--------------------------------------------------------------------------
            | Workspace relative artifact
            |--------------------------------------------------------------------------
            */

            $workspacePath =
                'final/document.' . $plan->getType();



            $outputPath =
                $plan->getOutputPath()
                ?? 'documents/' . $filename;



            /*
            |--------------------------------------------------------------------------
            | Create FileContent from workspace artifact
            |--------------------------------------------------------------------------
            */

            $content =
                new FileContent(
                    path: $workspace->physicalPath(
                        $workspacePath
                    ),
                    type: $plan->getType(),
                    filename: $filename,
                    extension: $plan->extension(),
                    metadata: $plan->getMetadata()?->toArray() ?? [],
                    physical: true
                );



            $result =
                new DocumentResult(

                    content: $content,

                    path: $outputPath,

                    filename: $filename,

                    metadata: $plan->getMetadata()
                        ?? new DocumentMetadata([]),

                    type: $plan->getType()
                );



            DocumentExecutionMetadata::append(
                $result,
                $plan,
                $context,
                $filename
            );



            $context->setResult(
                $result
            );
        } catch (Throwable $e) {

            AppLogger::channel('document-builder')
                ->error(
                    'Failed to merge document chunks',
                    new AppContext(
                        $context->getPlan()->getContext()
                    ),
                    [
                        'stage' => 'MergeStage',
                        'document_type' =>
                        $context->getPlan()->getType(),
                        'error' =>
                        $e->getMessage(),
                    ]
                );

            throw $e;
        }


        return $next($context);
    }
}
