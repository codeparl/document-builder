<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;

/**
 * Class StringContent
 *
 * Represents a document artifact stored as an in-memory binary string.
 */
final class StringContent implements DocumentContent
{
    /**
     * @param string $content Raw document binary content.
     * @param string $type The document's extension format (e.g., 'pdf', 'html').
     * @param string $filename The target output filename.
     * @param array $metadata Associated processing context metadata.
     */
    public function __construct(
        private readonly string $content,
        private readonly string $type = 'pdf',
        private readonly string $filename = 'document',
        private readonly array $metadata = []
    ) {}

    /**
     * Return the underlying binary content.
     *
     * For StringContent this is the document data itself.
     */
    public function value(): string
    {
        return $this->content;
    }

    /**
     * Write the content to a consumer.
     */
    public function writeTo(
        callable $writer
    ): void {
        $writer(
            $this->content
        );
    }

    /**
     * Return content size in bytes.
     */
    public function size(): int
    {
        return strlen(
            $this->content
        );
    }

    /**
     * String content is not stream based.
     */
    public function isStream(): bool
    {
        return false;
    }

    /**
     * String content is not backed by a physical file.
     */
    public function isFile(): bool
    {
        return false;
    }

    /**
     * In-memory strings do not have a physical disk trail.
     */
    public function getPath(): ?string
    {
        return null;
    }

    /**
     * Get the explicit document classification layout extension.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the intended workspace output filename.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Get the compiled contextual metadata map arrays.
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Flat text accessor method.
     */
    public function toString(): string
    {
        return $this->content;
    }
}