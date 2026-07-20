<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Tests\Mocks;

use UnnovateBrains\DocumentBuilder\Contracts\ContextResolver;
use UnnovateBrains\DocumentBuilder\Pipelines\Contracts\PipelineContext;

final class MockContextResolver implements ContextResolver
{
    public function enter(
        PipelineContext $context
    ): void {

        $ctx = $context
            ->getPlan()
            ->getContext();


        app(MockTenantEnvironment::class)
            ->enter(
                $ctx['tenant_id'] ?? null,
                $ctx['school_id'] ?? null
            );


        $context->setState(
            'context_entered',
            true
        );
    }


    public function leave(
        PipelineContext $context
    ): void {

        app(MockTenantEnvironment::class)
            ->leave();


        $context->setState(
            'context_left',
            true
        );
    }
}