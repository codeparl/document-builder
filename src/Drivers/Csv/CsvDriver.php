<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Drivers\Csv;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Pipeline\ExecutionPlan;

final class CsvDriver implements DocumentEngine
{
    public function supports(string $documentType, string $engineName): bool
    {
        return $documentType === 'csv' && $engineName === 'csv';
    }

    public function execute(ExecutionPlan $plan): mixed
    {
        throw new \RuntimeException('Not implemented yet');
    }
}

