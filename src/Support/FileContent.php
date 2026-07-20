<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;

/**
 * Class FileContent
 *
 * Represents document content that already exists as a physical file on disk.
 */
final class FileContent implements DocumentContent
{
    private readonly string $type;
    private readonly string $filename;

    /**
     * @param string $path Absolute path to the generated document file.
     * @param string|null $type The document's extension format (e.g., 'pdf', 'xlsx'). Falls back to path extension.
     * @param string|null $filename The target output filename. Falls back to basename.
     * @param array $metadata Associated processing context metadata.
     *
     * @throws RuntimeException When the file does not exist.
     */
    public function __construct(
        private readonly string $path,
        ?string $type = null,
        ?string $filename = null,
        private readonly array $metadata = []
    ) {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Document file [$path] does not exist."
            );
        }

        // Auto-derive structural values if not explicitly provided
        $this->type = $type ?? pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf';
        $this->filename = $filename ?? basename($path);
    }

    /**
     * Return the underlying file representation.
     *
     * For FileContent this is the physical file path.
     */
    public function value(): string
    {
        return $this->path;
    }

    /**
     * Write the file content to a consumer.
     *
     * The file is opened as a read stream so large documents
     * can be transferred without loading the entire file into memory.
     */
    public function writeTo(callable $writer): void
    {
        $stream = fopen($this->path, 'rb');

        if ($stream === false) {
            throw new RuntimeException(
                "Unable to open document file [$this->path]."
            );
        }

        try {
            $writer($stream);
        } finally {
            fclose($stream);
        }
    }

    /**
     * Return the file size in bytes.
     */
    public function size(): ?int
    {
        $size = filesize($this->path);

        return $size === false ? null : $size;
    }

    /**
     * File content is not directly a stream representation.
     */
    public function isStream(): bool
    {
        return false;
    }

    /**
     * Indicates that this content is backed by a physical file.
     */
    public function isFile(): bool
    {
        return true;
    }

    /**
     * Retrieve the original file path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Backward-compatible alias helper mapping directly to getPath().
     */
    public function path(): string
    {
        return $this->getPath();
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
     * Safely load the disk file into an in-memory string representation.
     */
    public function toString(): string
    {
        $content = file_get_contents($this->path);

        if ($content === false) {
            throw new RuntimeException("Failed to read raw file string bytes from [{$this->path}].");
        }

        return $content;
    }
}