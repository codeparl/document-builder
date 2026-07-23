<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Merge;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentMerger;

final class DocumentMergerManager
{
    /**
     * @var array<string, DocumentMerger>
     */
    private array $mergers = [];


    public function __construct(
        private readonly DefaultMerger $defaultMerger
    ) {}


    /**
     * Register a document type merger.
     *
     * Custom mergers override the default behaviour.
     *
     * Example:
     *
     * pdf  => PdfMerger
     * xlsx => ExcelMerger
     */
    public function register(
        string $type,
        DocumentMerger $merger
    ): void {

        $this->mergers[strtolower($type)] = $merger;
    }



    /**
     * Resolve merger implementation for a document type.
     *
     * Resolution order:
     *
     * 1. Registered custom merger
     * 2. Default merger fallback
     */
    public function merger(
        string $type
    ): DocumentMerger {

        $type = strtolower($type);


        foreach ($this->mergers as $merger) {

            if ($merger->supports($type)) {

                return $merger;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Fallback behaviour
        |--------------------------------------------------------------------------
        |
        | Some document types cannot merge.
        |
        | Example:
        | - Excel
        | - CSV
        | - Word
        |
        | DefaultMerger handles the single generated artifact
        | produced by chunk execution.
        |
        */
        return $this->defaultMerger;
    }
}
