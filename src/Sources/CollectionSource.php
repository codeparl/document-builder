<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Sources;

use Illuminate\Support\Collection;
use UnnovateBrains\DocumentBuilder\Contracts\Source;

final class CollectionSource implements Source
{
    public function __construct(
        protected Collection $collection
    ) {}


    /**
     * Resolve collection records.
     *
     * @return iterable<mixed>
     */
    public function resolve(): iterable
    {
        return $this->collection;
    }


    /**
     * Return total number of records.
     */
    public function count(): int
    {
        return $this->collection->count();
    }


    /**
     * Determine if collection has no records.
     */
    public function isEmpty(): bool
    {
        return $this->collection->isEmpty();
    }


    /**
     * Serialize collection source for queue storage.
     *
     * @return array<string,mixed>
     */
    public function serialize(): array
    {
        return [
            'type' => 'collection',
            'items' => $this->collection->toArray(),
        ];
    }


    /**
     * Restore collection source from queue payload.
     *
     * @param array<string,mixed> $data
     */
    public static function deserialize(array $data): Source
    {
        return new self(
            collect($data['items'] ?? [])
        );
    }
}