<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

final class MockStudent
{
    public function __construct(
        public readonly string $name
    ) {
    }

    public function toDocumentArray(): array
    {
        return [

            'name' => strtoupper($this->name),

            'processed' => true,

        ];
    }
}