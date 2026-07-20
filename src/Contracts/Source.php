<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

use Countable;

/**
 * Interface Source
 *
 * Defines the contract for document-driven data ingestion. It abstracts data sources 
 * (e.g., Eloquent queries, array payloads, external API collections) into a predictable, 
 * countable, iterable structure that the document compilation pipeline can stream or iterate over.
 *
 * Sources must also support serialization because queued document generation cannot
 * transport live objects such as database builders, closures, or connections.
 *
 * @package UnnovateBrains\DocumentBuilder\Contracts
 */
interface Source extends Countable
{
    /**
     * Resolve and return the iterable dataset.
     *
     * This may return a standard array, lazy collection, generator, or database cursor.
     *
     * @return iterable<mixed>
     */
    public function resolve(): iterable;


    /**
     * Get the total number of items in the dataset.
     *
     * @return int
     */
    public function count(): int;


    /**
     * Determine if the dataset contains zero records.
     *
     * @return bool
     */
    public function isEmpty(): bool;


    /**
     * Serialize the source definition for queue transportation.
     *
     * This must only contain the information required to rebuild the source.
     *
     * Example:
     *
     * [
     *     'type' => 'query',
     *     'model' => Student::class,
     *     'filters' => []
     * ]
     *
     * @return array<string,mixed>
     */
    public function serialize(): array;


    /**
     * Restore a source instance from serialized data.
     *
     * @param array<string,mixed> $data
     */
    public static function deserialize(array $data): self;
}