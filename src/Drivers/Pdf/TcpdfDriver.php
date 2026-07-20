<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Drivers\Pdf;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Pipeline\ExecutionPlan;

final class TcpdfDriver implements DocumentEngine
{
    public function supports(string $documentType, string $engineName): bool
    {
        return $documentType === 'pdf' && $engineName === 'tcpdf';
    }

    public function execute(ExecutionPlan $plan): mixed
    {
        throw new \RuntimeException('Not implemented yet');
    }
}

