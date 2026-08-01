<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Engines;

use Mpdf\Mpdf;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentContentFactory;

final class MpdfEngine implements DocumentEngine
{
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



    public function render(
        ExecutionPlan $plan,
        string $content,
        PipelineContext $context
    ): DocumentContent {


        /*
        |--------------------------------------------------------------------------
        | Resolve only PDF configuration
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | [
        |    'page_size'=>'A4',
        |    'orientation'=>'landscape'
        | ]
        |
        */

        $config =
            $plan->getPdfConfig();




        /*
        |--------------------------------------------------------------------------
        | Initialize mPDF
        |--------------------------------------------------------------------------
        */

        $mpdf =
            new Mpdf(
                $this->resolveMpdfConfig(
                    $config
                )
            );


        $plan->setExtension('pdf');
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

        $binary =
            $mpdf->Output(
                '',
                \Mpdf\Output\Destination::STRING_RETURN
            );



        /*
        |--------------------------------------------------------------------------
        | Return document content
        |--------------------------------------------------------------------------
        */

        return $this->factory->make(
            content: $binary,
            type: $this->type(),
            filename: ($plan->getOutputFilename() ?? 'document')
                . '.pdf',
            extension: 'pdf',
            metadata: $plan->getMetadata()
                ->merge([
                    'engine' => $this->name(),
                    'rendered_at' => date('c')
                ])
                ->toArray()
        );
    }



    /**
     * Resolve mPDF-specific configuration.
     */
    private function resolveMpdfConfig(
        array $config
    ): array {

        $options = [];


        if (isset($config['page_size'])) {

            $options['format'] =
                $config['page_size'];
        }


        if (isset($config['orientation'])) {

            $options['orientation'] =
                strtoupper(
                    $config['orientation']
                );
        }


        if (isset($config['margins'])) {

            $margins =
                $config['margins'];


            $options['margin_top'] =
                $margins['top'] ?? 10;

            $options['margin_right'] =
                $margins['right'] ?? 10;

            $options['margin_bottom'] =
                $margins['bottom'] ?? 10;

            $options['margin_left'] =
                $margins['left'] ?? 10;
        }


        return $options;
    }



    public function supports(
        string $engine
    ): bool {

        return strtolower($engine) === $this->name();
    }
}
