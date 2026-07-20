<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Sources;

use UnnovateBrains\DocumentBuilder\Contracts\Source;

final class JsonSource implements Source
{
    public function __construct(
        private readonly string $json
    ) {}


    /**
     * Resolve JSON payload into records.
     *
     * @return iterable<mixed>
     */
    public function resolve(): iterable
    {
        return json_decode(
            $this->json,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }


    /**
     * Return total records count.
     */
    public function count(): int
    {
        return count($this->resolve());
    }


    /**
     * Determine if JSON source has no records.
     */
    public function isEmpty(): bool
    {
        return empty($this->resolve());
    }


    /**
     * Serialize source for queue execution.
     *
     * @return array<string,mixed>
     */
    public function serialize(): array
    {
        return [
            'type' => 'json',
            'json' => $this->json,
        ];
    }


    /**
     * Restore JSON source from queue payload.
     *
     * @param array<string,mixed> $data
     */
    public static function deserialize(array $data): Source
    {
        return new self(
            $data['json'] ?? '[]'
        );
    }
}