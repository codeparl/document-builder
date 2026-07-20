<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Context;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextResolver;

final class NullDocumentContextResolver implements DocumentContextResolver
{
    public function tenantId(): ?string
    {
        return 'emma';
    }
    public function schoolId(): ?string
    {
        return 'emma-school';
    }
}
