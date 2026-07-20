<?php

namespace UnnovateBrains\DocumentBuilder\Support;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * Class DocumentMetadata
 *
 * A flexible, serializable repository for runtime generation metrics, structural telemetry,
 * and engine-specific metadata. Implements IteratorAggregate and JsonSerializable for seamless 
 * array and JSON handling.
 *
 * @package UnnovateBrains\DocumentBuilder\Support
 * @implements IteratorAggregate<string, mixed>
 */
class DocumentMetadata implements IteratorAggregate, JsonSerializable
{
    /**
     * Create a new DocumentMetadata instance.
     *
     * @param array<string, mixed> $items
     */
    public function __construct(
        protected array $items = []
    ) {}

    /**
     * Set a metadata key-value pair.
     */
    public function set(string $key, mixed $value): self
    {
        $this->items[$key] = $value;
        return $this;
    }

    /**
     * Retrieve a metadata value by its key with an optional default fallback.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    /**
     * Check if a metadata key exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Remove a metadata key from the repository.
     */
    public function forget(string $key): self
    {
        unset($this->items[$key]);
        return $this;
    }

    /**
     * Merge an external array of tracking attributes into the metadata registry.
     *
     * @param array<string, mixed> $attributes
     */
    public function merge(array $attributes): self
    {
        $this->items = array_merge($this->items, $attributes);
        return $this;
    }

    /**
     * Convert the metadata into a plain associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Fulfill the JsonSerializable interface requirement.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Fulfill the IteratorAggregate interface requirement to allow looping directly over the object.
     *
     * @return Traversable<string, mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}