<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

interface DocumentBatchRepository
{
    public function create(
        array $data
    ): array;


    public function find(
        string $id
    ): ?array;


    public function update(
        string $id,
        array $data
    ): void;


    public function delete(
        string $id
    ): void;
}