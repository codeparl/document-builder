<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

/**
 * Interface DocumentContent
 *
 * Represents a generated document artifact independently of its physical
 * storage representation.
 */
interface DocumentContent
{
    /**
     * Returns the underlying content value.
     */
    public function value(): mixed;

    /**
     * Writes the content to a consumer.
     *
     * @param callable $writer Receives the underlying content representation.
     */
    public function writeTo(callable $writer): void;

    /**
     * Returns the content size in bytes when available.
     */
    public function size(): ?int;

    /**
     * Determines whether this content uses a stream representation.
     */
    public function isStream(): bool;

    /**
     * Determines whether this content represents a physical file.
     */
    public function isFile(): bool;

    /**
     * Get the physical path to the file if it exists, otherwise null.
     */
    public function getPath(): ?string;

    /**
     * Get the document's extension or content type format (e.g., 'pdf', 'html').
     */
    public function getType(): string;

    /**
     * Get the intended output filename.
     */
    public function getFilename(): string;

    /**
     * Get associated execution or rendering metadata.
     */
    public function getMetadata(): array;

    /**
     * Get the raw content representation as a flat string.
     */
    public function toString(): string;
}
