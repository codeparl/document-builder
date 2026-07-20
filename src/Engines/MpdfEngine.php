<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Engines;

use Mpdf\Mpdf;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentContentFactory;

final class MpdfEngine implements DocumentEngine
{
    /**
     * Inject the factory to safely broker the raw string binaries.
     */
    public function __construct(
        private readonly DocumentContentFactory $factory
    ) {}

    public function name(): string
    {
        return 'mpdf';
    }

    public function type(): string
    {
        return 'pdf';
    }

    /**
     * Render HTML content into PDF document content.
     */
    public function render(
        ExecutionPlan $plan,
        string $content
    ): DocumentContent {

        /*
        |--------------------------------------------------------------------------
        | Initialize mPDF
        |--------------------------------------------------------------------------
        */
        $mpdf = new Mpdf(
            $plan->getDriverConfig()
        );

        /*
        |--------------------------------------------------------------------------
        | Render HTML
        |--------------------------------------------------------------------------
        */
        $mpdf->WriteHTML(
            $content
        );

        /*
        |--------------------------------------------------------------------------
        | Generate PDF binary
        |--------------------------------------------------------------------------
        */
        $binary = $mpdf->Output(
            '',
            \Mpdf\Output\Destination::STRING_RETURN
        );

        /*
        |--------------------------------------------------------------------------
        | Delegate to Factory for Smart Content Wrapping
        |--------------------------------------------------------------------------
        |
        | Instead of blindly returning StringContent, we pass the execution properties.
        | The factory will decide if it fits nicely in memory or needs to be downshifted 
        | into an efficient StreamContent object automatically.
        |
        */
        return $this->factory->make(
            content: $binary,
            type: $this->type(),
            filename: $plan->getOutputFilename() ?? 'document.pdf',
            metadata: $plan->getMetadata()->merge(
                [
                    'engine' => $this->name(),
                    'rendered_at' => date('c')
                ]
            )->toArray()

        );
    }

    public function supports(
        string $engine
    ): bool {
        return $engine === 'mpdf';
    }
}
