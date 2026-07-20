<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;

/**
 * Class StreamContent
 *
 * Represents document content that is available as a readable PHP stream.
 */
final class StreamContent implements DocumentContent
{
    /**
     * @param resource $stream
     * @param int|null $size Size in bytes when known.
     * @param string $type The document's extension format (e.g., 'pdf', 'html').
     * @param string $filename The target output filename.
     * @param array $metadata Associated processing context metadata.
     *
     * @throws RuntimeException When the provided value is not a stream.
     */
    public function __construct(
        private readonly mixed $stream,
        private readonly ?int $size = null,
        private readonly string $type = 'pdf',
        private readonly string $filename = 'document.pdf',
        private readonly array $metadata = []
    ) {
        if (!is_resource($stream)) {
            throw new RuntimeException(
                'StreamContent requires a valid PHP stream resource.'
            );
        }
    }

    /**
     * Return the underlying stream resource.
     */
    public function value(): mixed
    {
        return $this->stream;
    }

    /**
     * Write the stream content to a consumer.
     */
    public function writeTo(callable $writer): void
    {
        $writer($this->stream);
    }

    /**
     * Return stream size when known.
     */
    public function size(): ?int
    {
        return $this->size;
    }

    /**
     * Indicates that this content is stream based.
     */
    public function isStream(): bool
    {
        return true;
    }

    /**
     * Stream content is not natively a physical file wrapper,
     * but can point to one if generated via fromFile().
     */
    public function isFile(): bool
    {
        return $this->getPath() !== null;
    }

    /**
     * Get the physical path if the stream points directly to an accessible file track.
     */
    public function getPath(): ?string
    {
        if (!is_resource($this->stream)) {
            return null;
        }

        $meta = stream_get_meta_data($this->stream);
        $uri = $meta['uri'] ?? null;

        // Ensure we don't treat internal memory streams (php://temp, php://memory) as valid storage paths
        if ($uri && !str_starts_with($uri, 'php://')) {
            return $uri;
        }

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
     * Safe extraction string reader method.
     */
    public function toString(): string
    {
        if (!is_resource($this->stream)) {
            return '';
        }

        $meta = stream_get_meta_data($this->stream);
        
        if ($meta['seekable'] ?? false) {
            rewind($this->stream);
        }

        return stream_get_contents($this->stream) ?: '';
    }

    /**
     * Create stream content from an existing file system layout trail.
     */
    public static function fromFile(
        string $path,
        ?string $type = null,
        ?string $filename = null,
        array $metadata = []
    ): self {
        if (!file_exists($path)) {
            throw new RuntimeException(
                "Stream source file [$path] does not exist."
            );
        }

        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw new RuntimeException(
                "Unable to open stream for [$path]."
            );
        }

        $size = filesize($path);

        // Fallback options to derive file profiles automatically
        $type ??= pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf';
        $filename ??= basename($path);

        return new self(
            stream: $stream,
            size: $size === false ? null : $size,
            type: $type,
            filename: $filename,
            metadata: $metadata
        );
    }

    /**
     * Close the underlying stream resource.
     */
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * Automatically release the stream resource on object sweep lifecycles.
     */
    public function __destruct()
    {
        $this->close();
    }
}