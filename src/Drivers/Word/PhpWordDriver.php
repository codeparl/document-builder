<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Drivers\Word;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Pipeline\ExecutionPlan;

final class PhpWordDriver implements DocumentEngine
{
    public function supports(string $documentType, string $engineName): bool
    {
        return $documentType === 'word' && $engineName === 'phpword';
    }

    public function execute(ExecutionPlan $plan): mixed
    {
        throw new \RuntimeException('Not implemented yet');
    }
}

