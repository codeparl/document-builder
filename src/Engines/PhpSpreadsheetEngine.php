<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Engines;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Traversable;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\StringContent;

final class PhpSpreadsheetEngine implements DocumentEngine
{
    public function name(): string
    {
        return 'phpspreadsheet';
    }


    public function type(): string
    {
        return 'xlsx';
    }


    public function supports(string $engine): bool
    {
        return strtolower($engine) === $this->name();
    }



    public function render(
        ExecutionPlan $plan,
        string $content,
        PipelineContext $context
    ): DocumentContent {


        $config =
            $plan->getDriverConfig('xlsx')
            ?? [];



        /*
        |--------------------------------------------------------------------------
        | Resolve spreadsheet source
        |--------------------------------------------------------------------------
        */

        if (!empty($content)) {


            /*
            |--------------------------------------------------------------------------
            | Blade / HTML template mode
            |--------------------------------------------------------------------------
            */

            $reader =
                new Html();


            $spreadsheet =
                $reader->loadFromString(
                    $content
                );
        } else {


            /*
            |--------------------------------------------------------------------------
            | Direct records mode
            |--------------------------------------------------------------------------
            */

            $records =
                $context->getRecords()
                ?? [];


            $records =
                $this->normalizeRecords(
                    $records
                );


            $spreadsheet =
                $this->buildFromRecords(
                    $records,
                    $config
                );
        }



        $sheet =
            $spreadsheet->getActiveSheet();



        /*
        |--------------------------------------------------------------------------
        | Sheet name
        |--------------------------------------------------------------------------
        */

        if (
            isset($config['sheet'])
            &&
            is_string($config['sheet'])
        ) {

            $sheet->setTitle(
                $config['sheet']
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Auto size columns
        |--------------------------------------------------------------------------
        */

        if (
            ($config['auto_size'] ?? false)
            &&
            $sheet->getHighestColumn() !== null
        ) {


            $highestColumn =
                Coordinate::columnIndexFromString(
                    $sheet->getHighestColumn()
                );


            for (
                $i = 1;
                $i <= $highestColumn;
                $i++
            ) {

                $sheet
                    ->getColumnDimension(
                        Coordinate::stringFromColumnIndex($i)
                    )
                    ->setAutoSize(true);
            }
        }



        /*
        |--------------------------------------------------------------------------
        | Freeze header
        |--------------------------------------------------------------------------
        */

        if (
            $config['freeze_header'] ?? false
        ) {

            $sheet->freezePane('A2');
        }



        /*
        |--------------------------------------------------------------------------
        | Freeze rows
        |--------------------------------------------------------------------------
        */

        if (
            isset($config['freeze_rows'])
            &&
            $config['freeze_rows'] > 0
        ) {

            $sheet->freezePane(
                'A'
                    .
                    ($config['freeze_rows'] + 1)
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Protect worksheet
        |--------------------------------------------------------------------------
        */

        if (
            isset($config['password'])
        ) {

            $sheet
                ->getProtection()
                ->setSheet(
                    true
                );


            $sheet
                ->getProtection()
                ->setPassword(
                    $config['password']
                );
        }



        /*
        |--------------------------------------------------------------------------
        | Generate XLSX
        |--------------------------------------------------------------------------
        */

        $writer =
            new Xlsx(
                $spreadsheet
            );


        ob_start();


        $writer->save(
            'php://output'
        );


        $binary =
            ob_get_clean();



        return new StringContent(
            content: $binary,
            type: 'xlsx',
            filename: ($plan->getOutputFilename() ?? 'document')
                . '.xlsx',

            metadata: $plan->getMetadata()?->toArray()
                ?? []
        );
    }




    private function buildFromRecords(
        array $records,
        array $config
    ): Spreadsheet {


        $spreadsheet =
            new Spreadsheet();


        $sheet =
            $spreadsheet->getActiveSheet();



        $columns =
            $config['columns']
            ?? [];



        /*
        |--------------------------------------------------------------------------
        | Auto detect columns
        |--------------------------------------------------------------------------
        */

        if (
            empty($columns)
            &&
            !empty($records)
        ) {


            foreach (
                array_keys($records[0])
                as $field
            ) {

                $columns[$field] = $field;
            }
        }



        $row = 1;



        /*
        |--------------------------------------------------------------------------
        | Headers
        |--------------------------------------------------------------------------
        */

        if (
            ($config['headers'] ?? true)
            &&
            !empty($columns)
        ) {


            $column = 1;


            foreach (
                $columns as $header => $field
            ) {


                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($column)
                        . $row,
                    $header
                );


                $column++;
            }


            $row++;
        }



        /*
        |--------------------------------------------------------------------------
        | Data rows
        |--------------------------------------------------------------------------
        */

        foreach (
            $records as $record
        ) {


            $column = 1;


            foreach (
                $columns as $field
            ) {


                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($column)
                        . $row,
                    $record[$field] ?? null
                );


                $column++;
            }


            $row++;
        }



        return $spreadsheet;
    }




    private function normalizeRecords(
        mixed $records
    ): array {


        if ($records instanceof Collection) {

            return $records->toArray();
        }


        if ($records instanceof Traversable) {

            return iterator_to_array(
                $records
            );
        }


        return is_array($records)
            ? $records
            : [];
    }
}
