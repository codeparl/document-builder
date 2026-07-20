<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Pipelines\Contracts;

use phpDocumentor\Reflection\Types\Nullable;
use Throwable;
use UnnovateBrains\DocumentBuilder\Contracts\Source;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecution;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\DocumentMetadata;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecutionResult;

interface PipelineContext
{
    /** Blueprint Accessor */
    public function getPlan(): ExecutionPlan;

    /** Source & Hydrated Data Ingestion Tracking */
    public function getSource(): ?Source;
    public function setSource(Source $source): void;

    public function getData(): array;
    public function setData(array $data): void;

    public function getRecords(): array;
    public function setRecords(array $records): void;

    public function setTotalRecords(int $total): void;

    /** Intermediary Layout Layer (e.g., Raw HTML, XML, CSV Data Matrix) */
    public function getRenderedContent(): ?string;
    public function setRenderedContent(string $content): void;

    /** Polymorphic Architecture Components */
    public function getDriver(): ?DocumentDriver;
    public function setDriver(DocumentDriver $driver): void;

    public function getEngine(): ?DocumentEngine;
    public function setEngine(DocumentEngine $engine): void;

    /** Definitive Outbound Results */
    public function getResult(): Nullable | DocumentExecutionResult | DocumentResult;
    public function setResult(Nullable | DocumentExecutionResult | DocumentResult $result): void;
    public function hasResult(): bool;

    /** Auditing and Structural Storage */
    public function getMetadata(): DocumentMetadata;

    public function getAssets(): array;
    public function setAssets(array $assets): void;

    /** Dynamic Step State Memory Bag */
    public function getState(string $key, mixed $default = null): mixed;
    public function setState(string $key, mixed $value): void;


    public function isBatchExecution(): bool;
    public function setExecution(DocumentExecution $execution): void;


    public function execution(): ?DocumentExecution;

    /** Operational Flow Lifecycle Tracking Helpers */
    public function completed(): bool;
    public function fail(Throwable $exception): void;
    public function hasError(): bool;
    public function error(): ?Throwable;
}
