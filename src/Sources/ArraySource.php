<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Sources;

use UnnovateBrains\DocumentBuilder\Contracts\Source;

final class ArraySource implements Source
{

    /**
     * @param array<int|string, mixed> $items
     */

    public function __construct(
        private readonly array $items
    ) {}


    /**
     * Resolve the stored array payload.
     *
     * @return iterable<mixed>
     */
    public function resolve(): iterable
    {
        return $this->items;
    }


    /**
     * Return total records count.
     */
    public function count(): int
    {
        return count($this->items);
    }


    /**
     * Determine if source has no records.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }


    /**
     * Serialize source for queue persistence.
     *
     * Arrays are already transport-safe, so we simply store them.
     *
     * @return array<string,mixed>
     */
    public function serialize(): array
    {
        return [
            'type' => 'array',
            'items' => $this->items,
        ];
    }


    /**
     * Restore source from serialized queue payload.
     *
     * @param array<string,mixed> $data
     */
    public static function deserialize(array $data): Source
    {
        return new self(
            $data['items'] ?? []
        );
    }
}
