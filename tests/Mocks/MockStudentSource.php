<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

use RuntimeException;
use UnnovateBrains\DocumentBuilder\Contracts\Source;

/**
 * Class MockStudentSource
 *
 * Test implementation of Source contract.
 *
 * Simulates a document dataset containing students.
 * This source supports:
 *
 * - pipeline resolution
 * - counting
 * - empty checks
 * - queue serialization
 * - queue restoration
 */
final class MockStudentSource implements Source
{
    /**
     * @param array<int,array<string,mixed>> $students
     */
    public function __construct(
        private readonly array $students
    ) {
    }



    /**
     * Resolve dataset records.
     *
     * @return iterable<array<string,mixed>>
     */
    public function resolve(): iterable
    {
        return $this->students;
    }



    /**
     * Total records available.
     */
    public function count(): int
    {
        return count($this->students);
    }



    /**
     * Determine if dataset has no records.
     */
    public function isEmpty(): bool
    {
        return empty($this->students);
    }



    /**
     * Serialize source definition for queue storage.
     *
     * Important:
     * We do not serialize PHP objects.
     * Workspace storage contains a portable description.
     */
    public function serialize(): array
    {
        return [
            'type' => 'mock_students',

            'data' => $this->students,
        ];
    }



    /**
     * Restore source from queued payload.
     *
     * @param array<string,mixed> $payload
     */
    public static function deserialize(
        array $payload
    ): self {

        if (
            !isset($payload['data'])
            ||
            !is_array($payload['data'])
        ) {

            throw new RuntimeException(
                'Invalid MockStudentSource payload.'
            );
        }


        return new self(
            $payload['data']
        );
    }
}