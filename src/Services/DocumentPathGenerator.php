<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Services;

use UnnovateBrains\DocumentBuilder\Storage\DocumentPathResolver;

final class DocumentPathGenerator
{
    public function __construct(
        private readonly DocumentPathResolver $pathResolver
    ) {}

    public function forContext(?string $tenantId, ?string $schoolId): self
    {
        return new self($this->pathResolver->withContext($tenantId, $schoolId));
    }

    public function temporary(string $filename): string
    {
        return $this->pathResolver->resolveTemporary($filename);
    }

    public function output(string $filename): string
    {
        return $this->pathResolver->resolve($filename);
    }
}