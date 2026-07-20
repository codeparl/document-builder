<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Context;

use UnnovateBrains\DocumentBuilder\Contracts\ContextHandler;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class DefaultContextHandler implements ContextHandler
{
    public function enter(PipelineContext $context): void
    {
        //no-op
        
    }

    public function leave(PipelineContext $context): void
    {
     //no-op
    }

    
}