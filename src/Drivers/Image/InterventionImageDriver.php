<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Drivers\Image;

use UnnovateBrains\DocumentBuilder\Contracts\DocumentContent;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentDriver;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentEngine;
use UnnovateBrains\DocumentBuilder\Contracts\ImageEngine;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;
use UnnovateBrains\DocumentBuilder\Support\ExecutionPlan;

final class InterventionImageDriver implements DocumentDriver
{

    public function __construct(
        private ImageEngine $engine
    ) {}



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



    public function type(): string
    {
        return 'image';
    }



    public function engine(): string
    {
        return 'intervention';
    }

    public function getEngineInstance(): DocumentEngine
    {
        return $this->engine;
    }

    public function supports(
        string $type,
        ?string $engine = null
    ): bool {

        return strtolower($type) === 'image'
            &&
            (
                $engine === null
                ||
                strtolower($engine) === 'intervention'
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
