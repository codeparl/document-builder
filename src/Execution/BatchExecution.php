<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Execution;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecution;
use UnnovateBrains\DocumentBuilder\Contracts\StorageWorkspaceInterface;

final class BatchExecution implements DocumentExecution
{
    public function __construct(
        private readonly string $batchId,
        private readonly int $chunkNumber,
        private readonly StorageWorkspaceInterface $workspace
    ) {}

    public function batchId(): string { return $this->batchId; }
    public function chunkNumber(): int { return $this->chunkNumber; }
    public function isBatch(): bool { return true; }
    public function workspace(): StorageWorkspaceInterface { return $this->workspace; }
}