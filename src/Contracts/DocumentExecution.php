<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

interface DocumentExecution
{
    public function batchId(): ?string;
    public function chunkNumber(): ?int;
    public function isBatch(): bool;
    public function workspace(): ?StorageWorkspaceInterface;
}