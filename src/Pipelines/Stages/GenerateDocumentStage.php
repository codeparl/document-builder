<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Stages;

use Closure;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentStorage;
use UnnovateBrains\DocumentBuilder\Services\DocumentGenerator;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineStage;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\StringContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Support\DocumentExecutionMetadata;

final class GenerateDocumentStage implements PipelineStage
{
    public function __construct(
        private readonly DocumentGenerator $generator,
        private readonly DocumentStorage $storage
    ) {}


    /**
     * Generates the final document for standard execution.
     *
     * Chunked executions skip this stage because document fragments are
     * generated independently by workers and finalized by MergeStage.
     *
     * Responsibilities:
     *
     * - Execute the document generation service.
     * - Convert generated output into a DocumentContent representation.
     * - Resolve the final storage path.
     * - Create the immutable DocumentResult.
     * - Store the result inside PipelineContext.
     *
     * The stage does not persist files. Actual storage is handled by
     * OutputStage after the pipeline has completed successfully.
     */
    public function handle(
        PipelineContext $context,
        Closure $next
    ): mixed {

        /*
        |--------------------------------------------------------------------------
        | Skip chunk execution
        |--------------------------------------------------------------------------
        |
        | Chunk workers generate fragments.
        | MergeStage creates the final artifact.
        |
        */
        if ($context->isBatchExecution()) {
            return $next($context);
        }


        /*
        |--------------------------------------------------------------------------
        | Generate document artifact
        |--------------------------------------------------------------------------
        */
        $generated = $this->generator->generate(
            $context
        );


        /*
        |--------------------------------------------------------------------------
        | Normalize generated content
        |--------------------------------------------------------------------------
        |
        | Drivers may return:
        |
        | - DocumentContent
        | - Raw binary strings (legacy drivers)
        |
        */
        if ($generated instanceof DocumentContent) {

            $content = $generated;
        } else {

            $content = new StringContent(
                (string) $generated
            );
        }


        $plan = $context->getPlan();


        /*
        |--------------------------------------------------------------------------
        | Resolve filename
        |--------------------------------------------------------------------------
        */
        $filename = $plan->getOutputFilename()
            ?? 'document';


        if (!str_ends_with(
            strtolower($filename),
            '.' . $plan->getType()
        )) {
            $filename .= '.' . $plan->getType();
        }


        /*
        |--------------------------------------------------------------------------
        | Resolve final storage path
        |--------------------------------------------------------------------------
        */
        $path = $this->storage->resolvePath(
            $plan->getOutputPath()
                ?? 'documents/' . $filename
        );


        /*
        |--------------------------------------------------------------------------
        | Create final document result
        |--------------------------------------------------------------------------
        */
        $result = new DocumentResult(
            content: $content,
            path: $path,
            filename: $filename,
            type: $plan->getType(),
            metadata: $plan->getMetadata()
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


        return $next($context);
    }
}
