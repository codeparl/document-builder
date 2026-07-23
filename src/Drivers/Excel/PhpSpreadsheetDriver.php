<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Drivers\Excel;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Engines\PhpSpreadsheetEngine;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;


final class PhpSpreadsheetDriver implements DocumentDriver
{

    public function __construct(
        private readonly PhpSpreadsheetEngine $engine
    ) {}



    public function type(): string
    {
        return 'xlsx';
    }



    public function engine(): string
    {
        return 'phpspreadsheet';
    }



    public function getEngineInstance(): DocumentEngine
    {
        return $this->engine;
    }



    public function supports(
        string $type,
        ?string $engine = null
    ): bool {


        if ($type !== $this->type()) {
            return false;
        }


        if ($engine === null) {
            return true;
        }


        return $this->engine()
            === strtolower($engine);
    }



    public function handle(
        ExecutionPlan $plan,
        string $content,
        PipelineContext $context
    ): DocumentContent {

        return $this->engine->render(
            $plan,
            $content,
            $context
        );
    }


    public function supportsSplitting(): bool
    {
        return false;
    }

    public function supportsMerging(): bool
    {
        return false;
    }
}
