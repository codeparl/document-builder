<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Support;

final class ChunkResult
{
    public function __construct(
        public readonly int $number,
        public readonly ?string $path,
        public readonly ?string $type,
        public readonly ?string $filename,
        public readonly array $metadata
    ) {}

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'number'   => $this->number,
            'path'     => $this->path,
            'type'     => $this->type,
            'filename' => $this->filename,
            'metadata' => $this->metadata,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            number: $data['number'],
            path: $data['path'],
            type: $data['type'],
            filename: $data['filename'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }
}
