<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use UnnovateBrains\DocumentBuilder\Contracts\Source;
use UnnovateBrains\DocumentBuilder\Services\DocumentTransformer;

final class ExecutionPlan
{
    public function __construct(
        private readonly string $type,
        private readonly ?string $engine,          // For the Document Engine (e.g., 'mpdf', 'phpspreadsheet')
        private readonly ?string $templateEngine, // For the Template Engine (e.g., 'blade', 'twig')
        private readonly ?Source $source,
        private readonly ?string $view,
        private readonly array $viewData,
        public readonly ?string $outputPath = null,
        private readonly ?int $chunkSize,
        private readonly ?bool $shouldMerge,
        private readonly ?string $outputFilename,
        private readonly ?string $disk,
        private readonly bool $shouldQueue,
        private readonly ?DocumentMetadata $metadata = null,
        private readonly array $context = [],
        private array $driverConfig = [
            'pdf' => [
                'page_size' => null,
                'orientation' => null,
                'margins' => [
                    'margin_left' => 15,
                    'margin_right' => 15,
                    'margin_top' => 16,
                    'margin_bottom' => 16,
                ],
                'header' => false,
                'footer' => false,
                'header_content' => null,
                'footer_content' => null,
                'watermark' => null,
                'metadata' => [],
            ],

            'xlsx' => [
                'sheet' => null,
                'columns' => [],
                'headers' => true,
                'auto_size' => false,
                'freeze_header' => false,
                'filters' => false,
            ],

            'csv' => [
                'delimiter' => ',',
                'enclosure' => '"',
                'encoding' => 'UTF-8',
            ],

            'docx' => [
                'styles' => [],
                'headers' => [],
                'footers' => [],
            ],

            'html' => [
                'css' => null,
            ],

            'image' => [
                'width' => null,
                'height' => null,
                'quality' => null,
            ]
        ],


        private array $transformers = [],
        protected array $excelConfig = []

    ) {}


    public function getChunkExecutor(): string
    {
        return $this->shouldQueue()
            ? 'queue'
            : 'sync';
    }

    public function driverConfig(array $config): static
    {
        $this->driverConfig = $config;

        return $this;
    }

    /**
     * Get PDF driver configuration.
     */
    public function getPdfConfig(): array
    {
        return $this->driverConfig['pdf'] ?? [];
    }


    /**
     * Get Excel/XLSX driver configuration.
     */
    public function getExcelConfig(): array
    {
        return $this->driverConfig['xlsx'] ?? [];
    }


    /**
     * Get CSV driver configuration.
     */
    public function getCsvConfig(): array
    {
        return $this->driverConfig['csv'] ?? [];
    }


    /**
     * Get Word/DOCX driver configuration.
     */
    public function getWordConfig(): array
    {
        return $this->driverConfig['docx'] ?? [];
    }


    /**
     * Get HTML driver configuration.
     */
    public function getHtmlConfig(): array
    {
        return $this->driverConfig['html'] ?? [];
    }


    /**
     * Get image driver configuration.
     */
    public function getImageConfig(): array
    {
        return $this->driverConfig['image'] ?? [];
    }






    public function getDriverConfig(): array
    {
        return $this->excelConfig;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function getTemplateEngine(): ?string
    {
        return $this->templateEngine;
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function getViewData(): array
    {
        return $this->viewData;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function isChunked(): bool
    {
        return $this->getChunkSize() > 0;
    }
    public function getChunkSize(): ?int
    {
        return $this->chunkSize;
    }

    public function shouldMerge(): ?bool
    {
        return $this->shouldMerge;
    }

    public function getOutputFilename(): ?string
    {
        return $this->outputFilename;
    }

    public function getOutputPath(): ?string
    {
        return $this->outputPath;
    }

    public function getDisk(): ?string
    {
        return $this->disk;
    }

    public function shouldQueue(): bool
    {
        return $this->shouldQueue;
    }

    public function getMetadata(): ?DocumentMetadata
    {
        return $this->metadata;
    }

    public function addTransform(
        callable|DocumentTransformer $transformer
    ): self {
        $this->transformers[] = $transformer;

        return $this;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            engine: $data['engine'] ?? null,
            source: null,
            view: $data['view'] ?? null,
            templateEngine: $data['template_engine'] ?? 'blade',
            viewData: $data['view_data'] ?? [],
            chunkSize: $data['chunk_size'] ?? null,
            shouldMerge: $data['should_merge'] ?? false,
            outputFilename: $data['output_filename'] ?? null,
            outputPath: $data['output_path'] ?? null,
            disk: $data['disk'] ?? 'local',
            shouldQueue: true,
            metadata: new DocumentMetadata(
                $data['metadata'] ?? []
            ),
            driverConfig: $data['driver_config'] ?? [],
            context: $data['context'] ?? [],
            transformers: $data['transformers'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'engine' => $this->engine,
            'source' => [
                'class' => $this->source ? get_class($this->source) : null,
            ],
            'view' => $this->view,
            'template_engine' => $this->templateEngine,
            'view_data' => $this->viewData,
            'chunk_size' => $this->chunkSize,
            'should_merge' => $this->shouldMerge,
            'output_filename' => $this->outputFilename,
            'output_path' => $this->outputPath,
            'disk' => $this->disk,
            'should_queue' => $this->shouldQueue,
            'metadata' => $this->metadata->toArray(),
            'driver_config' => $this->driverConfig,
            'context' => $this->context,
            'chunk_executor' => $this->getChunkExecutor(),
            'transformers' => $this->transformers,
        ];
    }

    public function getTransformers(): array
    {
        return $this->transformers;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function resolvedOutputPath(): string
    {
        if ($this->outputPath) {
            return $this->outputPath;
        }

        $filename = $this->outputFilename ?? 'document';

        if (!str_ends_with($filename, '.' . $this->type)) {
            $filename .= '.' . $this->type;
        }

        return 'documents/' . $filename;
    }
}
