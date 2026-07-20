<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

interface DocumentExecutionResult
{
    /**
     * Determine whether the document generation is complete.
     */
    public function isComplete(): bool;
}