<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentExecutionResult;

final class QueuedDocumentResult implements DocumentExecutionResult
{
    public function __construct(
        private readonly string $batchId
    ) {
    }


    public function batchId(): string
    {
        return $this->batchId;
    }


    /**
     * Queue accepted but document not generated yet.
     */
    public function isComplete(): bool
    {
        return false;
    }
}