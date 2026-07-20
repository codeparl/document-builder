<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Services;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class DocumentGenerator
{
    /**
     * Generate a document using the already compiled driver.
     *
     * This service is intentionally stateless. It does not modify the
     * pipeline context and does not decide how the generated artifact
     * should be represented.
     *
     * The caller is responsible for wrapping the output into the
     * appropriate execution result:
     *
     * - DocumentResult for synchronous execution
     * - ChunkResult for chunked execution
     * - QueuedDocumentResult for queued execution
     */
    public function generate(
        PipelineContext $context
    ): DocumentContent {

        $driver = $context->getDriver();

        if (!$driver instanceof DocumentDriver) {
            throw new RuntimeException(
                'No compiled document driver available.'
            );
        }

        return $driver->handle(
            $context->getPlan(),
            $context->getRenderedContent() ?? ''
        );
    }
}
