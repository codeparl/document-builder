<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use phpDocumentor\Reflection\Types\Nullable;
use Throwable;
use UnnovateBrains\DocumentBuilder\Contracts\Source;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecution;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecutionResult;
use UnnovateBrains\DocumentBuilder\Execution\SyncExecution;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;
use UnnovateBrains\DocumentBuilder\Support\DocumentResult;
use UnnovateBrains\DocumentBuilder\Support\DocumentMetadata;

final class DocumentPipelineContext implements PipelineContext
{
    private array $data = [];
    private array $records = []; // 💡 Added: First-class records array backing store
    private ?Source $source = null;
    private int $totalRecords = 0; // 💡 Added: Total records count for context
    private ?string $renderedContent = null;
    private ?DocumentDriver $driver = null;
    private ?DocumentEngine $engine = null;
    private Nullable | DocumentExecutionResult | DocumentResult $result;
    private ?DocumentExecution $execution = null;
    private DocumentMetadata $metadata;
    private array $assets = [];
    private array $state = [];
    private ?Throwable $error = null;

    public function __construct(
        private readonly ExecutionPlan $plan
    ) {
        $this->metadata = $plan->getMetadata() ?? new DocumentMetadata();
    }

    public function getPlan(): ExecutionPlan
    {
        return $this->plan;
    }




    public function setExecution(DocumentExecution $execution): void
    {
        $this->execution = $execution;
    }

    public function execution(): DocumentExecution
    {
        if ($this->execution === null) {
            $this->execution = new SyncExecution();
        }

        return $this->execution;
    }
    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(Source $source): void
    {
        $this->source = $source;
    }

    public function setTotalRecords(int $total): void
    {
        $this->totalRecords = $total;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    // 💡 Added: Explicit getter for resolved records stream
    public function getRecords(): array
    {
        return $this->records;
    }

    public function isBatchExecution(): bool
    {
        return $this->execution()?->isBatch() ?? false;
    }

    // 💡 Added: Explicit setter for resolved records stream
    public function setRecords(array $records): void
    {
        $this->records = $records;
    }

    public function getRenderedContent(): ?string
    {
        return $this->renderedContent;
    }

    public function setRenderedContent(string $content): void
    {
        $this->renderedContent = $content;
    }

    public function getDriver(): ?DocumentDriver
    {
        return $this->driver;
    }

    public function setDriver(DocumentDriver $driver): void
    {
        $this->driver = $driver;
    }

    public function getEngine(): ?DocumentEngine
    {
        return $this->engine;
    }

    public function setEngine(DocumentEngine $engine): void
    {
        $this->engine = $engine;
    }

    public function getResult(): Nullable | DocumentExecutionResult | DocumentResult
    {
        return $this->result;
    }

    public function setResult(Nullable | DocumentExecutionResult | DocumentResult $result): void
    {
        $this->result = $result;
    }

    public function hasResult(): bool
    {
        return $this->result !== null;
    }

    public function getMetadata(): DocumentMetadata
    {
        return $this->metadata;
    }

    public function getAssets(): array
    {
        return $this->assets;
    }

    public function setAssets(array $assets): void
    {
        $this->assets = $assets;
    }

    public function getState(string $key, mixed $default = null): mixed
    {
        return $this->state[$key] ?? $default;
    }

    public function setState(string $key, mixed $value): void
    {
        $this->state[$key] = $value;
    }

    public function completed(): bool
    {
        return $this->hasResult() && ! $this->hasError();
    }

    public function fail(Throwable $exception): void
    {
        $this->error = $exception;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function error(): ?Throwable
    {
        return $this->error;
    }
}
