<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

interface DocumentContextResolver
{
    public function tenantId(): ?string;

    public function schoolId(): ?string;
}