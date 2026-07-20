<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Drivers\Pdf;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Engines\MpdfEngine;
use UnnovateBrains\DocumentBuilder\Support\DocumentContentFactory;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

final class MpdfDriver implements DocumentDriver
{
    private DocumentEngine $engineInstance;

    public function __construct(?DocumentEngine $engineInstance = null)
    {
        // Default to a new instance if none is provided via the container
        $factory  =  new DocumentContentFactory();
        $this->engineInstance = $engineInstance ?? new MpdfEngine($factory);
    }

    public function handle(ExecutionPlan $plan, string $content): DocumentContent
    {
        // Delegate structural processing down into the lower-level vendor engine wrapper
        return $this->engineInstance->render($plan, $content);
    }

    public function type(): string
    {
        return 'pdf';
    }

    public function engine(): string
    {
        return 'mpdf';
    }
    /**
     * Verify whether this driver matches a given combination of format type and processing engine.
     * * 💡 Matches your exact DocumentDriver contract signature perfectly!
     */
    public function supports(
        string $type,
        ?string $engine = null
    ): bool {
        return strtolower($type) === 'pdf'
            && ($engine === null || strtolower($engine) === 'mpdf');
    }

    public function getEngineInstance(): DocumentEngine
    {
        return $this->engineInstance;
    }
}
