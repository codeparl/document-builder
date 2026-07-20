<?php

declare(strict_types=1);

namespace UnnovateBrains\DocumentBuilder\Contracts;

interface DocumentContextHandlerRegistry
{
    /**
     * Register a context resolver.
     */
    public function register(
        ContextHandler $resolver
    ): void;


    /**
     * Get all registered resolvers.
     *
     * @return array<ContextHandler>
     */
    public function all(): array;
}