<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

/**
 * Represents the state of an entire document generation batch.
 */
final readonly class DocumentBatch
{
    public function __construct(
        public string $id,
        public int $totalChunks,
        public array $context
    ) {}
}