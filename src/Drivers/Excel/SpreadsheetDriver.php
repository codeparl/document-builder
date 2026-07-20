<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Drivers\Excel;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Pipeline\ExecutionPlan;

final class SpreadsheetDriver implements DocumentEngine
{
    public function supports(string $documentType, string $engineName): bool
    {
        return $documentType === 'excel' && $engineName === 'spreadsheet';
    }

    public function execute(ExecutionPlan $plan): mixed
    {
        throw new \RuntimeException('Not implemented yet');
    }
}

