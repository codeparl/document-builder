<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Chunking;

use UnnovateBrains\DocumentBuilder\Contracts\ChunkProcessor;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Services\DocumentGenerator;
use UnnovateBrains\DocumentBuilder\Services\DocumentRenderer;
use UnnovateBrains\DocumentBuilder\Services\DocumentTransformer;
use UnnovateBrains\DocumentBuilder\Support\ChunkResult;

/**
 * Class DefaultChunkProcessor
 *
 * Responsible for processing one document chunk.
 *
 * A chunk represents a subset of the original document data.
 *
 * Processing lifecycle:
 *
 * 1. Replace pipeline records with the current chunk.
 * 2. Apply configured transformers.
 * 3. Render the document template.
 * 4. Generate the document artifact.
 * 5. Persist the generated artifact in the execution workspace.
 * 6. Persist the ChunkResult metadata.
 * 7. Return a lightweight ChunkResult reference.
 *
 * The generated document content is never returned to the caller.
 * Only metadata and storage references are kept.
 *
 * This allows the processor to handle very large documents where thousands
 * of chunks may be generated through queues.
 */
final class DefaultChunkProcessor implements ChunkProcessor
{
    /**
     * Create a new chunk processor.
     */
    public function __construct(
        private readonly DocumentTransformer $transformer,
        private readonly DocumentRenderer $renderer,
        private readonly DocumentGenerator $generator
    ) {}



    /**
     * Process a single chunk.
     *
     * @param PipelineContext $context Current pipeline execution context.
     * @param array $chunk Records belonging to this chunk.
     * @param int $number Chunk sequence number.
     *
     * @return ChunkResult Stored chunk reference.
     */
    public function process(
        PipelineContext $context,
        array $chunk,
        int $number
    ): ChunkResult {


        /*
        |--------------------------------------------------------------------------
        | Resolve execution workspace
        |--------------------------------------------------------------------------
        |
        | Workspace belongs to the current execution. It is created by the
        | executor (sync or queue) and attached to the context.
        |
        */
        $workspace =
            $context
            ->execution()
            ->workspace();



        /*
        |--------------------------------------------------------------------------
        | Replace records with current chunk
        |--------------------------------------------------------------------------
        */
        $context->setRecords(
            $chunk
        );



        /*
        |--------------------------------------------------------------------------
        | Transform records
        |--------------------------------------------------------------------------
        */
        $records =
            $this->transformer->apply(
                $chunk,
                $context->getPlan()->getTransformers(),
                $context
            );


        $context->setRecords(
            $records
        );



        /*
        |--------------------------------------------------------------------------
        | Render template
        |--------------------------------------------------------------------------
        */
        $content =
            $this->renderer->render(
                $context
            );


        $context->setRenderedContent(
            $content
        );



        /*
        |--------------------------------------------------------------------------
        | Generate document artifact
        |--------------------------------------------------------------------------
        |
        | DocumentContent abstracts the generated representation:
        |
        | - binary string
        | - stream
        | - file
        |
        */
        $document =
            $this->generator->generate(
                $context
            );



        /*
        |--------------------------------------------------------------------------
        | Store physical chunk artifact
        |--------------------------------------------------------------------------
        |
        | The actual document is written immediately. The processor does not
        | retain generated documents.
        |
        */
        $workspace->putRendered(
            chunk: $number,
            contents: $document->value(),
            type: $document->getType()
        );



        /*
        |--------------------------------------------------------------------------
        | Create lightweight chunk reference
        |--------------------------------------------------------------------------
        */
        $result = new ChunkResult(
            number: $number,
            path: sprintf(
                'rendered/%d.%s',
                $number,
                $document->getType()
            ),
            type: $document->getType(),
            filename: $document->getFilename(),
            metadata: $document->getMetadata()
        );



        /*
        |--------------------------------------------------------------------------
        | Persist chunk metadata
        |--------------------------------------------------------------------------
        |
        | This allows merge stages and queue workers to know exactly which
        | artifacts were generated without scanning storage directories.
        |
        */
        $workspace->putChunk(
            $number,
            $result->toArray()
        );


        return $result;
    }
}
