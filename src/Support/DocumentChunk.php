<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

/**
 * Represents a single processed segment of the data source.
 */
final readonly class DocumentChunk
{
    public function __construct(
        public int $number,
        public string $path,
        public int $recordCount,
        public string $status = 'pending'
    ) {}
}