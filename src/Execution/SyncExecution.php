<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Execution;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecution;
use UnnovateBrains\DocumentBuilder\Contracts\StorageWorkspaceInterface;

final class SyncExecution implements DocumentExecution
{
    public function batchId(): ?string { return null; }
    public function chunkNumber(): ?int { return null; }
    public function isBatch(): bool { return false; }
    public function workspace(): ?StorageWorkspaceInterface { return null; }
}