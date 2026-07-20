<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Merge;

use RuntimeException;
use setasign\Fpdi\Fpdi;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentMerger;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

/**
 * Class PdfMerger
 *
 * Merges multiple PDF document fragments into a single PDF document.
 *
 * The merger receives physical filesystem paths to generated PDF chunks.
 *
 * Example input:
 *
 * [
 *     "/storage/app/batches/abc/rendered/1.pdf",
 *     "/storage/app/batches/abc/rendered/2.pdf"
 * ]
 *
 * The workspace layer is responsible for converting logical storage paths
 * into physical paths before reaching this class.
 *
 * Responsibilities:
 *
 * - Validate PDF chunk files.
 * - Import pages using FPDI.
 * - Preserve original page dimensions.
 * - Return merged PDF binary content.
 *
 * The merger does not store files.
 * Storage responsibility belongs to DocumentStorage/Workspace.
 */
final class PdfMerger implements DocumentMerger
{
    /**
     * Determine whether this merger supports the requested document type.
     */
    public function supports(
        string $type
    ): bool {

        return strtolower($type) === 'pdf';
    }



    /**
     * Merge PDF chunks.
     *
     * @param array<string> $chunks Absolute filesystem paths to PDF files.
     * @param PipelineContext $context Current pipeline context.
     *
     * @return string Generated merged PDF binary content.
     *
     * @throws RuntimeException When a chunk file cannot be found.
     */
    public function merge(
        array $chunks,
        PipelineContext $context
    ): string {


        if (empty($chunks)) {

            throw new RuntimeException(
                'Cannot merge PDF. No chunks supplied.'
            );
        }


        $pdf = new Fpdi();



        foreach ($chunks as $file) {


            if (!is_string($file)) {

                throw new RuntimeException(
                    'Invalid PDF chunk path supplied.'
                );
            }


            if (!file_exists($file)) {

                throw new RuntimeException(
                    "PDF chunk does not exist: {$file}"
                );
            }



            /*
            |--------------------------------------------------------------------------
            | Import source PDF
            |--------------------------------------------------------------------------
            */

            $pages =
                $pdf->setSourceFile(
                    $file
                );



            /*
            |--------------------------------------------------------------------------
            | Copy every page
            |--------------------------------------------------------------------------
            */

            for (
                $page = 1;
                $page <= $pages;
                $page++
            ) {


                $template =
                    $pdf->importPage(
                        $page
                    );


                $size =
                    $pdf->getTemplateSize(
                        $template
                    );



                $pdf->AddPage(
                    $size['orientation'],
                    [
                        $size['width'],
                        $size['height']
                    ]
                );



                $pdf->useTemplate(
                    $template
                );
            }
        }



        /*
        |--------------------------------------------------------------------------
        | Return merged PDF binary
        |--------------------------------------------------------------------------
        |
        | 'S' tells FPDI to return the document as a string instead of
        | writing directly to disk.
        |
        */
        return $pdf->Output(
            'S'
        );
    }
}
