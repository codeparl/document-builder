<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Context;

use UnnovateBrains\DocumentBuilder\Contracts\ContextHandler;
use UnnovateBrains\DocumentBuilder\Contracts\DocumentContextHandlerRegistry;

final class ContextHandlerRegistry implements DocumentContextHandlerRegistry
{
    /**
     * @var array<ContextHandler>
     */
    private array $resolvers = [];


    public function register(
        ContextHandler $resolver
    ): void {

        $this->resolvers[] = $resolver;
    }


    public function all(): array
    {
        return $this->resolvers;
    }
}