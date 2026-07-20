<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Support\DocumentMetadata;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;

final class MockPdfDriver implements DocumentDriver
{
    private DocumentEngine $engineInstance;

   public function __construct()
    {
        // Fully satisfy all 4 abstract methods defined by the DocumentEngine contract
        $this->engineInstance = new class implements DocumentEngine {
            
            public function name(): string
            {
                return 'mock-pdf-engine';
            }

            public function type(): string
            {
                return 'pdf';
            }

            public function render(ExecutionPlan $plan, string $content): DocumentResult
            {
                $binaryPdfContent = "%PDF-1.4 Mock Binary Data derived from engine:\n" . $content;
                $filename = ($plan->getOutputFilename() ?? 'document') . '.pdf';

                return new DocumentResult(
                    content: $binaryPdfContent,
                    path: 'documents/' . $filename,
                    filename: $filename,
                    type: 'pdf',
                    metadata: $plan->getMetadata() ?? new \UnnovateBrains\DocumentBuilder\Support\DocumentMetadata()
                );
            }

            public function supports(string $engine): bool
            {
                return $engine === 'mock-pdf-engine';
            }
        };
    }

    public function handle(ExecutionPlan $plan, string $content): DocumentResult
    {
        $binaryPdfContent = "%PDF-1.4 Mock Binary Data derived from layout:\n" . $content;
        $filename = ($plan->getOutputFilename() ?? 'document') . '.pdf';

        return new DocumentResult(
            content: $binaryPdfContent,
            path: 'documents/' . $filename,
            filename: $filename,
            type: 'pdf',
            metadata: $plan->getMetadata() ?? new DocumentMetadata()
        );
    }

    public function type(): string
    {
        return 'pdf';
    }

    public function engine(): string
    {
        return 'mock-pdf-engine';
    }

    public function supports(string $type, ?string $engine = null): bool
    {
        return $type === 'pdf' && ($engine === null || $engine === 'mock-pdf-engine');
    }

    /**
     * {@inheritdoc}
     */
    public function getEngineInstance(): DocumentEngine
    {
        return $this->engineInstance;
    }
}